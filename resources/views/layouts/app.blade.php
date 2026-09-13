<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>@yield('title', $site['long_name']) - {{ $site['long_name'] }}</title>
    @vite(['resources/css/app.css'])
    @include('partials.tailwind-config')
    @stack('head')
</head>

<body class="flex min-h-screen flex-col bg-background font-body text-ink antialiased selection:bg-navy/20">
    @include('partials.header')

    <main class="grow">
        @yield('content')
    </main>

    @include('partials.footer')

    @include('partials.whatsapp-button')

    <script>
        const navToggle = document.getElementById('nav-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        if (navToggle && mobileMenu) {
            navToggle.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
        }

        window.addEventListener('scroll', () => {
            const nav = document.getElementById('top-nav');
            if (!nav) return;
            if (window.scrollY > 50) {
                nav.classList.add('shadow-nav');
                nav.querySelector('div')?.classList.replace('h-20', 'h-16');
            } else {
                nav.classList.remove('shadow-nav');
                nav.querySelector('div')?.classList.replace('h-16', 'h-20');
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
