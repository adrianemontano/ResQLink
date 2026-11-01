/**
 * ResQLink Dispatcher Real-Time Notifications
 * Manages 10-12s polling, Web Audio alerts, unread badge, dropdown list, and live toast banners.
 */
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('notifications-widget');
    if (!widget) return;

    const bellBtn = document.getElementById('notification-bell-btn');
    const badge = document.getElementById('notification-badge');
    const dropdown = document.getElementById('notification-dropdown');
    const list = document.getElementById('notification-list');
    const markAllBtn = document.getElementById('mark-all-read-btn');
    const soundToggle = document.getElementById('notification-sound-toggle');
    const soundOn = document.getElementById('sound-icon-on');
    const soundOff = document.getElementById('sound-icon-off');
    const toastContainer = document.getElementById('notification-toast-container');

    let isMuted = localStorage.getItem('resqlink_sound_muted') === 'true';
    updateSoundIcons();

    let lastTimestamp = new Date().toISOString();
    let audioCtx = null;

    function initAudio() {
        if (!audioCtx && (window.AudioContext || window.webkitAudioContext)) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
    }

    function playAlertSound(isHighCritical) {
        if (isMuted) return;
        try {
            initAudio();
            if (!audioCtx) return;
            if (audioCtx.state === 'suspended') audioCtx.resume();

            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.type = isHighCritical ? 'sawtooth' : 'sine';
            osc.frequency.setValueAtTime(isHighCritical ? 880 : 587.33, audioCtx.currentTime); // A5 or D5
            gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + (isHighCritical ? 0.6 : 0.35));

            osc.start();
            osc.stop(audioCtx.currentTime + (isHighCritical ? 0.6 : 0.35));
        } catch (e) {
            console.warn('Audio alert blocked:', e);
        }
    }

    function updateSoundIcons() {
        if (isMuted) {
            soundOn.style.display = 'none';
            soundOff.style.display = 'inline-block';
        } else {
            soundOn.style.display = 'inline-block';
            soundOff.style.display = 'none';
        }
    }

    soundToggle.addEventListener('click', () => {
        isMuted = !isMuted;
        localStorage.setItem('resqlink_sound_muted', isMuted ? 'true' : 'false');
        updateSoundIcons();
        initAudio();
    });

    bellBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        bellBtn.setAttribute('aria-expanded', !isOpen);
        if (!isOpen) loadNotifications();
    });

    document.addEventListener('click', (e) => {
        if (!widget.contains(e.target)) {
            dropdown.style.display = 'none';
            bellBtn.setAttribute('aria-expanded', 'false');
        }
    });

    async function loadNotifications() {
        try {
            const res = await fetch('/dispatcher/notifications', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            updateBadge(data.unread_count);
            renderList(data.notifications);
        } catch (e) {
            console.error('Failed to load notifications', e);
        }
    }

    function updateBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    function renderList(notifications) {
        if (!notifications || notifications.length === 0) {
            list.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 12px;">No recent emergency alerts.</div>';
            return;
        }

        list.innerHTML = notifications.map(n => {
            const isHighOrCritical = ['High', 'Critical'].includes(n.data.severity);
            const borderStyle = isHighOrCritical ? 'border-left: 4px solid var(--maximum-red, #D92200); background: #FFF8F6;' : 'border-left: 4px solid var(--border);';
            const badgeClass = `severity-${n.data.severity ? n.data.severity.toLowerCase() : 'unrated'}`;

            return `
                <div class="notification-item" style="padding: 10px 14px; border-bottom: 1px solid var(--border); text-decoration: none; display: block; ${borderStyle} transition: background 0.15s;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <a href="${escapeHtml(n.data.detail_url)}" style="font-weight: 700; font-size: 13px; color: var(--rosewood);">${escapeHtml(n.data.identifier || 'Incident')} - ${escapeHtml(n.data.category)}</a>
                        <span class="badge ${badgeClass}" style="font-size: 10px; height: 18px; padding: 0 6px;">${escapeHtml(n.data.severity)}</span>
                    </div>
                    <div style="font-size: 12px; color: var(--text-label); margin-bottom: 2px;">
                        📍 ${escapeHtml(n.data.barangay)} &bull; ${escapeHtml(n.data.reporter_name || 'Reporter')}
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted);">
                        ${n.created_at}
                    </div>
                    ${n.read_at ? '' : `<button type="button" data-notification-read="${escapeHtml(n.id)}" class="secondary notification-read-button">Mark as read</button>`}
                </div>
            `;
        }).join('');
    }

    list.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-notification-read]');
        if (!button) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch(`/dispatcher/notifications/${encodeURIComponent(button.dataset.notificationRead)}/read`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        if (response.ok) await loadNotifications();
    });

    function showToast(alert) {
        const isHighOrCritical = ['High', 'Critical'].includes(alert.data.severity);
        const toast = document.createElement('div');
        toast.style.cssText = `
            pointer-events: auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(90, 0, 6, 0.22);
            border: 1px solid ${isHighOrCritical ? 'var(--maximum-red, #D92200)' : 'var(--border)'};
            border-left: 6px solid ${isHighOrCritical ? 'var(--maximum-red, #D92200)' : 'var(--philippine-orange, #F37100)'};
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            transform: translateX(360px);
        `;

        toast.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                <div>
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: ${isHighOrCritical ? 'var(--maximum-red)' : 'var(--tangelo)'}; letter-spacing: 0.05em;">
                        🚨 NEW EMERGENCY REPORT
                    </div>
                    <strong style="font-size: 14px; color: var(--rosewood); display: block; margin: 2px 0;">
                        ${escapeHtml(alert.data.identifier)}: ${escapeHtml(alert.data.category)} (${escapeHtml(alert.data.severity)})
                    </strong>
                    <div style="font-size: 12px; color: var(--text-label);">
                        Barangay: <strong>${escapeHtml(alert.data.barangay)}</strong>
                    </div>
                    <small style="color: var(--text-muted); font-size: 11px;">By ${escapeHtml(alert.data.reporter_name || 'Volunteer')}</small>
                </div>
                <button type="button" style="background: none; border: none; font-size: 16px; cursor: pointer; color: var(--text-muted); line-height: 1;">&times;</button>
            </div>
        `;

        toast.addEventListener('click', (e) => {
            if (e.target.tagName !== 'BUTTON') {
                window.location.href = alert.data.detail_url;
            } else {
                toast.remove();
            }
        });

        toastContainer.appendChild(toast);
        setTimeout(() => toast.style.transform = 'translateX(0)', 20);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(360px)';
            setTimeout(() => toast.remove(), 300);
        }, 8000);
    }

    markAllBtn.addEventListener('click', async () => {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/dispatcher/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                }
            });
            if (res.ok) {
                updateBadge(0);
                list.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 12px;">All notifications marked as read.</div>';
            }
        } catch (e) {
            console.error('Failed to mark all as read', e);
        }
    });

    async function poll() {
        try {
            const res = await fetch(`/dispatcher/notifications/poll?since=${encodeURIComponent(lastTimestamp)}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            lastTimestamp = data.timestamp;

            if (data.new_alerts && data.new_alerts.length > 0) {
                let hasHighOrCritical = false;
                data.new_alerts.forEach(alert => {
                    if (['High', 'Critical'].includes(alert.data.severity)) hasHighOrCritical = true;
                    showToast(alert);
                });
                playAlertSound(hasHighOrCritical);
                loadNotifications();
            } else {
                updateBadge(data.unread_count);
            }
        } catch (e) {
            // Silently retry on next interval
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    }

    // Initial load & poll loop every 11 seconds
    loadNotifications();
    setInterval(poll, 11000);
});
