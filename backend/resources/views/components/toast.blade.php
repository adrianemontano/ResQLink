<div class="rq-toast-wrap" id="rqToasts" aria-live="polite"></div>
<link rel="stylesheet" href="{{ asset('css/toast.css') }}">
<script src="{{ asset('js/toast.js') }}"></script>
@php
  $toasts = [];
  if (session('incident')) {
    $inc = session('incident');
    $toasts[] = ['success', 'Incident INC-' . str_pad((string)($inc['id'] ?? ''), 4, '0', STR_PAD_LEFT) . ' was submitted successfully.'];
  }
  foreach (['success', 'status', 'notice', 'incident_status_success', 'incident_update_success'] as $k) {
    if (session($k)) {
      $toasts[] = ['success', session($k)];
    }
  }
  if ($errors->has('update')) {
    $toasts[] = ['error', $errors->first('update')];
  }
  if (session('warning')) {
    $toasts[] = ['warning', session('warning')];
  }
  if (session('error')) {
    $toasts[] = ['error', session('error')];
  }
  if ($errors->has('account')) {
    $toasts[] = ['error', $errors->first('account')];
  }

  // Map authentication / system errors to standard toast pop-ups
  if ($errors->has('login') && str_contains(strtolower($errors->first('login')), 'credential')) {
    $toasts[] = ['error', 'Incorrect username or password.'];
  } elseif ($errors->has('username') && str_contains(strtolower($errors->first('username')), 'credential')) {
    $toasts[] = ['error', 'Incorrect username or password.'];
  } elseif ($errors->has('email') && str_contains(strtolower($errors->first('email')), 'credential')) {
    $toasts[] = ['error', 'Incorrect username or password.'];
  } elseif ($errors->has('login') && str_contains(strtolower($errors->first('login')), 'deactivated')) {
    $toasts[] = ['error', 'Your account has been deactivated. Please contact your administrator.'];
  } elseif ($errors->has('status') && !str_contains(strtolower($errors->first('status')), 'required')) {
    $toasts[] = ['error', $errors->first('status')];
  }

  $uniqueToasts = [];
  $seenKeys = [];
  foreach ($toasts as $t) {
    $dedupKey = $t[0] . ':' . $t[1];
    if (!isset($seenKeys[$dedupKey])) {
      $seenKeys[$dedupKey] = true;
      $uniqueToasts[] = $t;
    }
  }
@endphp
@if (!empty($uniqueToasts))
<script>
document.addEventListener('DOMContentLoaded', function () {
  @foreach ($uniqueToasts as $t)
    showToast({ type: @json($t[0]), message: @json($t[1]) });
  @endforeach
});
</script>
@endif
