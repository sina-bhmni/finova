<?php
$pageTitle = 'کیف پول‌ها';
$activeNav = 'wallets';
require APP_PATH . '/views/partials/header.php';

$walletTypeLabels = [
    'cash'    => 'نقدی',
    'bank'    => 'بانکی',
    'card'    => 'کارت بانکی',
    'savings' => 'پس‌انداز',
    'other'   => 'سایر',
];
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
            <p><?= e($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><p><?= e($success) ?></p></div>
<?php endif; ?>

<div class="page-header-row">
    <div class="card total-balance-card">
        <span class="text-muted">مجموع موجودی حساب‌ها</span>
        <h2><?= formatMoney($total) ?> <small class="text-muted">تومان</small></h2>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('createWalletModal').classList.add('open')">
        + افزودن حساب جدید
    </button>
    <button class="btn btn-secondary" onclick="document.getElementById('transferModal').classList.add('open')">
        ⇄ انتقال بین حساب‌ها
    </button>
</div>

<div class="grid-cards">
    <?php foreach ($wallets as $wallet): ?>
        <div class="card wallet-card">
            <div class="wallet-card-top">
                <span class="wallet-badge wallet-<?= e($wallet['type']) ?>">
                    <?= e($walletTypeLabels[$wallet['type']] ?? $wallet['type']) ?>
                </span>
                <div class="wallet-actions">
                    <button class="icon-btn" title="ویرایش"
                            onclick="openEditWallet(<?= (int)$wallet['id'] ?>, '<?= e($wallet['name']) ?>', '<?= e($wallet['type']) ?>', <?= (float)$wallet['balance'] ?>)">✏️</button>
                    <form action="<?= BASE_URL ?>/wallets/delete" method="POST" style="display:inline"
                          onsubmit="return confirm('آیا از حذف این حساب مطمئن هستید؟');">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= (int)$wallet['id'] ?>">
                        <button type="submit" class="icon-btn" title="حذف">🗑️</button>
                    </form>
                </div>
            </div>
            <h3><?= e($wallet['name']) ?></h3>
            <p class="wallet-balance"><?= formatMoney($wallet['balance']) ?> <small>تومان</small></p>
        </div>
    <?php endforeach; ?>

    <?php if (empty($wallets)): ?>
        <p class="text-muted">هنوز هیچ حسابی ثبت نشده است.</p>
    <?php endif; ?>
</div>

<!-- مودال افزودن حساب جدید -->
<div class="modal-overlay" id="createWalletModal">
    <div class="modal-box card">
        <div class="modal-header">
            <h3>افزودن حساب جدید</h3>
            <button class="icon-btn" onclick="document.getElementById('createWalletModal').classList.remove('open')">✕</button>
        </div>
        <form action="<?= BASE_URL ?>/wallets" method="POST">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="name">نام حساب</label>
                <input type="text" id="name" name="name" placeholder="مثلاً حساب بانک ملت" required>
            </div>
            <div class="form-group">
                <label for="type">نوع حساب</label>
                <select id="type" name="type" required>
                    <option value="cash">نقدی</option>
                    <option value="bank">بانکی</option>
                    <option value="card">کارت بانکی</option>
                    <option value="savings">پس‌انداز</option>
                    <option value="other">سایر</option>
                </select>
            </div>
            <div class="form-group">
                <label for="balance">موجودی اولیه (تومان)</label>
                <input type="number" step="0.01" id="balance" name="balance" placeholder="0" value="0" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">ذخیره حساب</button>
        </form>
    </div>
</div>

<!-- مودال ویرایش حساب -->
<div class="modal-overlay" id="editWalletModal">
    <div class="modal-box card">
        <div class="modal-header">
            <h3>ویرایش حساب</h3>
            <button class="icon-btn" onclick="document.getElementById('editWalletModal').classList.remove('open')">✕</button>
        </div>
        <form action="<?= BASE_URL ?>/wallets/update" method="POST">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label for="edit_name">نام حساب</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="edit_type">نوع حساب</label>
                <select id="edit_type" name="type" required>
                    <option value="cash">نقدی</option>
                    <option value="bank">بانکی</option>
                    <option value="card">کارت بانکی</option>
                    <option value="savings">پس‌انداز</option>
                    <option value="other">سایر</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_balance">موجودی (تومان)</label>
                <input type="number" step="0.01" id="edit_balance" name="balance" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">ذخیره تغییرات</button>
        </form>
    </div>
</div>

<script>
    function openEditWallet(id, name, type, balance) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_type').value = type;
        document.getElementById('edit_balance').value = balance;
        document.getElementById('editWalletModal').classList.add('open');
    }
</script>

<!-- مودال انتقال بین حساب‌ها -->
<div class="modal-overlay" id="transferModal">
    <div class="modal-box card">
        <div class="modal-header">
            <h3>انتقال بین حساب‌ها</h3>
            <button class="icon-btn" onclick="document.getElementById('transferModal').classList.remove('open')">✕</button>
        </div>
        <form action="<?= BASE_URL ?>/transfer" method="POST">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="from_wallet_id">از حساب</label>
                <select id="from_wallet_id" name="from_wallet_id" required>
                    <?php foreach ($wallets as $w): ?>
                        <option value="<?= (int)$w['id'] ?>"><?= e($w['name']) ?> (<?= formatMoney($w['balance']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="to_wallet_id">به حساب</label>
                <select id="to_wallet_id" name="to_wallet_id" required>
                    <?php foreach ($wallets as $w): ?>
                        <option value="<?= (int)$w['id'] ?>"><?= e($w['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="transfer_amount">مبلغ (تومان)</label>
                <input type="number" step="0.01" id="transfer_amount" name="amount" min="1" required>
            </div>
            <div class="form-group">
                <label for="transfer_date">تاریخ</label>
                <input type="date" id="transfer_date" name="transaction_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label for="transfer_description">توضیح (اختیاری)</label>
                <input type="text" id="transfer_description" name="description" placeholder="مثلاً واریز به پس‌انداز">
            </div>
            <button type="submit" class="btn btn-primary btn-block">انجام انتقال</button>
        </form>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
