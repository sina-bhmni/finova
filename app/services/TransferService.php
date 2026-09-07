<?php
/**
 * FINOVA - Personal Finance Manager
 * TransferService
 * منطق اختصاصی انتقال سریع بین دو حساب کاربر (مستقل از فرم عمومی تراکنش)
 * از TransactionService برای اجرای واقعی انتقال (و به‌روزرسانی موجودی) استفاده می‌کند.
 */

require_once APP_PATH . '/models/Wallet.php';
require_once APP_PATH . '/services/TransactionService.php';

class TransferService
{
    private Wallet $walletModel;
    private TransactionService $transactionService;

    public function __construct()
    {
        $this->walletModel = new Wallet();
        $this->transactionService = new TransactionService();
    }

    /**
     * انجام انتقال بین دو حساب کاربر
     * برمی‌گرداند آرایه‌ای شامل ['success' => bool, 'error' => string|null]
     */
    public function transfer(int $userId, int $fromWalletId, int $toWalletId, float $amount, ?string $description, string $date): array
    {
        if ($fromWalletId === $toWalletId) {
            return ['success' => false, 'error' => 'حساب مبدأ و مقصد نمی‌توانند یکسان باشند.'];
        }

        if ($amount <= 0) {
            return ['success' => false, 'error' => 'مبلغ انتقال باید بزرگتر از صفر باشد.'];
        }

        $fromWallet = $this->walletModel->findByIdForUser($fromWalletId, $userId);
        $toWallet   = $this->walletModel->findByIdForUser($toWalletId, $userId);

        if (!$fromWallet || !$toWallet) {
            return ['success' => false, 'error' => 'یکی از حساب‌های انتخاب‌شده معتبر نیست.'];
        }

        if ((float)$fromWallet['balance'] < $amount) {
            return ['success' => false, 'error' => 'موجودی حساب مبدأ کافی نیست.'];
        }

        $this->transactionService->createTransaction([
            'user_id'               => $userId,
            'wallet_id'             => $fromWalletId,
            'category_id'           => null,
            'type'                  => 'transfer',
            'amount'                => $amount,
            'description'           => $description ?: 'انتقال بین حساب‌ها',
            'transaction_date'      => $date,
            'transfer_to_wallet_id' => $toWalletId,
        ]);

        return ['success' => true, 'error' => null];
    }
}
