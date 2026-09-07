<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر مدیریت اهداف مالی
 */

require_once APP_PATH . '/models/Goal.php';
require_once APP_PATH . '/models/Notification.php';

class GoalController
{
    private Goal $goalModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->goalModel = new Goal();
    }

    /**
     * نمایش لیست اهداف مالی کاربر
     */
    public function index(): void
    {
        $userId = Auth::id();
        $goals  = $this->goalModel->allByUser($userId);

        $errors  = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['success']);

        $pageTitle = 'اهداف مالی';
        $activeNav = 'goals';

        require APP_PATH . '/views/goals.php';
    }

    /**
     * ساخت هدف مالی جدید
     */
    public function store(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();

        $validator = new Validator($_POST);
        $validator->required('title', 'عنوان هدف')
                  ->required('target_amount', 'مبلغ هدف')
                  ->numeric('target_amount', 'مبلغ هدف')
                  ->min('target_amount', 1, 'مبلغ هدف');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            redirect('/goals');
        }

        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

        $this->goalModel->create($userId, trim($_POST['title']), (float)$_POST['target_amount'], $deadline);

        $_SESSION['success'] = 'هدف مالی جدید با موفقیت ایجاد شد.';
        redirect('/goals');
    }

    /**
     * افزودن مبلغ به پس‌انداز یک هدف
     */
    public function addFunds(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);

        $goal = $this->goalModel->findByIdForUser($id, $userId);
        if (!$goal || $amount <= 0) {
            $_SESSION['errors'] = ['general' => 'مبلغ نامعتبر است.'];
            redirect('/goals');
        }

        $this->goalModel->addFunds($id, $userId, $amount);

        // بررسی پیشرفت هدف پس از افزودن مبلغ برای ارسال اعلان مناسب
        $updatedGoal = $this->goalModel->findByIdForUser($id, $userId);
        $notificationModel = new Notification();
        if ($updatedGoal) {
            $remaining = (float)$updatedGoal['target_amount'] - (float)$updatedGoal['saved_amount'];
            if ($remaining <= 0) {
                $msg = '🎯 تبریک! به هدف «' . $updatedGoal['title'] . '» رسیدید.';
            } else {
                $msg = '🎯 ' . formatMoney($remaining) . ' تومان تا هدف «' . $updatedGoal['title'] . '» باقی مانده است.';
            }
            $notificationModel->create($userId, $msg, $remaining <= 0 ? 'success' : 'info');
        }

        $_SESSION['success'] = 'مبلغ با موفقیت به هدف اضافه شد.';
        redirect('/goals');
    }

    /**
     * حذف هدف مالی
     */
    public function delete(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);

        $this->goalModel->delete($id, $userId);

        $_SESSION['success'] = 'هدف مورد نظر حذف شد.';
        redirect('/goals');
    }
}
