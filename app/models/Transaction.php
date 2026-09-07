<?php
/**
 * FINOVA - Personal Finance Manager
 * مدل Transaction - مسئول ارتباط با جدول transactions
 */

class Transaction
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * ساخت شرط WHERE مشترک بر اساس فیلترهای ارسالی
     * برمی‌گرداند: [whereSql, params]
     */
    private function buildWhere(int $userId, array $filters): array
    {
        $where  = 't.user_id = :user_id';
        $params = ['user_id' => $userId];

        if (!empty($filters['type'])) {
            $where .= ' AND t.type = :type';
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $where .= ' AND t.category_id = :category_id';
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['wallet_id'])) {
            $where .= ' AND t.wallet_id = :wallet_id';
            $params['wallet_id'] = (int)$filters['wallet_id'];
        }

        if (!empty($filters['date_from'])) {
            $where .= ' AND t.transaction_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where .= ' AND t.transaction_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (isset($filters['min_amount']) && $filters['min_amount'] !== '') {
            $where .= ' AND t.amount >= :min_amount';
            $params['min_amount'] = (float)$filters['min_amount'];
        }

        if (isset($filters['max_amount']) && $filters['max_amount'] !== '') {
            $where .= ' AND t.amount <= :max_amount';
            $params['max_amount'] = (float)$filters['max_amount'];
        }

        if (!empty($filters['search'])) {
            $where .= ' AND t.description LIKE :search';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        return [$where, $params];
    }

    /**
     * دریافت لیست تراکنش‌ها با فیلتر، مرتب‌سازی و صفحه‌بندی
     * همراه با نام کیف پول و دسته‌بندی برای نمایش راحت‌تر
     */
    public function allByUserFiltered(int $userId, array $filters, int $page = 1, int $perPage = 15, string $sort = 'date_desc'): array
    {
        [$where, $params] = $this->buildWhere($userId, $filters);

        $sortMap = [
            'date_desc'   => 'transaction_date DESC, t.id DESC',
            'date_asc'    => 'transaction_date ASC, t.id ASC',
            'amount_desc' => 'amount DESC',
            'amount_asc'  => 'amount ASC',
        ];
        $orderBy = $sortMap[$sort] ?? $sortMap['date_desc'];

        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT t.*, w.name AS wallet_name, c.name AS category_name, c.icon AS category_icon,
                       tw.name AS transfer_to_wallet_name
                FROM transactions t
                LEFT JOIN wallets w  ON w.id = t.wallet_id
                LEFT JOIN categories c ON c.id = t.category_id
                LEFT JOIN wallets tw ON tw.id = t.transfer_to_wallet_id
                WHERE $where
                ORDER BY $orderBy
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * تعداد کل رکوردهای مطابق با فیلتر (برای صفحه‌بندی)
     */
    public function countByUserFiltered(int $userId, array $filters): int
    {
        [$where, $params] = $this->buildWhere($userId, $filters);

        $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM transactions t WHERE $where");
        $stmt->execute($params);
        return (int)$stmt->fetch()['cnt'];
    }

    /**
     * پیدا کردن یک تراکنش با بررسی مالکیت کاربر
     */
    public function findByIdForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM transactions WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * آخرین تراکنش‌های کاربر (برای داشبورد)
     */
    public function recentByUser(int $userId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.*, w.name AS wallet_name, c.name AS category_name, c.icon AS category_icon
             FROM transactions t
             LEFT JOIN wallets w ON w.id = t.wallet_id
             LEFT JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :user_id
             ORDER BY t.transaction_date DESC, t.id DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * ثبت یک ردیف تراکنش خام (بدون منطق تغییر موجودی - آن در Service انجام می‌شود)
     */
    public function insert(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO transactions
                (user_id, wallet_id, category_id, type, amount, description, transaction_date, transfer_to_wallet_id)
             VALUES
                (:user_id, :wallet_id, :category_id, :type, :amount, :description, :transaction_date, :transfer_to_wallet_id)'
        );
        $stmt->execute([
            'user_id'               => $data['user_id'],
            'wallet_id'             => $data['wallet_id'],
            'category_id'           => $data['category_id'] ?? null,
            'type'                  => $data['type'],
            'amount'                => $data['amount'],
            'description'           => $data['description'] ?? null,
            'transaction_date'      => $data['transaction_date'],
            'transfer_to_wallet_id' => $data['transfer_to_wallet_id'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * به‌روزرسانی یک ردیف تراکنش خام
     */
    public function updateRow(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE transactions SET
                wallet_id = :wallet_id,
                category_id = :category_id,
                type = :type,
                amount = :amount,
                description = :description,
                transaction_date = :transaction_date,
                transfer_to_wallet_id = :transfer_to_wallet_id
             WHERE id = :id'
        );
        return $stmt->execute([
            'wallet_id'             => $data['wallet_id'],
            'category_id'           => $data['category_id'] ?? null,
            'type'                  => $data['type'],
            'amount'                => $data['amount'],
            'description'           => $data['description'] ?? null,
            'transaction_date'      => $data['transaction_date'],
            'transfer_to_wallet_id' => $data['transfer_to_wallet_id'] ?? null,
            'id'                    => $id,
        ]);
    }

    /**
     * حذف یک ردیف تراکنش خام
     */
    public function deleteRow(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM transactions WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * مجموع درآمد و هزینه یک کاربر بین دو تاریخ (برای داشبورد و گزارش)
     */
    public function totalsByTypeForPeriod(int $userId, string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare(
            "SELECT type, COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = :user_id AND transaction_date BETWEEN :date_from AND :date_to
               AND type IN ('income', 'expense')
             GROUP BY type"
        );
        $stmt->execute(['user_id' => $userId, 'date_from' => $dateFrom, 'date_to' => $dateTo]);

        $totals = ['income' => 0.0, 'expense' => 0.0];
        foreach ($stmt->fetchAll() as $row) {
            $totals[$row['type']] = (float)$row['total'];
        }
        return $totals;
    }

    /**
     * جمع هزینه‌ها به تفکیک دسته‌بندی در یک بازه زمانی (برای نمودار)
     */
    public function expenseByCategoryForPeriod(int $userId, string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.name AS category_name, COALESCE(SUM(t.amount), 0) AS total
             FROM transactions t
             LEFT JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :user_id AND t.type = 'expense'
               AND t.transaction_date BETWEEN :date_from AND :date_to
             GROUP BY t.category_id
             ORDER BY total DESC"
        );
        $stmt->execute(['user_id' => $userId, 'date_from' => $dateFrom, 'date_to' => $dateTo]);
        return $stmt->fetchAll();
    }

    /**
     * مجموع هزینه یک دسته‌بندی خاص در یک بازه (برای محاسبه پیشرفت بودجه)
     */
    public function totalExpenseForCategoryInPeriod(int $userId, int $categoryId, string $dateFrom, string $dateTo): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = :user_id AND category_id = :category_id AND type = 'expense'
               AND transaction_date BETWEEN :date_from AND :date_to"
        );
        $stmt->execute([
            'user_id'     => $userId,
            'category_id' => $categoryId,
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
        ]);
        return (float)$stmt->fetch()['total'];
    }

    /**
     * تعداد کل تراکنش‌های ثبت‌شده در کل سیستم (برای پنل ادمین)
     */
    public function countAllSystemWide(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS cnt FROM transactions');
        return (int)$stmt->fetch()['cnt'];
    }
}
