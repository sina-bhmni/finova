<?php
/**
 * FINOVA - Personal Finance Manager
 * هلپر مدیریت Authentication و Authorization
 */

class Auth
{
    /**
     * بررسی اینکه کاربر لاگین کرده است یا نه
     */
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * دریافت شناسه کاربر لاگین‌کرده
     */
    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * دریافت اطلاعات پایه کاربر لاگین‌کرده از سشن
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['user_role'] ?? 'user',
        ];
    }

    /**
     * ورود کاربر و ذخیره اطلاعات لازم در سشن
     */
    public static function login(array $userRow): void
    {
        // برای جلوگیری از Session Fixation
        session_regenerate_id(true);

        $_SESSION['user_id']    = $userRow['id'];
        $_SESSION['user_name']  = $userRow['name'];
        $_SESSION['user_email'] = $userRow['email'];
        $_SESSION['user_role']  = $userRow['role'] ?? 'user';
    }

    /**
     * خروج کاربر و پاک‌سازی کامل سشن
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie('PHPSESSID', '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    /**
     * بررسی نقش ادمین
     */
    public static function isAdmin(): bool
    {
        return self::check() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    /**
     * اگر کاربر لاگین نکرده باشد، به صفحه ورود هدایت می‌شود
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('/login');
        }
    }

    /**
     * اگر کاربر ادمین نباشد، دسترسی رد می‌شود
     */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            die('شما دسترسی لازم برای مشاهده این صفحه را ندارید.');
        }
    }

    /**
     * هش کردن رمز عبور با الگوریتم امن bcrypt
     */
    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    /**
     * بررسی تطابق رمز عبور ساده با هش ذخیره‌شده
     */
    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
