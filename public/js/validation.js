/**
 * FINOVA - Personal Finance Manager
 * اعتبارسنجی سمت کلاینت فرم‌های Auth (Login / Register)
 * توجه: این اعتبارسنجی جایگزین اعتبارسنجی سمت سرور نیست، فقط تجربه کاربری را بهتر می‌کند.
 */

function showFieldError(input, message) {
    clearFieldError(input);
    const error = document.createElement('div');
    error.className = 'field-error';
    error.textContent = message;
    input.parentElement.appendChild(error);
    input.style.borderColor = 'var(--color-danger)';
}

function clearFieldError(input) {
    input.style.borderColor = '';
    const existing = input.parentElement.querySelector('.field-error');
    if (existing) existing.remove();
}

function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            let valid = true;
            const email = document.getElementById('email');
            const password = document.getElementById('password');

            clearFieldError(email);
            clearFieldError(password);

            if (!email.value.trim() || !isValidEmail(email.value.trim())) {
                showFieldError(email, 'لطفاً یک ایمیل معتبر وارد کنید.');
                valid = false;
            }

            if (!password.value.trim()) {
                showFieldError(password, 'رمز عبور نمی‌تواند خالی باشد.');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            let valid = true;
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirmation');

            [name, email, password, passwordConfirm].forEach(clearFieldError);

            if (!name.value.trim()) {
                showFieldError(name, 'لطفاً نام خود را وارد کنید.');
                valid = false;
            }

            if (!email.value.trim() || !isValidEmail(email.value.trim())) {
                showFieldError(email, 'لطفاً یک ایمیل معتبر وارد کنید.');
                valid = false;
            }

            if (password.value.length < 6) {
                showFieldError(password, 'رمز عبور باید حداقل ۶ کاراکتر باشد.');
                valid = false;
            }

            if (passwordConfirm.value !== password.value) {
                showFieldError(passwordConfirm, 'تکرار رمز عبور مطابقت ندارد.');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }
});
