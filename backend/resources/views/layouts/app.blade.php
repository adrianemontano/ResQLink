<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'ResQLink') }}</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f6f7f9;
            color: #18202f;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f6f7f9;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            align-items: center;
            background: #ffffff;
            border-bottom: 1px solid #dde3ea;
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            min-height: 64px;
            padding: 0 2rem;
        }

        .brand {
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0;
        }

        .nav {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .main {
            width: min(1120px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 2rem 0;
        }

        .auth-main {
            align-items: center;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }

        .panel,
        .card {
            background: #ffffff;
            border: 1px solid #dde3ea;
            border-radius: 8px;
        }

        .panel {
            width: min(420px, 100%);
            padding: 2rem;
        }

        .header-row {
            align-items: center;
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .card {
            padding: 1.25rem;
        }

        .metric {
            color: #0f766e;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
            margin-top: .5rem;
        }

        h1,
        h2 {
            margin: 0;
        }

        h1 {
            font-size: clamp(1.6rem, 2.4vw, 2.2rem);
            line-height: 1.15;
        }

        h2 {
            font-size: 1rem;
        }

        p {
            color: #596579;
            margin: .5rem 0 0;
        }

        label {
            display: block;
            font-size: .9rem;
            font-weight: 700;
            margin-bottom: .4rem;
        }

        input,
        select {
            background: #ffffff;
            border: 1px solid #c9d2de;
            border-radius: 6px;
            color: #18202f;
            display: block;
            min-height: 42px;
            padding: .65rem .75rem;
            width: 100%;
        }

        input[type="checkbox"] {
            display: inline-block;
            min-height: auto;
            width: auto;
        }

        .field {
            margin-top: 1rem;
        }

        .inline-field {
            align-items: center;
            display: flex;
            gap: .5rem;
            margin-top: 1rem;
        }

        .inline-field label {
            margin: 0;
        }

        .button,
        button {
            align-items: center;
            background: #0f766e;
            border: 1px solid #0f766e;
            border-radius: 6px;
            color: #ffffff;
            cursor: pointer;
            display: inline-flex;
            font-weight: 800;
            gap: .4rem;
            justify-content: center;
            min-height: 40px;
            padding: .6rem .95rem;
        }

        .button.secondary,
        button.secondary {
            background: #ffffff;
            color: #18202f;
            border-color: #c9d2de;
        }

        .button.danger,
        button.danger {
            background: #b42318;
            border-color: #b42318;
        }

        .actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            margin-top: 1.25rem;
        }

        .alert {
            border-radius: 6px;
            margin-bottom: 1rem;
            padding: .85rem 1rem;
        }

        .alert.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert.success {
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .error-text {
            color: #b42318;
            font-size: .85rem;
            margin-top: .35rem;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border-bottom: 1px solid #e6ebf1;
            padding: .85rem;
            text-align: left;
            vertical-align: middle;
        }

        th {
            color: #596579;
            font-size: .78rem;
            text-transform: uppercase;
        }

        .badge {
            background: #eef2f6;
            border-radius: 999px;
            display: inline-block;
            font-size: .8rem;
            font-weight: 800;
            padding: .25rem .55rem;
        }

        .badge.active {
            background: #dcfce7;
            color: #166534;
        }

        .badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .split {
            display: grid;
            gap: 1rem;
            grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
        }

        @media (max-width: 760px) {
            .topbar,
            .header-row {
                align-items: flex-start;
                flex-direction: column;
            }

            .split {
                grid-template-columns: 1fr;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    @yield('body')
</body>
</html>
