<?php require APP_PATH . '/views/partials/header.php'; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><p><?= e($success) ?></p></div>
<?php endif; ?>

<div class="card form-card">
    <form action="<?= BASE_URL ?>/settings" method="POST">
        <?= Csrf::field() ?>

        <div class="form-group">
            <label for="language">زبان</label>
            <select id="language" name="language">
                <option value="fa" <?= $settings['language']==='fa'?'selected':'' ?>>فارسی</option>
                <option value="en" <?= $settings['language']==='en'?'selected':'' ?>>English</option>
            </select>
        </div>

        <div class="form-group">
            <label for="theme">پوسته</label>
            <select id="theme" name="theme">
                <option value="light" <?= $settings['theme']==='light'?'selected':'' ?>>روشن</option>
                <option value="dark"  <?= $settings['theme']==='dark'?'selected':'' ?>>تیره</option>
            </select>
            <p class="text-muted" style="font-size:11px; margin-top:6px;">
                در نسخه فعلی، ظاهر برنامه به‌صورت روشن (Light) طراحی شده است.
            </p>
        </div>

        <div class="form-group">
            <label for="timezone">منطقه زمانی</label>
            <select id="timezone" name="timezone">
                <option value="Asia/Tehran" <?= $settings['timezone']==='Asia/Tehran'?'selected':'' ?>>تهران (Asia/Tehran)</option>
                <option value="UTC" <?= $settings['timezone']==='UTC'?'selected':'' ?>>UTC</option>
            </select>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="notifications_enabled" <?= $settings['notifications_enabled'] ? 'checked' : '' ?>>
                فعال‌سازی اعلان‌های داخلی
            </label>
        </div>

        <button type="submit" class="btn btn-primary">ذخیره تنظیمات</button>
    </form>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
