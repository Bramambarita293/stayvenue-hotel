<header id="top-nav" class="fixed top-0 inset-x-0 z-50 border-b border-border/70 bg-background/90 backdrop-blur-md transition-all duration-300">
    <div class="mx-auto flex h-20 max-w-container items-center justify-between px-5 md:px-8">
        <a href="/" class="font-display text-2xl font-semibold tracking-tight text-ink">
            {{ $site['name'] }}<span class="text-navy">.</span>
        </a>

        <nav class="hidden items-center gap-8 md:flex">
            <a href="{{ route('rooms.index') }}"
                class="text-sm font-medium text-muted-text transition-colors hover:text-ink">Rooms</a>
            <a href="{{ route('halls.index') }}"
                class="text-sm font-medium text-muted-text transition-colors hover:text-ink">Venues</a>
            <a href="{{ route('contact') }}"
                @if (request()->routeIs('contact')) aria-current="page" @endif
                class="text-sm font-medium transition-colors {{ request()->routeIs('contact') ? 'text-ink' : 'text-muted-text hover:text-ink' }}">Contact</a>
            @auth
                <a href="{{ route('user.reservations') }}"
                    class="text-sm font-medium text-muted-text transition-colors hover:text-ink">Reservations</a>
            @endauth
        </nav>

        <div class="hidden items-center gap-5 md:flex">
            @auth
                <span class="text-sm text-muted-text">Halo, <span class="font-semibold text-ink">{{ Auth::user()->name }}</span></span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-muted-text transition-colors hover:text-danger">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}"
                    class="text-sm font-medium text-ink underline-offset-4 transition-colors hover:text-navy-soft hover:underline">Sign in</a>
                <a href="{{ route('register') }}"
                    class="rounded-full bg-ink px-5 py-2.5 text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-navy-dark hover:text-white">Book now</a>
            @endauth
        </div>

        <button id="nav-toggle" class="flex items-center justify-center md:hidden" aria-label="Menu">
            <span class="material-symbols-outlined text-ink">menu</span>
        </button>
    </div>

    <div id="mobile-menu" class="hidden border-t border-border/70 bg-background px-5 py-4 md:hidden">
        <nav class="flex flex-col gap-4">
            <a href="{{ route('rooms.index') }}" class="text-sm font-medium text-ink">Rooms</a>
            <a href="{{ route('halls.index') }}" class="text-sm font-medium text-ink">Venues</a>
            <a href="{{ route('contact') }}" class="text-sm font-medium text-ink">Contact</a>
            @auth
                <a href="{{ route('user.reservations') }}" class="text-sm font-medium text-ink">Reservations</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-danger">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-ink">Sign in</a>
                <a href="{{ route('register') }}"
                    class="rounded-full bg-ink px-5 py-2.5 text-center text-sm font-semibold text-background">Book now</a>
            @endauth
        </nav>
    </div>
</header>