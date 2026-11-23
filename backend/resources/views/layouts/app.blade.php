<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'ResQLink') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/resqlink-tokens.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-theme.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-badges.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-dashboard.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-incident-card.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-widgets.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-status-filters.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-map.css') }}?v=20261123">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/resqlink-design-system.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-refresh.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-refresh-shell.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-responsive.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-layout-scroll.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/account.css') }}?v=20261123">
    <link rel="stylesheet" href="{{ asset('css/resqlink-footer.css') }}?v=20261123">
    @if (auth()->check() && auth()->user()->hasRole('dispatcher'))
        <link rel="stylesheet" href="{{ asset('css/dispatcher-theme.css') }}?v=20261123">
        <link rel="stylesheet" href="{{ asset('css/dispatcher-notifications.css') }}?v=20261123">
        <link rel="stylesheet" href="{{ asset('css/dispatcher-dropdowns.css') }}?v=20261123">
        <script src="{{ asset('js/dispatcher-dropdowns.js') }}" defer></script>
    @endif
    <script src="{{ asset('js/responsive-tables.js') }}" defer></script>
</head>
<body>
    <x-toast />
    @yield('body')
    @stack('scripts')
</body>
</html>
