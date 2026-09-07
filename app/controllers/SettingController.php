<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر پروفایل و تنظیمات کاربر
 */

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Setting.php';
require_once APP_PATH . '/models/ActivityLog.php';

class SettingController
{
    private User $userModel;
    private Setting $settingModel;
    private ActivityLog $activityLogModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->userModel        = new User();
        $this->settingModel     = new Setting();
        $this->activityLogModel = new ActivityLog();
    }

    /**
     * نمایش صفحه پروفایل کاربر
     */
    public function profile(): void
    {
        $userId = Auth::id();
        $user   = $this->userModel->findById($userId);
        $recentActivity = $this->activityLogModel->recentByUser($userId, 8);

        $errors  = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['success']);

        $pageTitle = 'پروفایل';
        $activeNav = 'profile';

        require APP_PATH . '/views/profile.php';
    }

    /**
     * به‌روزرسانی اطلاعات پروفایل (نام، ارز)
     */
    public function updateProfile(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();

        $validator = new Validator($_POST);
        $validator->required('name', 'نام')
                  ->required('currency', 'واحد پول');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            redirect('/profile');
        }

        $this->userModel->updateProfile($userId, trim($_POST['name']), trim($_POST['currency']));

        // به‌روزرسانی نام در سشن تا بلافاصله در تاپ‌بار نمایش داده شود
        $_SESSION['user_name'] = trim($_POST['name']);

        $this->activityLogModel->log($userId, 'پروفایل کاربری به‌روزرسانی شد');

        $_SESSION['success'] = 'پروفایل با موفقیت به‌روزرسانی شد.';
        redirect('/profile');
    }

    /**
     * تغییر رمز عبور کاربر
     */
    public function updatePassword(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();

        $validator = new Validator($_POST);
        $validator->required('current_password', 'رمز عبور فعلی')
                  ->required('new_password', 'رمز عبور جدید')
                  ->minLength('new_password', 6, 'رمز عبور جدید')
                  ->matches('new_password_confirmation', 'new_password', 'تکرار رمز عبور جدید');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            redirect('/profile');
        }

        $user = $this->userModel->findById($userId);
        if (!Auth::verifyPassword($_POST['current_password'], $user['password_hash'])) {
            $_SESSION['errors'] = ['current_password' => 'رمز عبور فعلی اشتباه است.'];
            redirect('/profile');
        }

        $this->userModel->updatePassword($userId, $_POST['new_password']);
        $this->activityLogModel->log($userId, 'رمز عبور تغییر یافت');

        $_SESSION['success'] = 'رمز عبور با موفقیت تغییر یافت.';
        redirect('/profile');
    }

    /**
     * نمایش صفحه تنظیمات
     */
    public function index(): void
    {
        $userId   = Auth::id();
        $settings = $this->settingModel->getByUser($userId);

        $errors  = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['success']);

        $pageTitle = 'تنظیمات';
        $activeNav = 'settings';

        require APP_PATH . '/views/settings.php';
    }

    /**
     * به‌روزرسانی تنظیمات کاربر
     */
    public function update(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();

        $validator = new Validator($_POST);
        $validator->required('language', 'زبان')
                  ->in('language', ['fa', 'en'], 'زبان')
                  ->required('theme', 'پوسته')
                  ->in('theme', ['light', 'dark'], 'پوسته')
                  ->required('timezone', 'منطقه زمانی');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            redirect('/settings');
        }

        $notificationsEnabled = isset($_POST['notifications_enabled']);

        $this->settingModel->update(
            $userId,
            $_POST['language'],
            $_POST['theme'],
            trim($_POST['timezone']),
            $notificationsEnabled
        );

        $this->activityLogModel->log($userId, 'تنظیمات حساب به‌روزرسانی شد');

        $_SESSION['success'] = 'تنظیمات با موفقیت ذخیره شد.';
        redirect('/settings');
    }
}
