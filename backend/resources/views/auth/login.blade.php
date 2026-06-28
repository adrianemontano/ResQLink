@extends('layouts.app', ['title' => 'Login | ResQLink'])

@section('body')
    <main class="auth-main">
        <section class="panel">
            <h1>ResQLink Web Login</h1>
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
