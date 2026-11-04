/**
 * Dispatcher notification dropdown, unread count, and live incident alerts.
 */
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('notifications-widget');
    if (!widget) return;

    const bell = document.getElementById('notification-bell-btn');
    const badge = document.getElementById('notification-badge');
    const dropdown = document.getElementById('notification-dropdown');
    const list = document.getElementById('notification-list');
    const errorMessage = document.getElementById('notification-error');
    const markAllButton = document.getElementById('mark-all-read-btn');
    const toastContainer = document.getElementById('notification-toast-container');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    let lastTimestamp = new Date().toISOString();

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                ...(options.headers || {}),
            },
        });
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'The notification request failed.');
        }
        return data;
    }

    function updateUnreadCount(count) {
        const unreadCount = Math.max(0, Number(count) || 0);
        badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
        badge.hidden = unreadCount === 0;
        bell.setAttribute('aria-label', `Notifications, ${unreadCount} unread`);
    }

    function showError(message, revealDropdown = false) {
        errorMessage.textContent = message;
        errorMessage.hidden = false;
        if (revealDropdown) {
            dropdown.hidden = false;
            bell.setAttribute('aria-expanded', 'true');
        }
    }

    function clearError() {
        errorMessage.textContent = '';
        errorMessage.hidden = true;
    }

    async function loadNotifications() {
        try {
            const data = await requestJson(widget.dataset.indexUrl);
            updateUnreadCount(data.unread_count);
            renderNotifications(data.notifications);
            clearError();
        } catch (error) {
            console.error('Failed to load dispatcher notifications.', error);
            showError('Notifications could not be loaded. Please try again.');
        }
    }

    function renderNotifications(notifications) {
        if (!Array.isArray(notifications) || notifications.length === 0) {
            list.innerHTML = '<div class="notification-empty" role="status">No recent notifications.</div>';
            return;
        }

        list.innerHTML = notifications.map((notification) => {
            const data = notification.data || {};
            const severity = ['low', 'moderate', 'high', 'critical'].includes(
                String(data.severity || '').toLowerCase()
            ) ? String(data.severity).toLowerCase() : 'unrated';
            const unreadClass = notification.read_at ? '' : ' unread';
            const readButton = notification.read_at ? '' : `
                <button type="button" class="notification-read-button"
                    data-notification-read="${escapeHtml(notification.id)}">
                    Mark as read
                </button>`;

            return `
                <article class="notification-item severity-${severity}${unreadClass}">
                    <a class="notification-item-link"
                        data-notification-link="${escapeHtml(notification.id)}"
                        href="${escapeHtml(data.detail_url || '#')}">
                        <div class="notification-item-heading">
                            <span class="notification-category">
                                ${escapeHtml(data.category || 'Incident')}
                                <span class="notification-identifier">${escapeHtml(data.identifier || '')}</span>
                            </span>
                            <span class="badge severity-${severity}">
                                ${escapeHtml(data.severity || 'Unrated')}
                            </span>
                        </div>
                        <div class="notification-item-meta">
                            <span>Barangay: ${escapeHtml(data.barangay || 'Not provided')}</span>
                        </div>
                        <div class="notification-item-time">${escapeHtml(notification.created_at || '')}</div>
                    </a>
                    ${readButton}
                </article>`;
        }).join('');
    }

    function notificationReadUrl(id) {
        return widget.dataset.readUrlTemplate.replace('__NOTIFICATION__', encodeURIComponent(id));
    }

    async function markAsRead(id) {
        return requestJson(notificationReadUrl(id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        });
    }

    function closeDropdown(returnFocus = false) {
        dropdown.hidden = true;
        bell.setAttribute('aria-expanded', 'false');
        if (returnFocus) bell.focus();
    }

    bell.addEventListener('click', () => {
        const opening = dropdown.hidden;
        dropdown.hidden = !opening;
        bell.setAttribute('aria-expanded', String(opening));
        if (opening) loadNotifications();
    });

    document.addEventListener('click', (event) => {
        if (!event.composedPath().includes(widget)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !dropdown.hidden) closeDropdown(true);
    });

    list.addEventListener('click', async (event) => {
        const readButton = event.target.closest('[data-notification-read]');
        const notificationLink = event.target.closest('[data-notification-link]');

        if (readButton) {
            readButton.disabled = true;
            try {
                const data = await markAsRead(readButton.dataset.notificationRead);
                updateUnreadCount(data.unread_count);
                await loadNotifications();
            } catch (error) {
                console.error('Failed to mark notification as read.', error);
                showError('This notification could not be marked as read.');
            } finally {
                readButton.disabled = false;
            }
            return;
        }

        if (!notificationLink || notificationLink.getAttribute('aria-disabled') === 'true') return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            markAsRead(notificationLink.dataset.notificationLink)
                .then((data) => updateUnreadCount(data.unread_count))
                .catch((error) => {
                    console.error('Failed to mark notification as read.', error);
                    showError('The incident notification could not be marked as read.', true);
                });
            return;
        }
        event.preventDefault();
        const destination = notificationLink.href;
        notificationLink.setAttribute('aria-disabled', 'true');
        try {
            const data = await markAsRead(notificationLink.dataset.notificationLink);
            updateUnreadCount(data.unread_count);
            window.location.assign(destination);
        } catch (error) {
            notificationLink.removeAttribute('aria-disabled');
            console.error('Failed to mark notification as read.', error);
            showError('The incident could not be opened because its notification was not marked as read.');
        }
    });

    markAllButton.addEventListener('click', async () => {
        markAllButton.disabled = true;
        try {
            const data = await requestJson(widget.dataset.markAllUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
            });
            updateUnreadCount(data.unread_count);
            await loadNotifications();
        } catch (error) {
            console.error('Failed to mark all notifications as read.', error);
            showError('Notifications could not be marked as read.');
        } finally {
            markAllButton.disabled = false;
        }
    });

    function animateBell() {
        bell.classList.remove('has-new-notification');
        void bell.offsetWidth;
        bell.classList.add('has-new-notification');
        window.setTimeout(() => bell.classList.remove('has-new-notification'), 1500);
    }

    function showToast(alert) {
        const data = alert.data || {};
        const isHighOrCritical = ['High', 'Critical'].includes(data.severity);
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.style.cssText = `
            pointer-events: auto;
            background: var(--surface);
            border: 1px solid ${isHighOrCritical ? 'var(--maximum-red)' : 'var(--border)'};
            border-left: 6px solid ${isHighOrCritical ? 'var(--maximum-red)' : 'var(--philippine-orange)'};
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            cursor: pointer;
            padding: 12px 14px;
            transform: translateX(360px);
            transition: transform .3s ease, opacity .3s ease;
        `;
        toast.innerHTML = `
            <div class="notification-toast-heading">
                <div>
                    <div class="notification-toast-label">New emergency report</div>
                    <strong>${escapeHtml(data.identifier || 'Incident')}: ${escapeHtml(data.category || 'Incident')} (${escapeHtml(data.severity || 'Unrated')})</strong>
                    <div>Barangay: <strong>${escapeHtml(data.barangay || 'Not provided')}</strong></div>
                </div>
                <button type="button" class="notification-toast-dismiss" aria-label="Dismiss notification">&times;</button>
            </div>`;
        toast.addEventListener('click', async (event) => {
            if (event.target.closest('.notification-toast-dismiss')) {
                toast.remove();
                return;
            }
            try {
                const result = await markAsRead(alert.id);
                updateUnreadCount(result.unread_count);
                window.location.assign(data.detail_url);
            } catch (error) {
                console.error('Failed to open incident from notification.', error);
                showError('The incident could not be opened because its notification was not marked as read.', true);
            }
        });
        toastContainer.appendChild(toast);
        window.setTimeout(() => { toast.style.transform = 'translateX(0)'; }, 20);
        window.setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(360px)';
            window.setTimeout(() => toast.remove(), 300);
        }, 8000);
    }

    async function pollNotifications() {
        try {
            const url = `${widget.dataset.pollUrl}?since=${encodeURIComponent(lastTimestamp)}`;
            const data = await requestJson(url);
            lastTimestamp = data.timestamp;
            updateUnreadCount(data.unread_count);

            if (Array.isArray(data.new_alerts) && data.new_alerts.length > 0) {
                data.new_alerts.forEach(showToast);
                animateBell();
                if (!dropdown.hidden) await loadNotifications();
            }
        } catch (error) {
            console.error('Failed to check for new dispatcher notifications.', error);
        }
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        })[character]);
    }

    loadNotifications();
    window.setInterval(pollNotifications, 11000);
});
