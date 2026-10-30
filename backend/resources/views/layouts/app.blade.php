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
    <link rel="stylesheet" href="{{ asset('css/resqlink-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-badges.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-widgets.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-map.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/resqlink-design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-refresh.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-refresh-shell.css') }}">
    <script src="{{ asset('js/responsive-tables.js') }}" defer></script>
</head>
<body>
    @yield('body')
    @stack('scripts')
</body>
</html>
