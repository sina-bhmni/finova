<?php
/**
 * FINOVA - Personal Finance Manager
 * مدل Category - مسئول ارتباط با جدول categories
 */

class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * دریافت تمام دسته‌بندی‌های یک کاربر (اختیاری: فیلتر بر اساس نوع)
     */
    public function allByUser(int $userId, ?string $type = null): array
    {
        if ($type) {
            $stmt = $this->db->prepare(
                'SELECT * FROM categories WHERE user_id = :user_id AND type = :type ORDER BY name ASC'
            );
            $stmt->execute(['user_id' => $userId, 'type' => $type]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT * FROM categories WHERE user_id = :user_id ORDER BY type ASC, name ASC'
            );
            $stmt->execute(['user_id' => $userId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * پیدا کردن یک دسته‌بندی با بررسی مالکیت کاربر
     */
    public function findByIdForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    /**
     * ساخت دسته‌بندی جدید
     */
    public function create(int $userId, string $name, string $type, ?string $icon = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (user_id, name, type, icon) VALUES (:user_id, :name, :type, :icon)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'name'    => $name,
            'type'    => $type,
            'icon'    => $icon,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * ویرایش دسته‌بندی (فقط توسط مالک آن)
     */
    public function update(int $id, int $userId, string $name, string $type, ?string $icon = null): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categories SET name = :name, type = :type, icon = :icon
             WHERE id = :id AND user_id = :user_id'
        );
        return $stmt->execute([
            'name'    => $name,
            'type'    => $type,
            'icon'    => $icon,
            'id'      => $id,
            'user_id' => $userId,
        ]);
    }

    /**
     * حذف دسته‌بندی (فقط توسط مالک آن)
     * توجه: در تراکنش‌های وابسته، category_id به NULL تبدیل می‌شود (ON DELETE SET NULL)
     */
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    /**
     * بررسی اینکه آیا این دسته‌بندی حداقل یک تراکنش وابسته دارد
     */
    public function hasTransactions(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM transactions WHERE category_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return (bool)$stmt->fetch();
    }
}
