<?php
/**
 * FINOVA - Personal Finance Manager
 * TransactionService
 * این کلاس مسئول منطق واقعی تراکنش‌هاست: هر تراکنش باید اثر درستی
 * روی موجودی کیف پول بگذارد و در صورت ویرایش/حذف، اثر قبلی برگردانده شود.
 */

require_once APP_PATH . '/models/Transaction.php';
require_once APP_PATH . '/models/Wallet.php';

class TransactionService
{
    private PDO $db;
    private Transaction $transactionModel;
    private Wallet $walletModel;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->transactionModel = new Transaction();
        $this->walletModel = new Wallet();
    }

    /**
     * اعمال اثر یک تراکنش روی موجودی کیف پول(ها)
     */
    private function applyEffect(array $data): void
    {
        switch ($data['type']) {
            case 'income':
                $this->walletModel->increaseBalance($data['wallet_id'], $data['amount']);
                break;

            case 'expense':
                $this->walletModel->decreaseBalance($data['wallet_id'], $data['amount']);
                break;

            case 'transfer':
                $this->walletModel->decreaseBalance($data['wallet_id'], $data['amount']);
                $this->walletModel->increaseBalance($data['transfer_to_wallet_id'], $data['amount']);
                break;
        }
    }

    /**
     * برگرداندن اثر یک تراکنش قبلی (قبل از حذف یا ویرایش)
     */
    private function revertEffect(array $oldRow): void
    {
        switch ($oldRow['type']) {
            case 'income':
                $this->walletModel->decreaseBalance($oldRow['wallet_id'], $oldRow['amount']);
                break;

            case 'expense':
                $this->walletModel->increaseBalance($oldRow['wallet_id'], $oldRow['amount']);
                break;

            case 'transfer':
                $this->walletModel->increaseBalance($oldRow['wallet_id'], $oldRow['amount']);
                $this->walletModel->decreaseBalance($oldRow['transfer_to_wallet_id'], $oldRow['amount']);
                break;
        }
    }

    /**
     * ثبت تراکنش جدید (درآمد، هزینه یا انتقال) به همراه به‌روزرسانی موجودی
     */
    public function createTransaction(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $id = $this->transactionModel->insert($data);
            $this->applyEffect($data);
            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * ویرایش تراکنش موجود: ابتدا اثر قبلی برگردانده، سپس اثر جدید اعمال می‌شود
     */
    public function updateTransaction(int $id, int $userId, array $newData): bool
    {
        $this->db->beginTransaction();
        try {
            $oldRow = $this->transactionModel->findByIdForUser($id, $userId);
            if (!$oldRow) {
                $this->db->rollBack();
                return false;
            }

            $this->revertEffect($oldRow);
            $result = $this->transactionModel->updateRow($id, $newData);
            $this->applyEffect($newData);

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * حذف تراکنش: برگرداندن اثر آن روی موجودی و سپس حذف ردیف
     */
    public function deleteTransaction(int $id, int $userId): bool
    {
        $this->db->beginTransaction();
        try {
            $row = $this->transactionModel->findByIdForUser($id, $userId);
            if (!$row) {
                $this->db->rollBack();
                return false;
            }

            $this->revertEffect($row);
            $result = $this->transactionModel->deleteRow($id);

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
