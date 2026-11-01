<div class="notifications-widget" id="notifications-widget" style="position: relative; display: inline-flex; align-items: center; gap: 8px;">
    {{-- Sound Mute/Unmute Toggle --}}
    <button type="button" id="notification-sound-toggle" class="btn-icon" title="Toggle alert sound" style="background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 6px; border-radius: 4px; display: inline-flex; align-items: center;">
        <svg id="sound-icon-on" viewBox="0 0 24 24" style="width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2;"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
        <svg id="sound-icon-off" viewBox="0 0 24 24" style="width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2; display: none;"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>
    </button>

    {{-- Notification Bell Button --}}
    <button type="button" id="notification-bell-btn" class="btn-icon" aria-expanded="false" aria-label="Incident Notifications" style="position: relative; background: none; border: 1px solid var(--border); border-radius: 50%; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--rosewood); background: #ffffff;">
        <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span id="notification-badge" class="badge-count" style="display: none; position: absolute; top: -4px; right: -4px; background: var(--maximum-red, #D92200); color: #ffffff; font-size: 10px; font-weight: 700; border-radius: 10px; padding: 1px 6px; border: 2px solid #ffffff; line-height: 1;">0</span>
    </button>

    {{-- Dropdown Menu --}}
    <div id="notification-dropdown" class="notification-dropdown" style="display: none; position: absolute; top: 44px; right: 0; width: 340px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 8px 24px rgba(43, 29, 27, 0.15); z-index: 1050; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: var(--surface-alt); border-bottom: 1px solid var(--border);">
            <strong style="font-size: 13px; color: var(--rosewood);">Emergency Notifications</strong>
            <button type="button" id="mark-all-read-btn" style="background: none; border: none; font-size: 11px; color: var(--sangria); font-weight: 600; cursor: pointer; padding: 0;">Mark all as read</button>
        </div>

        <div id="notification-list" style="max-height: 320px; overflow-y: auto;">
            <div id="notification-empty" style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 12px;">
                No unread emergency alerts.
            </div>
        </div>
    </div>
</div>

{{-- Toast Container for Live Alerts --}}
<div id="notification-toast-container" class="notification-toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; width: 340px; pointer-events: none;">
</div>
