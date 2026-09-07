/**
 * FINOVA - Personal Finance Manager
 * منطق دراپ‌داون زنگوله اعلان‌ها در تاپ‌بار
 */

document.addEventListener('DOMContentLoaded', function () {
    const bellToggle   = document.getElementById('bellToggle');
    const dropdown     = document.getElementById('notificationDropdown');
    const listEl       = document.getElementById('notificationList');
    const badgeEl      = document.getElementById('bellBadge');
    const markAllBtn   = document.getElementById('markAllReadBtn');
    const csrfToken     = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!bellToggle) return;

    const typeIcons = { info: 'ℹ️', success: '✅', warning: '⚠️' };

    function timeAgo(dateStr) {
        const diffMs = Date.now() - new Date(dateStr.replace(' ', 'T'));
        const mins = Math.floor(diffMs / 60000);
        if (mins < 1) return 'همین الان';
        if (mins < 60) return mins + ' دقیقه پیش';
        const hours = Math.floor(mins / 60);
        if (hours < 24) return hours + ' ساعت پیش';
        return Math.floor(hours / 24) + ' روز پیش';
    }

    async function loadNotifications() {
        const data = await apiRequest(BASE_URL_JS + '/notifications');
        if (!data) return;

        badgeEl.textContent = data.unread_count;
        badgeEl.style.display = data.unread_count > 0 ? 'flex' : 'none';

        if (!data.notifications.length) {
            listEl.innerHTML = '<p class="text-muted" style="padding:16px; font-size:12px;">اعلانی وجود ندارد.</p>';
            return;
        }

        listEl.innerHTML = data.notifications.map(function (n) {
            const icon = typeIcons[n.type] || 'ℹ️';
            const readClass = n.is_read == 1 ? 'read' : 'unread';
            return `
                <div class="notification-item ${readClass}" data-id="${n.id}">
                    <span class="notification-icon">${icon}</span>
                    <div class="notification-body">
                        <p>${n.message}</p>
                        <span class="text-muted">${timeAgo(n.created_at)}</span>
                    </div>
                </div>`;
        }).join('');

        listEl.querySelectorAll('.notification-item.unread').forEach(function (item) {
            item.addEventListener('click', async function () {
                const id = item.dataset.id;
                await apiRequest(BASE_URL_JS + '/notifications/read', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id + '&csrf_token=' + encodeURIComponent(csrfToken),
                });
                item.classList.remove('unread');
                item.classList.add('read');
                loadNotifications();
            });
        });
    }

    bellToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('open');
        if (dropdown.classList.contains('open')) loadNotifications();
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && e.target !== bellToggle) {
            dropdown.classList.remove('open');
        }
    });

    markAllBtn?.addEventListener('click', async function () {
        await apiRequest(BASE_URL_JS + '/notifications/read-all', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'csrf_token=' + encodeURIComponent(csrfToken),
        });
        loadNotifications();
    });

    // بارگذاری اولیه شمارنده هنگام لود صفحه
    loadNotifications();
});
