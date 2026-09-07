/**
 * FINOVA - Personal Finance Manager
 * ابزارهای عمومی AJAX پروژه
 * توابع اختصاصی هر بخش (مثل فیلتر زنده تراکنش‌ها یا اعلان‌ها)
 * در فایل‌های مربوط به همان بخش اضافه می‌شوند.
 */

/**
 * ارسال درخواست ساده با Fetch API
 */
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (err) {
        console.error('خطا در ارتباط با سرور:', err);
        return null;
    }
}

// بستن مودال‌ها با کلیک روی پس‌زمینه
document.addEventListener('click', function (e) {
    if (e.target.classList && e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
    }
});
