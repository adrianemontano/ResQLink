@extends('layouts.app', ['title' => 'Login | ResQLink'])

@section('body')
    <header class="brand-header">
        <div class="brand-mark">
            <svg viewBox="0 0 28 28"><path d="M14 6l2 5h5l-4 3 1.5 5L14 16l-4.5 3 1.5-5-4-3h5z"/></svg>
        </div>
        <div class="brand-name">ResQ<span>Link</span></div>
    </header>

    <main class="auth-main">
        <section class="panel">
            <h1>Welcome back</h1>
            <p>Administrator and dispatcher access only.</p>

            @if (session('status'))
                <div class="alert success" style="margin-top: 1rem;">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert error" style="margin-top: 1rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="field">
                    <label for="login">Username or Email</label>
                    <input id="login" name="login" value="{{ old('login') }}" autocomplete="username" autofocus>
                    @error('login')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password">
                    @error('password')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="inline-field">
                    <input id="remember" name="remember" type="checkbox" value="1">
                    <label for="remember">Remember this device</label>
                </div>

                <div class="actions">
                    <button type="submit">Login</button>
                </div>
            </form>
        </section>
    </main>
@endsection
