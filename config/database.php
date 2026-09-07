<?php
/**
 * FINOVA - Personal Finance Manager
 * کلاس اتصال به دیتابیس با استفاده از PDO
 * از الگوی Singleton استفاده شده تا فقط یک اتصال باز شود
 */

class Database
{
    private static ?PDO $instance = null;

    // جلوگیری از ساخت نمونه مستقیم از این کلاس
    private function __construct() {}

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // برای اجرای واقعی Prepared Statements
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // در محیط واقعی نباید پیام خطای کامل نمایش داده شود
                die('خطا در اتصال به دیتابیس: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
