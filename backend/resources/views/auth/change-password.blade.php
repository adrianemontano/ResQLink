@extends('layouts.app', ['title' => 'Change Password | ResQLink'])

@section('body')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--surface-alt); padding: var(--space-4);">
    <div class="card" style="width: 100%; max-width: 440px; box-shadow: 0 10px 30px rgba(90, 0, 6, 0.12); border: 1px solid var(--border); border-top: 5px solid var(--rosewood);">
        <div style="text-align: center; margin-bottom: var(--space-4);">
            <div style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <span class="sidebar-logo-mark" style="background: var(--rosewood); color: #fff; width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 28 28" style="width: 20px; height: 20px; fill: currentColor;"><path d="M14 6l2 5h5l-4 3 1.5 5L14 16l-4.5 3 1.5-5-4-3h5z"/></svg>
                </span>
                <strong style="font-size: 20px; color: var(--rosewood);">ResQLink</strong>
            </div>
            <h1 style="font-size: 18px; margin: 0 0 6px 0; color: var(--text-primary);">Security Action Required</h1>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                You are logging in with a temporary password. For account protection, please set a new personal password before continuing.
            </p>
        </div>

        @if (session('status'))
            <div class="alert success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            {{-- Current Password --}}
            <div style="margin-bottom: var(--space-3);">
                <label for="current_password" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Current Temporary Password <span class="required-asterisk" aria-hidden="true">*</span></label>
                <div style="position: relative;">
                    <input id="current_password" type="password" name="current_password" required autofocus
                           style="width: 100%; padding-right: 40px;" placeholder="Enter temporary password">
                    <button type="button" class="btn-toggle-pw" data-target="current_password"
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                        👁️
                    </button>
                </div>
                @error('current_password')
                    <div class="error-text" style="color: var(--maximum-red); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- New Password --}}
            <div style="margin-bottom: var(--space-3);">
                <label for="password" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">New Password <span class="required-asterisk" aria-hidden="true">*</span></label>
                <div style="position: relative;">
                    <input id="password" type="password" name="password" required minlength="8"
                           style="width: 100%; padding-right: 40px;" placeholder="Minimum 8 characters">
                    <button type="button" class="btn-toggle-pw" data-target="password"
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                        👁️
                    </button>
                </div>
                
                {{-- Strength Indicator --}}
                <div style="margin-top: 6px;">
                    <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 2px;">
                        <span style="color: var(--text-muted);">Password Strength:</span>
                        <strong id="strength-label" style="color: var(--text-muted);">Too Short</strong>
                    </div>
                    <div style="height: 5px; width: 100%; background: var(--border); border-radius: 3px; overflow: hidden;">
                        <div id="strength-bar" style="height: 100%; width: 0%; background: var(--maximum-red); transition: width 0.25s, background-color 0.25s;"></div>
                    </div>
                </div>

                @error('password')
                    <div class="error-text" style="color: var(--maximum-red); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Confirm New Password --}}
            <div style="margin-bottom: var(--space-4);">
                <label for="password_confirmation" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Confirm New Password <span class="required-asterisk" aria-hidden="true">*</span></label>
                <div style="position: relative;">
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8"
                           style="width: 100%; padding-right: 40px;" placeholder="Re-enter new password">
                    <button type="button" class="btn-toggle-pw" data-target="password_confirmation"
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                        👁️
                    </button>
                </div>
            </div>

            <button type="submit" class="button primary" style="width: 100%; justify-content: center; padding: 10px;">
                Save New Password & Continue
            </button>
        </form>

        <div style="margin-top: var(--space-4); text-align: center; border-top: 1px solid var(--border); padding-top: var(--space-3);">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background: none; border: none; font-size: 12px; color: var(--text-muted); cursor: pointer; text-decoration: underline;">
                    Log out and return later
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Show/Hide password toggle
    document.querySelectorAll('.btn-toggle-pw').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            if (!input) return;
            const isPw = input.type === 'password';
            input.type = isPw ? 'text' : 'password';
            btn.textContent = isPw ? '🔒' : '👁️';
        });
    });

    // Password strength meter
    const pwInput = document.getElementById('password');
    const bar = document.getElementById('strength-bar');
    const label = document.getElementById('strength-label');

    pwInput.addEventListener('input', () => {
        const val = pwInput.value;
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        if (val.length === 0) {
            bar.style.width = '0%';
            label.textContent = 'None';
            label.style.color = 'var(--text-muted)';
        } else if (val.length < 8) {
            bar.style.width = '20%';
            bar.style.backgroundColor = 'var(--maximum-red)';
            label.textContent = 'Too Short (min 8)';
            label.style.color = 'var(--maximum-red)';
        } else if (score <= 2) {
            bar.style.width = '50%';
            bar.style.backgroundColor = 'var(--philippine-orange)';
            label.textContent = 'Fair';
            label.style.color = 'var(--philippine-orange)';
        } else if (score === 3) {
            bar.style.width = '75%';
            bar.style.backgroundColor = '#FBBF24';
            label.textContent = 'Good';
            label.style.color = '#B45309';
        } else {
            bar.style.width = '100%';
            bar.style.backgroundColor = 'var(--success, #476B45)';
            label.textContent = 'Strong';
            label.style.color = 'var(--success, #476B45)';
        }
    });
});
</script>
@endsection
