<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'ResQLink') }}</title>
    <link rel="stylesheet" href="{{ asset('css/resqlink-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-badges.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-widgets.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resqlink-map.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/resqlink-design-system.css') }}">
    <script src="{{ asset('js/responsive-tables.js') }}" defer></script>
</head>
<body>
    @yield('body')
    @stack('scripts')
</body>
</html>
