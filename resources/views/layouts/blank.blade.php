<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>@yield('title', $site['long_name']) - {{ $site['long_name'] }}</title>
    @include('partials.tailwind-config')
    @stack('head')
</head>

<body class="min-h-screen bg-background font-body text-ink antialiased selection:bg-gold/20">
    @yield('content')
    @stack('scripts')
</body>

</html>