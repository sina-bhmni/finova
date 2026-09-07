<?php
/**
 * FINOVA - Personal Finance Manager
 * مدل Wallet - مسئول ارتباط با جدول wallets
 */

class Wallet
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * دریافت تمام کیف پول‌های یک کاربر
     */
    public function allByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM wallets WHERE user_id = :user_id ORDER BY created_at ASC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * پیدا کردن یک کیف پول با بررسی مالکیت کاربر
     */
    public function findByIdForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM wallets WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $wallet = $stmt->fetch();
        return $wallet ?: null;
    }

    /**
     * مجموع موجودی تمام حساب‌های کاربر
     */
    public function totalBalance(int $userId): float
    {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(balance), 0) AS total FROM wallets WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return (float)$stmt->fetch()['total'];
    }

    /**
     * ساخت کیف پول جدید
     */
    public function create(int $userId, string $name, string $type, float $balance, string $currency): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO wallets (user_id, name, type, balance, currency)
             VALUES (:user_id, :name, :type, :balance, :currency)'
        );
        $stmt->execute([
            'user_id'  => $userId,
            'name'     => $name,
            'type'     => $type,
            'balance'  => $balance,
            'currency' => $currency,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * ویرایش کیف پول (فقط توسط مالک آن)
     */
    public function update(int $id, int $userId, string $name, string $type, float $balance): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE wallets SET name = :name, type = :type, balance = :balance
             WHERE id = :id AND user_id = :user_id'
        );
        return $stmt->execute([
            'name'    => $name,
            'type'    => $type,
            'balance' => $balance,
            'id'      => $id,
            'user_id' => $userId,
        ]);
    }

    /**
     * افزایش موجودی یک کیف پول (برای درآمد یا دریافت انتقال)
     */
    public function increaseBalance(int $id, float $amount): bool
    {
        $stmt = $this->db->prepare('UPDATE wallets SET balance = balance + :amount WHERE id = :id');
        return $stmt->execute(['amount' => $amount, 'id' => $id]);
    }

    /**
     * کاهش موجودی یک کیف پول (برای هزینه یا ارسال انتقال)
     */
    public function decreaseBalance(int $id, float $amount): bool
    {
        $stmt = $this->db->prepare('UPDATE wallets SET balance = balance - :amount WHERE id = :id');
        return $stmt->execute(['amount' => $amount, 'id' => $id]);
    }

    /**
     * حذف کیف پول (فقط توسط مالک آن)
     * توجه: به دلیل ON DELETE CASCADE، تراکنش‌های وابسته نیز حذف می‌شوند
     */
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM wallets WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    /**
     * تعداد کیف پول‌های کاربر (برای جلوگیری از حذف آخرین کیف پول)
     */
    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS cnt FROM wallets WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetch()['cnt'];
    }
}
