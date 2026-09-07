<?php
/**
 * FINOVA - Personal Finance Manager
 * فایل تنظیمات اصلی پروژه
 */

// نمایش خطاها فقط در محیط توسعه
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// تنظیم منطقه زمانی
date_default_timezone_set('Asia/Tehran');

// شروع سشن (اگر قبلاً شروع نشده باشد)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// مسیرهای اصلی پروژه
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');

// آدرس پایه سایت - به‌صورت خودکار از روی مسیر واقعی public/index.php تشخیص داده می‌شود
// به همین دلیل نیازی به تنظیم دستی نیست؛ حتی اگر نام پوشه پروژه را عوض کنی، خودش درست کار می‌کند
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', $scriptDir === '/' ? '' : rtrim($scriptDir, '/'));

// تنظیمات عمومی اپلیکیشن
define('APP_NAME', 'FINOVA');
define('DEFAULT_CURRENCY', 'Toman');
define('DEFAULT_THEME', 'light'); // پروژه به‌صورت پیش‌فرض روشن (Light) است

// اطلاعات اتصال به دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'finova_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// بارگذاری فایل اتصال به دیتابیس
require_once BASE_PATH . '/config/database.php';

// بارگذاری هلپرهای مورد نیاز در کل پروژه
require_once APP_PATH . '/helpers/Csrf.php';
require_once APP_PATH . '/helpers/Auth.php';
require_once APP_PATH . '/helpers/Validator.php';

/**
 * تابع کمکی برای هدایت (Redirect) به یک مسیر دیگر
 */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/**
 * تابع کمکی برای فرمت کردن مبلغ به صورت خوانا
 */
function formatMoney($amount): string
{
    return number_format((float)$amount, 0, '.', ',');
}

/**
 * تابع کمکی برای چاپ امن متن در HTML (جلوگیری از XSS)
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
