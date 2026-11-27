@props(['code', 'message'])

{{--
    Shared layout for the 403, 404, 419, and 500 error pages. The button goes to
    "/", which sends signed-in users to their dashboard and everyone else to the
    login page, so it works even when the session is unavailable.
--}}
<div class="error-page">
    <main class="error-page-main">
        <div class="card error-page-card">
            <h1 class="error-page-code">{{ $code }}</h1>
            <p class="error-page-message">{{ $message }}</p>
            <a class="button primary" href="{{ url('/') }}">Back to ResQLink</a>
        </div>
    </main>
    <x-footer />
</div>
