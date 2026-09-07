/**
 * FINOVA - Personal Finance Manager
 * افزودن میان‌برهای سریع به تمام input های تاریخ در کل پروژه
 * (روی input های بومی type="date" یک ردیف دکمه سریع اضافه می‌کند)
 */

document.addEventListener('DOMContentLoaded', function () {
    function formatDate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    document.querySelectorAll('input[type="date"]').forEach(function (input) {
        // جلوگیری از اضافه شدن تکراری میان‌بر
        if (input.dataset.quickpickAdded) return;
        input.dataset.quickpickAdded = 'true';

        const wrapper = document.createElement('div');
        wrapper.className = 'date-quickpicks';

        const today = new Date();
        const yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);
        const firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

        const options = [
            { label: 'امروز', value: formatDate(today) },
            { label: 'دیروز', value: formatDate(yesterday) },
            { label: 'اول ماه', value: formatDate(firstOfMonth) },
        ];

        options.forEach(function (opt) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'date-quickpick-btn';
            btn.textContent = opt.label;
            btn.addEventListener('click', function () {
                input.value = opt.value;
                input.dispatchEvent(new Event('change'));
            });
            wrapper.appendChild(btn);
        });

        input.insertAdjacentElement('afterend', wrapper);
    });
});
