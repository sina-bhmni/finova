<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر مدیریت کیف پول / حساب‌ها
 */

require_once APP_PATH . '/models/Wallet.php';
require_once APP_PATH . '/models/ActivityLog.php';

class WalletController
{
    private Wallet $walletModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->walletModel = new Wallet();
    }

    /**
     * نمایش لیست کیف پول‌های کاربر
     */
    public function index(): void
    {
        $userId  = Auth::id();
        $wallets = $this->walletModel->allByUser($userId);
        $total   = $this->walletModel->totalBalance($userId);

        $errors = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['success']);

        require APP_PATH . '/views/wallets.php';
    }

    /**
     * ساخت کیف پول جدید
     */
    public function store(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();

        $validator = new Validator($_POST);
        $validator->required('name', 'نام حساب')
                  ->required('type', 'نوع حساب')
                  ->in('type', ['cash', 'bank', 'card', 'savings', 'other'], 'نوع حساب')
                  ->numeric('balance', 'موجودی اولیه');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            redirect('/wallets');
        }

        $this->walletModel->create(
            $userId,
            trim($_POST['name']),
            $_POST['type'],
            (float)($_POST['balance'] ?? 0),
            DEFAULT_CURRENCY
        );

        $_SESSION['success'] = 'حساب جدید با موفقیت ایجاد شد.';
        (new ActivityLog())->log($userId, 'حساب جدید «' . trim($_POST['name']) . '» ایجاد شد');
        redirect('/wallets');
    }

    /**
     * ویرایش کیف پول موجود
     */
    public function update(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);

        $wallet = $this->walletModel->findByIdForUser($id, $userId);
        if (!$wallet) {
            $_SESSION['errors'] = ['general' => 'حساب مورد نظر یافت نشد.'];
            redirect('/wallets');
        }

        $validator = new Validator($_POST);
        $validator->required('name', 'نام حساب')
                  ->required('type', 'نوع حساب')
                  ->in('type', ['cash', 'bank', 'card', 'savings', 'other'], 'نوع حساب')
                  ->numeric('balance', 'موجودی');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            redirect('/wallets');
        }

        $this->walletModel->update(
            $id,
            $userId,
            trim($_POST['name']),
            $_POST['type'],
            (float)$_POST['balance']
        );

        $_SESSION['success'] = 'حساب با موفقیت ویرایش شد.';
        redirect('/wallets');
    }

    /**
     * حذف کیف پول
     */
    public function delete(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);

        // جلوگیری از حذف آخرین حساب باقی‌مانده کاربر
        if ($this->walletModel->countByUser($userId) <= 1) {
            $_SESSION['errors'] = ['general' => 'حداقل باید یک حساب فعال داشته باشید.'];
            redirect('/wallets');
        }

        $this->walletModel->delete($id, $userId);
        $_SESSION['success'] = 'حساب مورد نظر حذف شد.';
        redirect('/wallets');
    }
}
