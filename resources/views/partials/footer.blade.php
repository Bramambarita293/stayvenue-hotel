<footer class="bg-onyx text-background">
    <div class="mx-auto max-w-container px-5 py-16 md:px-8">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-12">
            <div class="md:col-span-5">
                <div class="font-display text-3xl font-semibold tracking-tight">
                    {{ $site['name'] }}<span class="text-gold">.</span>
                </div>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-background/60">{{ $site['tagline'] }} — where quiet
                    luxury meets meticulous hospitality.</p>
            </div>

            <div class="md:col-span-3">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold">Explore</p>
                <ul class="mt-4 space-y-3 text-sm text-background/70">
                    <li><a href="{{ route('rooms.index') }}" class="transition-colors hover:text-gold">Rooms &amp; Suites</a></li>
                    <li><a href="{{ route('halls.index') }}" class="transition-colors hover:text-gold">Event Venues</a></li>
                    @auth
                        <li><a href="{{ route('user.reservations') }}" class="transition-colors hover:text-gold">My Reservations</a></li>
                    @endauth
                </ul>
            </div>

            <div class="md:col-span-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold">Contact</p>
                <ul class="mt-4 space-y-3 text-sm text-background/70">
                    <li class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-[18px] text-gold">location_on</span>
                        {{ $site['address'] }}
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[18px] text-gold">call</span>
                        <a href="tel:{{ $site['phone'] }}" class="transition-colors hover:text-gold">{{ $site['phone'] }}</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[18px] text-gold">mail</span>
                        <a href="mailto:{{ $site['email'] }}" class="transition-colors hover:text-gold">{{ $site['email'] }}</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[18px] text-gold">schedule</span>
                        {{ $site['hours'] }}
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-14 flex flex-col items-center justify-between gap-4 border-t border-background/10 pt-6 md:flex-row">
            <p class="text-xs text-background/40">© {{ date('Y') }} {{ $site['long_name'] }}. All rights reserved.</p>
            <div class="flex gap-6 text-xs text-background/40">
                <a href="#" class="transition-colors hover:text-gold">Privacy Policy</a>
                <a href="#" class="transition-colors hover:text-gold">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>