<?php
$pageTitle = 'دسته‌بندی‌ها';
$activeNav = 'categories';
require APP_PATH . '/views/partials/header.php';
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
    <p class="text-muted">دسته‌بندی‌های درآمد و هزینه خود را مدیریت کنید.</p>
    <button class="btn btn-primary" onclick="document.getElementById('createCategoryModal').classList.add('open')">
        + دسته‌بندی جدید
    </button>
</div>

<div class="categories-columns">

    <div class="card category-column">
        <h3 class="category-column-title text-success">درآمد</h3>
        <ul class="category-list">
            <?php foreach ($incomeCategories as $cat): ?>
                <li class="category-item">
                    <span><?= e($cat['icon'] ?: '💰') ?> <?= e($cat['name']) ?></span>
                    <span class="category-actions">
                        <button class="icon-btn" title="ویرایش"
                                onclick="openEditCategory(<?= (int)$cat['id'] ?>, '<?= e($cat['name']) ?>', 'income', '<?= e($cat['icon'] ?? '') ?>')">✏️</button>
                        <form action="<?= BASE_URL ?>/categories/delete" method="POST" style="display:inline"
                              onsubmit="return confirm('آیا از حذف این دسته‌بندی مطمئن هستید؟');">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                            <button type="submit" class="icon-btn" title="حذف">🗑️</button>
                        </form>
                    </span>
                </li>
            <?php endforeach; ?>
            <?php if (empty($incomeCategories)): ?>
                <li class="text-muted">دسته‌بندی درآمدی ثبت نشده است.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="card category-column">
        <h3 class="category-column-title text-danger">هزینه</h3>
        <ul class="category-list">
            <?php foreach ($expenseCategories as $cat): ?>
                <li class="category-item">
                    <span><?= e($cat['icon'] ?: '🧾') ?> <?= e($cat['name']) ?></span>
                    <span class="category-actions">
                        <button class="icon-btn" title="ویرایش"
                                onclick="openEditCategory(<?= (int)$cat['id'] ?>, '<?= e($cat['name']) ?>', 'expense', '<?= e($cat['icon'] ?? '') ?>')">✏️</button>
                        <form action="<?= BASE_URL ?>/categories/delete" method="POST" style="display:inline"
                              onsubmit="return confirm('آیا از حذف این دسته‌بندی مطمئن هستید؟');">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                            <button type="submit" class="icon-btn" title="حذف">🗑️</button>
                        </form>
                    </span>
                </li>
            <?php endforeach; ?>
            <?php if (empty($expenseCategories)): ?>
                <li class="text-muted">دسته‌بندی هزینه‌ای ثبت نشده است.</li>
            <?php endif; ?>
        </ul>
    </div>

</div>

<!-- مودال افزودن دسته‌بندی -->
<div class="modal-overlay" id="createCategoryModal">
    <div class="modal-box card">
        <div class="modal-header">
            <h3>افزودن دسته‌بندی جدید</h3>
            <button class="icon-btn" onclick="document.getElementById('createCategoryModal').classList.remove('open')">✕</button>
        </div>
        <form action="<?= BASE_URL ?>/categories" method="POST">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="name">نام دسته‌بندی</label>
                <input type="text" id="name" name="name" placeholder="مثلاً ورزش" required>
            </div>
            <div class="form-group">
                <label for="type">نوع</label>
                <select id="type" name="type" required>
                    <option value="income">درآمد</option>
                    <option value="expense">هزینه</option>
                </select>
            </div>
            <div class="form-group">
                <label for="icon">آیکون (اختیاری - یک ایموجی)</label>
                <input type="text" id="icon" name="icon" placeholder="مثلاً 🏋️" maxlength="10">
            </div>
            <button type="submit" class="btn btn-primary btn-block">ذخیره دسته‌بندی</button>
        </form>
    </div>
</div>

<!-- مودال ویرایش دسته‌بندی -->
<div class="modal-overlay" id="editCategoryModal">
    <div class="modal-box card">
        <div class="modal-header">
            <h3>ویرایش دسته‌بندی</h3>
            <button class="icon-btn" onclick="document.getElementById('editCategoryModal').classList.remove('open')">✕</button>
        </div>
        <form action="<?= BASE_URL ?>/categories/update" method="POST">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label for="edit_name">نام دسته‌بندی</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="edit_type">نوع</label>
                <select id="edit_type" name="type" required>
                    <option value="income">درآمد</option>
                    <option value="expense">هزینه</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_icon">آیکون (اختیاری)</label>
                <input type="text" id="edit_icon" name="icon" maxlength="10">
            </div>
            <button type="submit" class="btn btn-primary btn-block">ذخیره تغییرات</button>
        </form>
    </div>
</div>

<script>
    function openEditCategory(id, name, type, icon) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_type').value = type;
        document.getElementById('edit_icon').value = icon;
        document.getElementById('editCategoryModal').classList.add('open');
    }
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
