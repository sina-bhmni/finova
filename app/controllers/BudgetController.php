<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر مدیریت بودجه‌ها
 */

require_once APP_PATH . '/models/Budget.php';
require_once APP_PATH . '/models/Category.php';
require_once APP_PATH . '/services/BudgetService.php';
require_once APP_PATH . '/models/Notification.php';
require_once APP_PATH . '/models/ActivityLog.php';

class BudgetController
{
    private Budget $budgetModel;
    private Category $categoryModel;
    private BudgetService $budgetService;

    public function __construct()
    {
        Auth::requireLogin();
        $this->budgetModel   = new Budget();
        $this->categoryModel = new Category();
        $this->budgetService = new BudgetService();
    }

    /**
     * نمایش بودجه‌های ماه انتخاب‌شده (پیش‌فرض: ماه جاری)
     */
    public function index(): void
    {
        $userId = Auth::id();

        $month = (int)($_GET['month'] ?? date('n'));
        $year  = (int)($_GET['year'] ?? date('Y'));

        $budgets = $this->budgetService->getBudgetsWithProgress($userId, $month, $year);
        $expenseCategories = $this->categoryModel->allByUser($userId, 'expense');

        // تولید اعلان داخلی برای بودجه‌هایی که به ۸۰٪ رسیده یا از سقف عبور کرده‌اند
        $notificationModel = new Notification();
        foreach ($budgets as $b) {
            if ($b['status'] === 'warning') {
                $msg = '⚠️ ' . $b['percent'] . '٪ از بودجه ' . $b['category_name'] . ' استفاده شده است.';
            } elseif ($b['status'] === 'exceeded') {
                $msg = '🚨 سقف بودجه ' . $b['category_name'] . ' رد شده است.';
            } else {
                continue;
            }
            if (!$notificationModel->existsWithMessage($userId, $msg)) {
                $notificationModel->create($userId, $msg, $b['status'] === 'exceeded' ? 'warning' : 'warning');
            }
        }

        $errors  = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['success']);

        $pageTitle = 'بودجه‌ها';
        $activeNav = 'budgets';

        require APP_PATH . '/views/budgets.php';
    }

    /**
     * ساخت بودجه جدید برای یک دسته‌بندی در ماه/سال مشخص
     */
    public function store(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();

        $month = (int)($_POST['period_month'] ?? date('n'));
        $year  = (int)($_POST['period_year'] ?? date('Y'));

        $validator = new Validator($_POST);
        $validator->required('category_id', 'دسته‌بندی')
                  ->required('amount_limit', 'سقف بودجه')
                  ->numeric('amount_limit', 'سقف بودجه')
                  ->min('amount_limit', 1, 'سقف بودجه');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            redirect('/budgets?month=' . $month . '&year=' . $year);
        }

        $categoryId = (int)$_POST['category_id'];

        // بررسی مالکیت دسته‌بندی
        $category = $this->categoryModel->findByIdForUser($categoryId, $userId);
        if (!$category || $category['type'] !== 'expense') {
            $_SESSION['errors'] = ['category_id' => 'دسته‌بندی انتخاب‌شده معتبر نیست.'];
            redirect('/budgets?month=' . $month . '&year=' . $year);
        }

        if ($this->budgetModel->existsForCategoryPeriod($userId, $categoryId, $month, $year)) {
            $_SESSION['errors'] = ['category_id' => 'برای این دسته‌بندی در این ماه قبلاً بودجه تعریف شده است.'];
            redirect('/budgets?month=' . $month . '&year=' . $year);
        }

        $this->budgetModel->create($userId, $categoryId, (float)$_POST['amount_limit'], $month, $year);

        $_SESSION['success'] = 'بودجه جدید با موفقیت تعریف شد.';
        (new ActivityLog())->log($userId, 'بودجه جدید برای «' . $category['name'] . '» تعریف شد');
        redirect('/budgets?month=' . $month . '&year=' . $year);
    }

    /**
     * حذف بودجه
     */
    public function delete(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);
        $month  = (int)($_POST['month'] ?? date('n'));
        $year   = (int)($_POST['year'] ?? date('Y'));

        $this->budgetModel->delete($id, $userId);

        $_SESSION['success'] = 'بودجه مورد نظر حذف شد.';
        redirect('/budgets?month=' . $month . '&year=' . $year);
    }
}
