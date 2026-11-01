@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/sidebar-toggle.js') }}"></script>
    @if (auth()->check() && auth()->user()->hasRole('dispatcher'))
        <script src="{{ asset('js/dispatcher-notifications.js') }}"></script>
    @endif
@endpush

@section('body')
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="sidebar-logo-row">
                    <div class="sidebar-logo-text">
                        <span class="sidebar-logo-mark">
                            <svg viewBox="0 0 28 28"><path d="M14 6l2 5h5l-4 3 1.5 5L14 16l-4.5 3 1.5-5-4-3h5z"/></svg>
                        </span>
                        <span class="sidebar-label">ResQLink</span>
                    </div>
                    <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-expanded="true" aria-label="Collapse sidebar">
                        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                </div>
                <div class="sidebar-logo-sub sidebar-label">
                    @if (auth()->user()->hasRole('admin')) Admin Control Panel @elseif (auth()->user()->hasRole('dispatcher')) Dispatch Control Center @endif
                </div>
            </div>

            @include('layouts.partials.sidebar-nav')

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <span class="sidebar-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="sidebar-label">
                        Logged in as
                        <strong>@if (auth()->user()->hasRole('dispatcher')) Dispatcher @else {{ auth()->user()->name }} @endif</strong>
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout">
                        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span class="sidebar-label">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="dashboard-main">
            @if (auth()->check() && auth()->user()->hasRole('dispatcher'))
                <div class="dashboard-topbar" style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: var(--space-3); padding-bottom: var(--space-2); border-bottom: 1px solid var(--border);">
                    <div style="font-size: 13px; color: var(--text-muted); margin-right: auto; display: flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #22C55E;"></span>
                        <strong>Dispatcher Live Center</strong> &bull; Cebu City Emergency Grid
                    </div>
                    @include('layouts.partials.notifications')
                </div>
            @endif

            @if (session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif

            @if ($errors->has('account'))
                <div class="alert error">{{ $errors->first('account') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
@endsection
