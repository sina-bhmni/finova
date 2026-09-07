<?php
/**
 * FINOVA - Personal Finance Manager
 * مدل User - مسئول ارتباط با جدول users
 */

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * پیدا کردن کاربر بر اساس ایمیل
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * پیدا کردن کاربر بر اساس شناسه
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * بررسی تکراری نبودن ایمیل
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetch();
    }

    /**
     * ساخت کاربر جدید و بازگرداندن شناسه آن
     * همچنین یک حساب نقدی پیش‌فرض، چند دسته‌بندی پایه و تنظیمات اولیه برای او می‌سازد
     */
    public function create(string $name, string $email, string $plainPassword): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (name, email, password_hash, currency, role)
                 VALUES (:name, :email, :password_hash, :currency, :role)'
            );
            $stmt->execute([
                'name'          => $name,
                'email'         => $email,
                'password_hash' => Auth::hashPassword($plainPassword),
                'currency'      => DEFAULT_CURRENCY,
                'role'          => 'user',
            ]);

            $userId = (int)$this->db->lastInsertId();

            // ساخت یک کیف پول نقدی پیش‌فرض
            $stmt = $this->db->prepare(
                'INSERT INTO wallets (user_id, name, type, balance, currency)
                 VALUES (:user_id, :name, :type, 0, :currency)'
            );
            $stmt->execute([
                'user_id'  => $userId,
                'name'     => 'کیف پول نقدی',
                'type'     => 'cash',
                'currency' => DEFAULT_CURRENCY,
            ]);

            // ساخت دسته‌بندی‌های پایه درآمد و هزینه
            $defaultCategories = [
                ['name' => 'حقوق',        'type' => 'income'],
                ['name' => 'پروژه',       'type' => 'income'],
                ['name' => 'هدیه',        'type' => 'income'],
                ['name' => 'غذا',         'type' => 'expense'],
                ['name' => 'حمل‌ونقل',    'type' => 'expense'],
                ['name' => 'خرید',        'type' => 'expense'],
                ['name' => 'تفریح',       'type' => 'expense'],
                ['name' => 'قبض',         'type' => 'expense'],
            ];

            $catStmt = $this->db->prepare(
                'INSERT INTO categories (user_id, name, type) VALUES (:user_id, :name, :type)'
            );
            foreach ($defaultCategories as $cat) {
                $catStmt->execute([
                    'user_id' => $userId,
                    'name'    => $cat['name'],
                    'type'    => $cat['type'],
                ]);
            }

            // ساخت تنظیمات پیش‌فرض کاربر - تم به‌صورت پیش‌فرض روشن است
            $stmt = $this->db->prepare(
                'INSERT INTO settings (user_id, language, theme, timezone, notifications_enabled)
                 VALUES (:user_id, :language, :theme, :timezone, 1)'
            );
            $stmt->execute([
                'user_id'  => $userId,
                'language' => 'fa',
                'theme'    => 'light',
                'timezone' => 'Asia/Tehran',
            ]);

            $this->db->commit();
            return $userId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * به‌روزرسانی اطلاعات پروفایل کاربر
     */
    public function updateProfile(int $id, string $name, string $currency, ?string $profilePicture = null): bool
    {
        if ($profilePicture !== null) {
            $stmt = $this->db->prepare(
                'UPDATE users SET name = :name, currency = :currency, profile_picture = :picture WHERE id = :id'
            );
            return $stmt->execute([
                'name'     => $name,
                'currency' => $currency,
                'picture'  => $profilePicture,
                'id'       => $id,
            ]);
        }

        $stmt = $this->db->prepare('UPDATE users SET name = :name, currency = :currency WHERE id = :id');
        return $stmt->execute([
            'name'     => $name,
            'currency' => $currency,
            'id'       => $id,
        ]);
    }

    /**
     * تغییر رمز عبور کاربر
     */
    public function updatePassword(int $id, string $newPlainPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        return $stmt->execute([
            'hash' => Auth::hashPassword($newPlainPassword),
            'id'   => $id,
        ]);
    }

    /**
     * تعداد کل کاربران (برای پنل ادمین)
     */
    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS cnt FROM users');
        return (int)$stmt->fetch()['cnt'];
    }

    /**
     * تعداد کاربرانی که در ماه جاری حداقل یک تراکنش ثبت کرده‌اند (کاربران فعال)
     */
    public function countActiveThisMonth(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT user_id) AS cnt FROM transactions
             WHERE transaction_date BETWEEN :start AND :end"
        );
        $stmt->execute(['start' => date('Y-m-01'), 'end' => date('Y-m-t')]);
        return (int)$stmt->fetch()['cnt'];
    }

    /**
     * دریافت لیست تمام کاربران برای پنل ادمین (بدون هش رمز عبور)
     */
    public function allForAdmin(): array
    {
        $stmt = $this->db->query(
            'SELECT id, name, email, role, currency, created_at FROM users ORDER BY created_at DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * تغییر نقش کاربر توسط ادمین
     */
    public function updateRole(int $id, string $role): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET role = :role WHERE id = :id');
        return $stmt->execute(['role' => $role, 'id' => $id]);
    }
}
