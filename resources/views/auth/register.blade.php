@extends('layouts.blank')

@section('title', 'Create Account')

@section('content')
    <div class="flex min-h-screen flex-col-reverse lg:flex-row">
        <!-- Cover -->
        <div class="relative hidden min-h-[60vh] lg:block lg:w-1/2 lg:min-h-full">
            <img class="absolute inset-0 h-full w-full object-cover" src="{{ $authCover }}" alt="{{ $site['long_name'] }}" />
            <div class="absolute inset-0 bg-gradient-to-t from-onyx/80 via-onyx/20 to-transparent"></div>
            <div class="absolute bottom-16 left-16 right-16">
                <p class="font-display text-3xl font-medium leading-snug text-background">
                    "A sanctuary of refined elegance, where every detail anticipates your arrival."
                </p>
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.25em] text-gold">The {{ $site['name'] }} experience</p>
            </div>
        </div>

        <!-- Form -->
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-14 lg:px-16">
            <div class="w-full max-w-md">
                <a href="/" class="inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-gold-soft" style="font-variation-settings:'FILL' 1;">hotel_class</span>
                    <span class="font-display text-2xl font-semibold tracking-tight text-ink">{{ $site['name'] }}<span class="text-gold">.</span></span>
                </a>

                <p class="eyebrow mt-10">Join the registry</p>
                <h1 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink">Create an account</h1>
                <p class="mt-2 text-sm text-stone">Experience the pinnacle of hospitality and manage your stays.</p>

                @if ($errors->any())
                    <div class="mt-6 rounded-xl border border-danger/20 bg-danger/5 p-4 text-sm text-danger">
                        <p class="flex items-center gap-2 font-semibold">
                            <span class="material-symbols-outlined text-[20px]">error</span> Registration issue
                        </p>
                        <ul class="mt-2 list-disc space-y-0.5 pl-5 text-xs opacity-90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST" class="mt-8 space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Full name</label>
                        <input id="name" name="name" type="text" required autofocus value="{{ old('name') }}" placeholder="Eleanor Example"
                            class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                    </div>
                    <div>
                        <label for="email" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" placeholder="eleanor@example.com"
                            class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                    </div>
                    <div>
                        <label for="phone_number" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Phone number</label>
                        <input id="phone_number" name="phone_number" type="tel" required value="{{ old('phone_number') }}" placeholder="081234567890"
                            class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Password</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" required placeholder="••••••••"
                                class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Confirm password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required placeholder="••••••••"
                                class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full rounded-full bg-ink py-4 text-sm font-semibold uppercase tracking-widest text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">
                        Create account
                    </button>
                </form>

                <p class="mt-8 border-t border-line/70 pt-6 text-center text-sm text-stone">
                    Already a registered guest?
                    <a href="{{ route('login') }}" class="font-semibold text-ink underline-offset-4 hover:text-gold-soft hover:underline">Sign in here</a>
                </p>
            </div>
        </div>
    </div>
@endsection