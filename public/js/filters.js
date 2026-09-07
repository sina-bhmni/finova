/**
 * FINOVA - Personal Finance Manager
 * منطق سمت کلاینت صفحه تراکنش‌ها:
 * ۱) در فرم افزودن/ویرایش: نمایش پویا فیلدها بر اساس نوع تراکنش + محاسبه زنده موجودی
 * ۲) در لیست تراکنش‌ها: بهبود تجربه فیلتر کردن
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ---------------------------------------------------------
       بخش فرم تراکنش (Create / Edit)
       --------------------------------------------------------- */
    const typeRadios      = document.querySelectorAll('input[name="type"]');
    const categoryGroup   = document.getElementById('categoryGroup');
    const transferToGroup = document.getElementById('transferToGroup');
    const walletLabel     = document.getElementById('walletLabel');
    const walletSelect    = document.getElementById('wallet_id');
    const amountInput     = document.getElementById('amount');
    const livePreview     = document.getElementById('livePreview');
    const previewAmount   = document.getElementById('previewAmount');

    function updateFormVisibility() {
        if (!typeRadios.length) return;

        const selectedType = document.querySelector('input[name="type"]:checked')?.value || 'expense';

        if (selectedType === 'transfer') {
            categoryGroup.style.display = 'none';
            transferToGroup.style.display = 'block';
            walletLabel.textContent = 'حساب مبدأ';
        } else {
            categoryGroup.style.display = 'block';
            transferToGroup.style.display = 'none';
            walletLabel.textContent = 'حساب';

            // فیلتر کردن دسته‌بندی‌ها بر اساس نوع انتخاب‌شده (درآمد/هزینه)
            const categorySelect = document.getElementById('category_id');
            let firstVisible = null;
            Array.from(categorySelect.options).forEach(function (opt) {
                const matches = opt.dataset.type === selectedType;
                opt.hidden = !matches;
                if (matches && firstVisible === null) firstVisible = opt.value;
            });
            if (categorySelect.selectedOptions[0]?.hidden && firstVisible) {
                categorySelect.value = firstVisible;
            }
        }

        updateLivePreview();
    }

    function updateLivePreview() {
        if (!walletSelect || !amountInput || !livePreview) return;

        const selectedType = document.querySelector('input[name="type"]:checked')?.value || 'expense';
        const walletId = walletSelect.value;
        const amount = parseFloat(amountInput.value) || 0;
        const currentBalance = walletBalances[walletId] ?? 0;

        let newBalance = currentBalance;
        if (selectedType === 'income') {
            newBalance = currentBalance + amount;
        } else if (selectedType === 'expense' || selectedType === 'transfer') {
            newBalance = currentBalance - amount;
        }

        if (amount > 0) {
            livePreview.style.display = 'flex';
            previewAmount.textContent = new Intl.NumberFormat('fa-IR').format(newBalance) + ' تومان';
            previewAmount.style.color = newBalance < 0 ? 'var(--color-danger)' : 'var(--color-success)';
        } else {
            livePreview.style.display = 'none';
        }
    }

    if (typeRadios.length) {
        typeRadios.forEach(radio => radio.addEventListener('change', updateFormVisibility));
        walletSelect?.addEventListener('change', updateLivePreview);
        amountInput?.addEventListener('input', updateLivePreview);
        updateFormVisibility();
    }

    /* ---------------------------------------------------------
       بخش لیست تراکنش‌ها (فیلتر)
       --------------------------------------------------------- */
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        // اعمال خودکار فیلتر هنگام تغییر select ها برای تجربه سریع‌تر
        filterForm.querySelectorAll('select').forEach(function (select) {
            select.addEventListener('change', function () {
                filterForm.submit();
            });
        });
    }
});
