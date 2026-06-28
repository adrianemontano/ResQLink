@extends('layouts.app')

@section('body')
    <div class="shell">
        <header class="topbar">
            <a class="brand" href="{{ route('dashboard') }}">ResQLink</a>
            <nav class="nav">
                @auth
                    @if (auth()->user()->hasRole('admin'))
                        <a class="button secondary" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                        <a class="button secondary" href="{{ route('admin.users.index') }}">Users</a>
                    @elseif (auth()->user()->hasRole('dispatcher'))
                        <a class="button secondary" href="{{ route('dispatcher.dashboard') }}">Dispatcher Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="secondary">Logout</button>
                    </form>
                @endauth
            </nav>
        </header>

        <main class="main">
            @if (session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
@endsection
