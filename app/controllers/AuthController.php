<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر Authentication (ثبت‌نام / ورود / خروج)
 */

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/ActivityLog.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * نمایش فرم ورود
     */
    public function showLogin(): void
    {
        // اگر کاربر قبلاً لاگین کرده، مستقیم به داشبورد برود
        if (Auth::check()) {
            redirect('/dashboard');
        }

        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        require APP_PATH . '/views/login.php';
    }

    /**
     * پردازش ورود کاربر
     */
    public function login(): void
    {
        Csrf::verifyOrFail();

        $email    = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $validator = new Validator($_POST);
        $validator->required('email', 'ایمیل')
                  ->email('email')
                  ->required('password', 'رمز عبور');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old']    = ['email' => $email];
            redirect('/login');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !Auth::verifyPassword($password, $user['password_hash'])) {
            $_SESSION['errors'] = ['login' => 'ایمیل یا رمز عبور اشتباه است.'];
            $_SESSION['old']    = ['email' => $email];
            redirect('/login');
        }

        Auth::login($user);
        (new ActivityLog())->log($user['id'], 'ورود به سیستم');
        redirect('/dashboard');
    }

    /**
     * نمایش فرم ثبت‌نام
     */
    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }

        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        require APP_PATH . '/views/register.php';
    }

    /**
     * پردازش ثبت‌نام کاربر جدید
     */
    public function register(): void
    {
        Csrf::verifyOrFail();

        $name           = trim($_POST['name'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $password       = (string)($_POST['password'] ?? '');
        $passwordRepeat = (string)($_POST['password_confirmation'] ?? '');

        $validator = new Validator($_POST);
        $validator->required('name', 'نام')
                  ->required('email', 'ایمیل')
                  ->email('email')
                  ->required('password', 'رمز عبور')
                  ->minLength('password', 6, 'رمز عبور')
                  ->matches('password_confirmation', 'password', 'تکرار رمز عبور');

        if ($this->userModel->emailExists($email)) {
            $validator2Errors = $validator->errors();
            $validator2Errors['email'] = 'این ایمیل قبلاً ثبت‌نام کرده است.';
            $_SESSION['errors'] = $validator2Errors;
            $_SESSION['old']    = ['name' => $name, 'email' => $email];
            redirect('/register');
        }

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old']    = ['name' => $name, 'email' => $email];
            redirect('/register');
        }

        $userId = $this->userModel->create($name, $email, $password);
        $newUser = $this->userModel->findById($userId);

        Auth::login($newUser);
        (new ActivityLog())->log($userId, 'ثبت‌نام در سیستم');
        redirect('/dashboard');
    }

    /**
     * خروج کاربر
     */
    public function logout(): void
    {
        Auth::logout();
        redirect('/login');
    }
}
