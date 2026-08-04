@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/sidebar-toggle.js') }}"></script>
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
                    {{ auth()->user()->hasRole('admin') ? 'Admin Control Panel' : 'Dispatch Control Center' }}
                </div>
            </div>

            @include('layouts.partials.sidebar-nav')

            <div class="sidebar-footer">
                <div class="sidebar-user sidebar-label">
                    Logged in as
                    <strong>{{ auth()->user()->name }}</strong>
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
            @if (session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
@endsection
