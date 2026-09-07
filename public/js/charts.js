/**
 * FINOVA - Personal Finance Manager
 * رسم نمودارهای داشبورد با استفاده از Chart.js
 * متغیرهای chartLabels, chartIncomeData, chartExpenseData, categoryLabels, categoryTotals
 * پیش از این فایل و در خود صفحه dashboard.php تعریف شده‌اند.
 */

document.addEventListener('DOMContentLoaded', function () {

    // رنگ‌های هماهنگ با تم روشن پروژه
    const colorPrimary = '#4f46e5';
    const colorSuccess = '#16a34a';
    const colorDanger  = '#dc2626';
    const colorMuted   = '#6b7280';

    const palette = ['#4f46e5', '#0284c7', '#16a34a', '#d97706', '#dc2626', '#7c3aed', '#db2777', '#0891b2'];

    /* ---------------------------------------------------------
       نمودار خطی درآمد / هزینه ۶ ماه اخیر
       --------------------------------------------------------- */
    const lineCtx = document.getElementById('incomeExpenseChart');
    if (lineCtx && typeof chartLabels !== 'undefined') {
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'درآمد',
                        data: chartIncomeData,
                        borderColor: colorSuccess,
                        backgroundColor: 'rgba(22, 163, 74, 0.08)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                    },
                    {
                        label: 'هزینه',
                        data: chartExpenseData,
                        borderColor: colorDanger,
                        backgroundColor: 'rgba(220, 38, 38, 0.08)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { color: colorMuted, font: { family: 'Vazirmatn' } } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { color: colorMuted } },
                    x: { ticks: { color: colorMuted } },
                },
            },
        });
    }

    /* ---------------------------------------------------------
       نمودار دایره‌ای هزینه بر اساس دسته‌بندی
       --------------------------------------------------------- */
    const pieCtx = document.getElementById('expenseCategoryChart');
    if (pieCtx && typeof categoryLabels !== 'undefined' && categoryLabels.length) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryTotals,
                    backgroundColor: palette,
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { color: colorMuted, font: { family: 'Vazirmatn' } } },
                },
            },
        });
    }
});
