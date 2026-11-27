@extends('layouts.app', ['title' => 'Change Password | ResQLink'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/password-change.css') }}?v=20261127">
@endpush

@section('body')
<div class="auth-shell">
    <main class="auth-shell-main">
        <div class="card auth-card">
            <header class="auth-card-header">
                <img class="auth-card-logo" src="{{ asset('images/resqlink-logo.png') }}" alt="ResQLink">
                <h1>Security Action Required</h1>
                <p>You are logging in with a temporary password. For account protection, please set a new personal password before continuing.</p>
            </header>

            <form method="POST" action="{{ route('password.update') }}" novalidate>
                @csrf

                <div class="auth-field">
                    <label for="current_password">Current Temporary Password <span class="required-asterisk" aria-hidden="true">*</span></label>
                    <div class="password-field">
                        <input id="current_password" type="password" name="current_password" required autofocus placeholder="Enter temporary password" autocomplete="current-password">
                        <button type="button" class="password-toggle" data-password-toggle="current_password" aria-label="Show password" aria-pressed="false">Show</button>
                    </div>
                    @error('current_password')
                        <div class="error-text field-error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password">New Password <span class="required-asterisk" aria-hidden="true">*</span></label>
                    <div class="password-field">
                        <input id="password" type="password" name="password" required minlength="8" placeholder="Minimum 8 characters" autocomplete="new-password" aria-describedby="strength-label">
                        <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false">Show</button>
                    </div>

                    <div class="password-strength" data-strength="none">
                        <div class="password-strength-label">
                            <span>Password strength:</span>
                            <strong id="strength-label" aria-live="polite">None</strong>
                        </div>
                        <div class="password-strength-track"><div class="password-strength-bar"></div></div>
                    </div>

                    @error('password')
                        <div class="error-text field-error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field auth-field-last">
                    <label for="password_confirmation">Confirm New Password <span class="required-asterisk" aria-hidden="true">*</span></label>
                    <div class="password-field">
                        <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" placeholder="Re-enter new password" autocomplete="new-password">
                        <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false">Show</button>
                    </div>
                </div>

                <button type="submit" class="button primary auth-submit">Save New Password &amp; Continue</button>
            </form>

            <footer class="auth-card-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="auth-link-button">Log out and return later</button>
                </form>
            </footer>
        </div>
    </main>

    <x-footer />
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            button.textContent = reveal ? 'Hide' : 'Show';
            button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            button.setAttribute('aria-pressed', String(reveal));
        });
    });

    // Strength levels are styled in password-change.css through data-strength.
    const input = document.getElementById('password');
    const meter = document.querySelector('.password-strength');
    const label = document.getElementById('strength-label');
    const levels = { none: 'None', short: 'Too short (min 8)', fair: 'Fair', good: 'Good', strong: 'Strong' };

    input.addEventListener('input', () => {
        const value = input.value;
        const score = [value.length >= 8, /[A-Z]/.test(value), /[0-9]/.test(value), /[^A-Za-z0-9]/.test(value)]
            .filter(Boolean).length;
        const level = value.length === 0 ? 'none'
            : value.length < 8 ? 'short'
            : score <= 2 ? 'fair'
            : score === 3 ? 'good'
            : 'strong';
        meter.dataset.strength = level;
        label.textContent = levels[level];
    });
});
</script>
@endsection
