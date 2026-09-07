<?php
$isEdit = isset($transaction) && $transaction !== null;
$pageTitle = $isEdit ? 'ویرایش تراکنش' : 'ثبت تراکنش جدید';
$activeNav = 'transactions';
require APP_PATH . '/views/partials/header.php';

$formValues = $isEdit ? $transaction : ($old ?? []);
$selectedType = $formValues['type'] ?? 'expense';
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card form-card">
    <form action="<?= $isEdit ? BASE_URL.'/transactions/update' : BASE_URL.'/transactions' ?>" method="POST" id="transactionForm">
        <?= Csrf::field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$transaction['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>نوع تراکنش</label>
            <div class="type-toggle">
                <label class="type-option">
                    <input type="radio" name="type" value="income"   <?= $selectedType==='income'?'checked':'' ?>> درآمد
                </label>
                <label class="type-option">
                    <input type="radio" name="type" value="expense"  <?= $selectedType==='expense'?'checked':'' ?>> هزینه
                </label>
                <label class="type-option">
                    <input type="radio" name="type" value="transfer" <?= $selectedType==='transfer'?'checked':'' ?>> انتقال
                </label>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="wallet_id" id="walletLabel">حساب</label>
                <select id="wallet_id" name="wallet_id" required>
                    <?php foreach ($wallets as $w): ?>
                        <option value="<?= (int)$w['id'] ?>" <?= (string)($formValues['wallet_id'] ?? '')===(string)$w['id']?'selected':'' ?>>
                            <?= e($w['name']) ?> (<?= formatMoney($w['balance']) ?> تومان)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="categoryGroup">
                <label for="category_id">دسته‌بندی</label>
                <select id="category_id" name="category_id">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" data-type="<?= e($cat['type']) ?>"
                                <?= (string)($formValues['category_id'] ?? '')===(string)$cat['id']?'selected':'' ?>>
                            <?= e(($cat['icon'] ?? '') . ' ' . $cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="transferToGroup">
                <label for="transfer_to_wallet_id">حساب مقصد</label>
                <select id="transfer_to_wallet_id" name="transfer_to_wallet_id">
                    <?php foreach ($wallets as $w): ?>
                        <option value="<?= (int)$w['id'] ?>" <?= (string)($formValues['transfer_to_wallet_id'] ?? '')===(string)$w['id']?'selected':'' ?>>
                            <?= e($w['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="amount">مبلغ (تومان)</label>
                <input type="number" step="0.01" id="amount" name="amount" min="1"
                       value="<?= e($formValues['amount'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="transaction_date">تاریخ</label>
                <input type="date" id="transaction_date" name="transaction_date"
                       value="<?= e($formValues['transaction_date'] ?? date('Y-m-d')) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="description">توضیحات (اختیاری)</label>
            <input type="text" id="description" name="description" placeholder="مثلاً رستوران، حقوق شهریور و..."
                   value="<?= e($formValues['description'] ?? '') ?>">
        </div>

        <div class="live-preview card" id="livePreview" style="display:none">
            <span>موجودی پیش‌بینی‌شده حساب بعد از این تراکنش:</span>
            <strong id="previewAmount">—</strong>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'ذخیره تغییرات' : 'ثبت تراکنش' ?></button>
            <a href="<?= BASE_URL ?>/transactions" class="btn btn-secondary">انصراف</a>
        </div>
    </form>
</div>

<!-- موجودی حساب‌ها برای محاسبه زنده در جاوااسکریپت -->
<script>
    const walletBalances = {
        <?php foreach ($wallets as $w): ?>
            <?= (int)$w['id'] ?>: <?= (float)$w['balance'] ?>,
        <?php endforeach; ?>
    };
</script>
<script src="<?= BASE_URL ?>/js/filters.js"></script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
