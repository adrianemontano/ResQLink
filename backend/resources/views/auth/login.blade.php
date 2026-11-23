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
<x-toast />
<div class="rq-login">

  <section class="rq-left">
    <div class="rq-left-inner">
      <div class="rq-logo">
        <img src="{{ asset('images/resqlink-logo-white.png') }}" alt="ResQLink" class="rq-logo-img">
      </div>

      <h1 class="rq-headline">Fast alerts. Coordinated response.</h1>
      <p class="rq-desc">ResQLink connects barangay volunteers and dispatchers so emergency reports are received and acted on quickly.</p>

      <ul class="rq-features">
        <li>
          <span class="rq-ficon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
          </span>
          Real-time incident alerts
        </li>
        <li>
          <span class="rq-ficon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
          </span>
          GPS location pinning
        </li>
        <li>
          <span class="rq-ficon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
          </span>
          Fast dispatch coordination
        </li>
      </ul>
    </div>
    <div class="rq-left-footer">CCDRRMO - Cebu City</div>
  </section>

  <section class="rq-right">
    <form class="rq-form" method="POST" action="{{ route('login.store') }}" novalidate>
      @csrf
      <div>
        <h2>Welcome back</h2>
        <p class="rq-sub">Administrator and dispatcher access only.</p>
      </div>

      <div class="rq-field">
        <label for="login">Username or Email <span class="req">*</span></label>
        <div class="rq-input">
          <input id="login" type="text" name="login" value="{{ old('login') }}" autofocus autocomplete="username" @if (session('error') || $errors->has('login')) aria-invalid="true" @endif>
        </div>
        <div id="login-error" class="rq-field-error" role="alert" style="display: none;"></div>
        @if ($errors->has('login') && !str_contains(strtolower($errors->first('login')), 'credential') && !str_contains(strtolower($errors->first('login')), 'deactivated'))
          <div class="rq-field-error" role="alert">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ $errors->first('login') }}</span>
          </div>
        @endif
      </div>

      <div class="rq-field">
        <label for="password">Password <span class="req">*</span></label>
        <div class="rq-input">
          <input id="password" type="password" name="password" autocomplete="current-password" @if (session('error') || $errors->has('password')) aria-invalid="true" @endif>
          <button type="button" class="rq-eye" aria-label="Show password" onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password';this.setAttribute('aria-label', i.type==='password'?'Show password':'Hide password')">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div id="password-error" class="rq-field-error" role="alert" style="display: none;"></div>
        @if ($errors->has('password') && !str_contains(strtolower($errors->first('password')), 'credential'))
          <div class="rq-field-error" role="alert">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ $errors->first('password') }}</span>
          </div>
        @endif
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

<script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
