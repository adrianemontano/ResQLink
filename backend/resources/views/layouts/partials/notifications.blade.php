<div
    class="notifications-widget"
    id="notifications-widget"
    data-index-url="{{ route('dispatcher.notifications.index') }}"
    data-poll-url="{{ route('dispatcher.notifications.poll') }}"
    data-mark-all-url="{{ route('dispatcher.notifications.mark-all-read') }}"
    data-read-url-template="{{ route('dispatcher.notifications.read', ['notification' => '__NOTIFICATION__']) }}"
>
    <button
        type="button"
        id="notification-bell-btn"
        class="notification-bell-btn"
        aria-expanded="false"
        aria-controls="notification-dropdown"
        aria-label="Notifications, 0 unread"
    >
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span id="notification-badge" class="notification-badge" aria-hidden="true" hidden></span>
    </button>

    <section
        id="notification-dropdown"
        class="notification-dropdown"
        aria-label="Recent notifications"
        hidden
    >
        <header class="notification-dropdown-header">
            <strong>Recent notifications</strong>
            <button type="button" id="mark-all-read-btn" class="notification-mark-all">
                Mark all as read
            </button>
        </header>
        <div id="notification-error" class="notification-error" role="alert" hidden></div>
        <div id="notification-list" class="notification-list" aria-live="polite">
            <div class="notification-empty" role="status">Loading notifications…</div>
        </div>
    </section>
</div>

<div id="notification-toast-container" class="notification-toast-container" aria-live="polite"></div>
