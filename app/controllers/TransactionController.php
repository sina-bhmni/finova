<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر مدیریت تراکنش‌ها
 */

require_once APP_PATH . '/models/Transaction.php';
require_once APP_PATH . '/models/Wallet.php';
require_once APP_PATH . '/models/Category.php';
require_once APP_PATH . '/services/TransactionService.php';
require_once APP_PATH . '/services/TransferService.php';
require_once APP_PATH . '/models/Notification.php';
require_once APP_PATH . '/models/ActivityLog.php';

class TransactionController
{
    private Transaction $transactionModel;
    private Wallet $walletModel;
    private Category $categoryModel;
    private TransactionService $transactionService;

    public function __construct()
    {
        Auth::requireLogin();
        $this->transactionModel  = new Transaction();
        $this->walletModel       = new Wallet();
        $this->categoryModel     = new Category();
        $this->transactionService = new TransactionService();
    }

    /**
     * نمایش لیست تراکنش‌ها با فیلتر، جستجو، مرتب‌سازی و صفحه‌بندی
     */
    public function index(): void
    {
        $userId = Auth::id();

        $filters = [
            'type'        => $_GET['type'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'wallet_id'   => $_GET['wallet_id'] ?? '',
            'date_from'   => $_GET['date_from'] ?? '',
            'date_to'     => $_GET['date_to'] ?? '',
            'min_amount'  => $_GET['min_amount'] ?? '',
            'max_amount'  => $_GET['max_amount'] ?? '',
            'search'      => trim($_GET['search'] ?? ''),
        ];
        $sort = $_GET['sort'] ?? 'date_desc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;

        $transactions = $this->transactionModel->allByUserFiltered($userId, $filters, $page, $perPage, $sort);
        $totalCount   = $this->transactionModel->countByUserFiltered($userId, $filters);
        $totalPages   = (int)ceil($totalCount / $perPage);

        $wallets    = $this->walletModel->allByUser($userId);
        $categories = $this->categoryModel->allByUser($userId);

        $success = $_SESSION['success'] ?? null;
        $errors  = $_SESSION['errors'] ?? [];
        unset($_SESSION['success'], $_SESSION['errors']);

        require APP_PATH . '/views/transactions_list.php';
    }

    /**
     * نمایش فرم افزودن تراکنش جدید
     */
    public function create(): void
    {
        $userId = Auth::id();

        $wallets    = $this->walletModel->allByUser($userId);
        $categories = $this->categoryModel->allByUser($userId);
        $transaction = null; // حالت افزودن، نه ویرایش

        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        require APP_PATH . '/views/transaction_form.php';
    }

    /**
     * ذخیره تراکنش جدید
     */
    public function store(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();

        $type = $_POST['type'] ?? '';

        $validator = new Validator($_POST);
        $validator->required('type', 'نوع تراکنش')
                  ->in('type', ['income', 'expense', 'transfer'], 'نوع تراکنش')
                  ->required('wallet_id', 'حساب')
                  ->required('amount', 'مبلغ')
                  ->numeric('amount', 'مبلغ')
                  ->min('amount', 1, 'مبلغ')
                  ->required('transaction_date', 'تاریخ');

        if ($type === 'transfer') {
            $validator->required('transfer_to_wallet_id', 'حساب مقصد');
        } else {
            $validator->required('category_id', 'دسته‌بندی');
        }

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old']    = $_POST;
            redirect('/transactions/create');
        }

        // بررسی مالکیت کیف پول(ها) توسط کاربر جاری
        $wallet = $this->walletModel->findByIdForUser((int)$_POST['wallet_id'], $userId);
        if (!$wallet) {
            $_SESSION['errors'] = ['wallet_id' => 'حساب انتخاب‌شده معتبر نیست.'];
            redirect('/transactions/create');
        }

        $data = [
            'user_id'          => $userId,
            'wallet_id'        => (int)$_POST['wallet_id'],
            'category_id'      => $type !== 'transfer' ? (int)$_POST['category_id'] : null,
            'type'             => $type,
            'amount'           => (float)$_POST['amount'],
            'description'      => trim($_POST['description'] ?? ''),
            'transaction_date' => $_POST['transaction_date'],
            'transfer_to_wallet_id' => null,
        ];

        if ($type === 'transfer') {
            $toWallet = $this->walletModel->findByIdForUser((int)$_POST['transfer_to_wallet_id'], $userId);
            if (!$toWallet || $toWallet['id'] === $wallet['id']) {
                $_SESSION['errors'] = ['transfer_to_wallet_id' => 'حساب مقصد معتبر نیست.'];
                redirect('/transactions/create');
            }
            $data['transfer_to_wallet_id'] = (int)$_POST['transfer_to_wallet_id'];
        }

        // بررسی کافی بودن موجودی برای هزینه یا انتقال
        if (in_array($type, ['expense', 'transfer'], true) && (float)$wallet['balance'] < $data['amount']) {
            $_SESSION['errors'] = ['amount' => 'موجودی حساب انتخاب‌شده کافی نیست.'];
            redirect('/transactions/create');
        }

        $this->transactionService->createTransaction($data);

        // اگر تراکنش از نوع درآمد بود، یک اعلان داخلی برای کاربر ثبت شود
        if ($type === 'income') {
            $category = $this->categoryModel->findByIdForUser($data['category_id'], $userId);
            $catName  = $category['name'] ?? 'درآمد';
            $notificationModel = new Notification();
            $notificationModel->create(
                $userId,
                '💰 ' . $catName . ' به مبلغ ' . formatMoney($data['amount']) . ' تومان ثبت شد.',
                'success'
            );
        }

        $_SESSION['success'] = 'تراکنش با موفقیت ثبت شد.';
        (new ActivityLog())->log($userId, 'تراکنش جدید ثبت شد (' . formatMoney($data['amount']) . ' تومان)');
        redirect('/transactions');
    }

    /**
     * نمایش فرم ویرایش تراکنش
     */
    public function edit(): void
    {
        $userId = Auth::id();
        $id     = (int)($_GET['id'] ?? 0);

        $transaction = $this->transactionModel->findByIdForUser($id, $userId);
        if (!$transaction) {
            redirect('/transactions');
        }

        $wallets    = $this->walletModel->allByUser($userId);
        $categories = $this->categoryModel->allByUser($userId);

        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        require APP_PATH . '/views/transaction_form.php';
    }

    /**
     * به‌روزرسانی تراکنش
     */
    public function update(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);

        $existing = $this->transactionModel->findByIdForUser($id, $userId);
        if (!$existing) {
            redirect('/transactions');
        }

        $type = $_POST['type'] ?? '';

        $validator = new Validator($_POST);
        $validator->required('type', 'نوع تراکنش')
                  ->in('type', ['income', 'expense', 'transfer'], 'نوع تراکنش')
                  ->required('wallet_id', 'حساب')
                  ->required('amount', 'مبلغ')
                  ->numeric('amount', 'مبلغ')
                  ->min('amount', 1, 'مبلغ')
                  ->required('transaction_date', 'تاریخ');

        if ($type === 'transfer') {
            $validator->required('transfer_to_wallet_id', 'حساب مقصد');
        } else {
            $validator->required('category_id', 'دسته‌بندی');
        }

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old']    = $_POST;
            redirect('/transactions/edit?id=' . $id);
        }

        $wallet = $this->walletModel->findByIdForUser((int)$_POST['wallet_id'], $userId);
        if (!$wallet) {
            $_SESSION['errors'] = ['wallet_id' => 'حساب انتخاب‌شده معتبر نیست.'];
            redirect('/transactions/edit?id=' . $id);
        }

        $data = [
            'wallet_id'        => (int)$_POST['wallet_id'],
            'category_id'      => $type !== 'transfer' ? (int)$_POST['category_id'] : null,
            'type'             => $type,
            'amount'           => (float)$_POST['amount'],
            'description'      => trim($_POST['description'] ?? ''),
            'transaction_date' => $_POST['transaction_date'],
            'transfer_to_wallet_id' => null,
        ];

        if ($type === 'transfer') {
            $toWallet = $this->walletModel->findByIdForUser((int)$_POST['transfer_to_wallet_id'], $userId);
            if (!$toWallet || $toWallet['id'] === $wallet['id']) {
                $_SESSION['errors'] = ['transfer_to_wallet_id' => 'حساب مقصد معتبر نیست.'];
                redirect('/transactions/edit?id=' . $id);
            }
            $data['transfer_to_wallet_id'] = (int)$_POST['transfer_to_wallet_id'];
        }

        $this->transactionService->updateTransaction($id, $userId, $data);

        $_SESSION['success'] = 'تراکنش با موفقیت ویرایش شد.';
        redirect('/transactions');
    }

    /**
     * حذف تراکنش
     */
    public function delete(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);

        $this->transactionService->deleteTransaction($id, $userId);

        $_SESSION['success'] = 'تراکنش مورد نظر حذف شد.';
        (new ActivityLog())->log($userId, 'یک تراکنش حذف شد');
        redirect('/transactions');
    }

    /**
     * انتقال سریع بین دو حساب (فرم اختصاصی، مستقل از فرم عمومی تراکنش)
     */
    public function transfer(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();

        $validator = new Validator($_POST);
        $validator->required('from_wallet_id', 'حساب مبدأ')
                  ->required('to_wallet_id', 'حساب مقصد')
                  ->required('amount', 'مبلغ')
                  ->numeric('amount', 'مبلغ')
                  ->min('amount', 1, 'مبلغ')
                  ->required('transaction_date', 'تاریخ');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            redirect('/wallets');
        }

        $transferService = new TransferService();
        $result = $transferService->transfer(
            $userId,
            (int)$_POST['from_wallet_id'],
            (int)$_POST['to_wallet_id'],
            (float)$_POST['amount'],
            trim($_POST['description'] ?? ''),
            $_POST['transaction_date']
        );

        if (!$result['success']) {
            $_SESSION['errors'] = ['general' => $result['error']];
            redirect('/wallets');
        }

        $_SESSION['success'] = 'انتقال بین حساب‌ها با موفقیت انجام شد.';
        redirect('/wallets');
    }
}
