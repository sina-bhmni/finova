<?php
/**
 * FINOVA - Personal Finance Manager
 * هلپر مدیریت توکن CSRF برای محافظت از فرم‌ها
 */

class Csrf
{
    /**
     * تولید (یا بازگرداندن) توکن CSRF فعلی و ذخیره آن در سشن
     */
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * تولید یک اینپوت مخفی برای استفاده داخل فرم‌های HTML
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * بررسی صحت توکن ارسال‌شده از فرم
     */
    public static function verify(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * بررسی توکن و در صورت نامعتبر بودن، توقف اجرای برنامه
     */
    public static function verifyOrFail(): void
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!self::verify($token)) {
            http_response_code(403);
            die('درخواست نامعتبر است (CSRF Token اشتباه است).');
        }
    }
}
