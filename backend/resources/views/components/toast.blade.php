<div class="rq-toast-wrap" id="rqToasts" aria-live="polite"></div>
<link rel="stylesheet" href="{{ asset('css/toast.css') }}">
<script src="{{ asset('js/toast.js') }}"></script>
@php
  $toasts = [];
  foreach (['success', 'status', 'notice'] as $k) {
    if (session($k)) {
      $toasts[] = ['success', session($k)];
    }
  }
  if (session('warning')) {
    $toasts[] = ['warning', session('warning')];
  }
  if (session('error')) {
    $toasts[] = ['error', session('error')];
  }
  if ($errors->any()) {
    foreach ($errors->all() as $message) {
      $toasts[] = ['error', $message];
    }
  }
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
  @foreach ($toasts as $t)
    showToast({ type: @json($t[0]), message: @json($t[1]) });
  @endforeach
});
</script>
