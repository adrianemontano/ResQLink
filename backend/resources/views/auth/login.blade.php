<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
<div class="rq-login">

  <section class="rq-left">
    <div class="rq-left-inner">
      <div class="rq-logo">
        <svg width="130" height="130" viewBox="0 0 240 240" aria-hidden="true">
          <ellipse cx="120" cy="180" rx="104" ry="32" fill="none" stroke="#F37100" stroke-width="8" opacity="0.3"/>
          <ellipse cx="120" cy="180" rx="72" ry="22" fill="none" stroke="#F37100" stroke-width="8" opacity="0.6"/>
          <ellipse cx="120" cy="180" rx="40" ry="12" fill="none" stroke="#F37100" stroke-width="8"/>
          <path d="M120 182 C120 182 70 134 70 104 A50 50 0 1 1 170 104 C170 134 120 182 120 182 Z" fill="#5A0006"/>
          <circle cx="120" cy="104" r="19" fill="#FBF3EA"/>
          <circle cx="120" cy="104" r="8" fill="#D92200"/>
        </svg>
        <div>
          <div class="rq-wordmark"><span class="r1">Res</span><span class="r2">Q</span><span class="r3">Link</span></div>
          <div class="rq-tagline">VOLUNTEER EMERGENCY ALERT AND DISPATCH</div>
        </div>
      </div>

      <h1 class="rq-headline">Fast alerts. Coordinated response.</h1>
      <p class="rq-desc">ResQLink connects barangay volunteers and dispatchers so emergency reports are received and acted on quickly.</p>

      <ul class="rq-features">
        <li><span class="rq-ficon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F3500A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg></span>Real-time incident alerts</li>
        <li><span class="rq-ficon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F3500A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></span>GPS location pinning</li>
        <li><span class="rq-ficon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F3500A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg></span>Fast dispatch coordination</li>
      </ul>
    </div>
    <div class="rq-left-footer">CCDRRMO - Cebu City</div>
  </section>

  <section class="rq-right">
    <form class="rq-form" method="POST" action="{{ route('login.store') }}">
      @csrf
      <div>
        <h2>Welcome back</h2>
        <p class="rq-sub">Administrator and dispatcher access only.</p>
      </div>

      <div class="rq-field">
        <label for="login">Username or Email <span class="req">*</span></label>
        <div class="rq-input">
          <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" @if ($errors->has('login')) aria-invalid="true" aria-describedby="login-error" @endif>
        </div>
        @error('login')
          <div id="login-error" class="rq-field-error">{{ $message }}</div>
        @enderror
      </div>

      <div class="rq-field">
        <label for="password">Password <span class="req">*</span></label>
        <div class="rq-input">
          <input id="password" type="password" name="password" required autocomplete="current-password" @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif>
          <button type="button" class="rq-eye" aria-label="Show password" onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password'">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6B5A55" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        @error('password')
          <div id="password-error" class="rq-field-error">{{ $message }}</div>
        @enderror
      </div>

      <label class="rq-remember"><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> Remember this device</label>

      <button type="submit" class="rq-btn" id="login-submit">
        <span class="btn-text">Login</span>
        <span class="rq-spinner" style="display: none;" aria-hidden="true"></span>
      </button>

      <p class="rq-forgot">Forgot your password? Contact your administrator.</p>
    </form>

    <x-footer />
  </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('.rq-form');
        var submitBtn = document.getElementById('login-submit');
        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                var btnText = submitBtn.querySelector('.btn-text');
                var spinner = submitBtn.querySelector('.rq-spinner');
                if (btnText && spinner) {
                    btnText.textContent = 'Signing in...';
                    spinner.style.display = 'inline-block';
                    submitBtn.classList.add('is-loading');
                }
            });
        }
    });
</script>
</body>
</html>
