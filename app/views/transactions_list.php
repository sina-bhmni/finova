<?php
$pageTitle = 'تراکنش‌ها';
$activeNav = 'transactions';
require APP_PATH . '/views/partials/header.php';

$typeLabels = ['income' => 'درآمد', 'expense' => 'هزینه', 'transfer' => 'انتقال'];
$typeBadgeClass = ['income' => 'badge-success', 'expense' => 'badge-danger', 'transfer' => 'badge-info'];

// برای اینکه فیلترهای فعلی در لینک صفحه‌بندی و مرتب‌سازی حفظ شوند
$queryWithout = function (array $except) {
    $params = $_GET;
    foreach ($except as $key) {
        unset($params[$key]);
    }
    return $params;
};
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><p><?= e($success) ?></p></div>
<?php endif; ?>

<div class="page-header-row">
    <p class="text-muted">مجموع نتایج: <?= (int)$totalCount ?> تراکنش</p>
    <a href="<?= BASE_URL ?>/transactions/create" class="btn btn-primary">+ ثبت تراکنش جدید</a>
</div>

<!-- فرم فیلتر و جستجو -->
<form method="GET" action="<?= BASE_URL ?>/transactions" class="card filter-form" id="filterForm">
    <div class="filter-grid">
        <div class="form-group">
            <label>جستجو در توضیحات</label>
            <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="مثلاً رستوران">
        </div>
        <div class="form-group">
            <label>نوع</label>
            <select name="type">
                <option value="">همه</option>
                <option value="income"   <?= $filters['type']==='income'?'selected':'' ?>>درآمد</option>
                <option value="expense"  <?= $filters['type']==='expense'?'selected':'' ?>>هزینه</option>
                <option value="transfer" <?= $filters['type']==='transfer'?'selected':'' ?>>انتقال</option>
            </select>
        </div>
        <div class="form-group">
            <label>دسته‌بندی</label>
            <select name="category_id">
                <option value="">همه</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= (string)$filters['category_id']===(string)$cat['id']?'selected':'' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>حساب</label>
            <select name="wallet_id">
                <option value="">همه</option>
                <?php foreach ($wallets as $w): ?>
                    <option value="<?= (int)$w['id'] ?>" <?= (string)$filters['wallet_id']===(string)$w['id']?'selected':'' ?>>
                        <?= e($w['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>از تاریخ</label>
            <input type="date" name="date_from" value="<?= e($filters['date_from']) ?>">
        </div>
        <div class="form-group">
            <label>تا تاریخ</label>
            <input type="date" name="date_to" value="<?= e($filters['date_to']) ?>">
        </div>
        <div class="form-group">
            <label>حداقل مبلغ</label>
            <input type="number" name="min_amount" value="<?= e($filters['min_amount']) ?>">
        </div>
        <div class="form-group">
            <label>حداکثر مبلغ</label>
            <input type="number" name="max_amount" value="<?= e($filters['max_amount']) ?>">
        </div>
        <div class="form-group">
            <label>مرتب‌سازی</label>
            <select name="sort">
                <option value="date_desc"   <?= $sort==='date_desc'?'selected':'' ?>>جدیدترین</option>
                <option value="date_asc"    <?= $sort==='date_asc'?'selected':'' ?>>قدیمی‌ترین</option>
                <option value="amount_desc" <?= $sort==='amount_desc'?'selected':'' ?>>بیشترین مبلغ</option>
                <option value="amount_asc"  <?= $sort==='amount_asc'?'selected':'' ?>>کمترین مبلغ</option>
            </select>
        </div>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary">اعمال فیلتر</button>
        <a href="<?= BASE_URL ?>/transactions" class="btn btn-secondary">پاک کردن فیلتر</a>
    </div>
</form>

<!-- جدول تراکنش‌ها -->
<div class="card table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>تاریخ</th>
                    <th>نوع</th>
                    <th>دسته‌بندی</th>
                    <th>حساب</th>
                    <th>توضیح</th>
                    <th>مبلغ</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td><?= e($tx['transaction_date']) ?></td>
                        <td><span class="badge <?= $typeBadgeClass[$tx['type']] ?>"><?= $typeLabels[$tx['type']] ?></span></td>
                        <td>
                            <?php if ($tx['type'] === 'transfer'): ?>
                                <?= e($tx['wallet_name']) ?> ← <?= e($tx['transfer_to_wallet_name']) ?>
                            <?php else: ?>
                                <?= e(($tx['category_icon'] ?? '') . ' ' . ($tx['category_name'] ?? '—')) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= e($tx['wallet_name']) ?></td>
                        <td><?= e($tx['description'] ?: '—') ?></td>
                        <td class="<?= $tx['type']==='income'?'text-success':($tx['type']==='expense'?'text-danger':'') ?>">
                            <?= $tx['type']==='income' ? '+' : ($tx['type']==='expense' ? '-' : '') ?><?= formatMoney($tx['amount']) ?>
                        </td>
                        <td class="table-actions">
                            <a href="<?= BASE_URL ?>/transactions/edit?id=<?= (int)$tx['id'] ?>" class="icon-btn" title="ویرایش">✏️</a>
                            <form action="<?= BASE_URL ?>/transactions/delete" method="POST" style="display:inline"
                                  onsubmit="return confirm('آیا از حذف این تراکنش مطمئن هستید؟');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int)$tx['id'] ?>">
                                <button type="submit" class="icon-btn" title="حذف">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($transactions)): ?>
                    <tr><td colspan="7" class="text-muted text-center">تراکنشی یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- صفحه‌بندی -->
<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <?php $params = $queryWithout(['page']); $params['page'] = $p; ?>
        <a href="?<?= http_build_query($params) ?>" class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
