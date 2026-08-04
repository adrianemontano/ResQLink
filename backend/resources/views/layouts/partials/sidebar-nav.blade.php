<nav class="sidebar-nav">
    @if (auth()->user()->hasRole('admin'))
        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-label">Main</div>
            <a class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                <span class="sidebar-label">Dashboard</span>
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-label">Management</div>
            <a class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span class="sidebar-label">User Management</span>
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-label">Incidents</div>
            <a class="sidebar-item {{ request()->routeIs('admin.incidents.*') ? 'active' : '' }}" href="{{ route('admin.incidents.index') }}">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span class="sidebar-label">Incident Records</span>
            </a>
            <a class="sidebar-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <span class="sidebar-label">Reports</span>
            </a>
        </div>
    @elseif (auth()->user()->hasRole('dispatcher'))
        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-label">Main</div>
            <a class="sidebar-item {{ request()->routeIs('dispatcher.dashboard') ? 'active' : '' }}" href="{{ route('dispatcher.dashboard') }}">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                <span class="sidebar-label">Overview</span>
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-label">Incidents</div>
            <a class="sidebar-item {{ request()->routeIs('dispatcher.incidents.*') ? 'active' : '' }}" href="{{ route('dispatcher.incidents.index') }}">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span class="sidebar-label">Incidents</span>
            </a>
            <a class="sidebar-item {{ request()->routeIs('dispatcher.map') ? 'active' : '' }}" href="{{ route('dispatcher.map') }}">
                <svg viewBox="0 0 24 24"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                <span class="sidebar-label">Map View</span>
            </a>
        </div>
    @endif
</nav>
