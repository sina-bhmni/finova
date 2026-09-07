<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود | FINOVA</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body class="auth-body">

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-brand">
                <span class="auth-logo">💰</span>
                <h1>FINOVA</h1>
                <p>مدیریت هوشمند مالی شخصی</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/login" method="POST" class="auth-form" id="loginForm" novalidate>
                <?= Csrf::field() ?>

                <div class="form-group">
                    <label for="email">ایمیل</label>
                    <input type="email" id="email" name="email" placeholder="example@mail.com"
                           value="<?= e($old['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">رمز عبور</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">ورود</button>
            </form>

            <p class="auth-footer">
                حساب کاربری ندارید؟
                <a href="<?= BASE_URL ?>/register">ثبت‌نام کنید</a>
            </p>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/validation.js"></script>
</body>
</html>
