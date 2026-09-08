@extends('layouts.blank')

@section('title', 'Sign In')

@section('content')
    <div class="flex min-h-screen flex-col lg:flex-row">
        <!-- Form -->
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-14 lg:px-16">
            <div class="w-full max-w-md">
                <a href="/" class="inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-gold-soft" style="font-variation-settings:'FILL' 1;">hotel_class</span>
                    <span class="font-display text-2xl font-semibold tracking-tight text-ink">{{ $site['name'] }}<span class="text-gold">.</span></span>
                </a>

                <p class="eyebrow mt-10">Welcome back</p>
                <h1 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink">Sign in to your account</h1>
                <p class="mt-2 text-sm text-stone">Continue your stay and manage your reservations.</p>

                @if (session('status'))
                    <div class="mt-6 flex items-start gap-3 rounded-xl border border-successful/30 bg-successful/10 p-4 text-sm text-successful">
                        <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        <p>{{ session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-6 flex items-start gap-3 rounded-xl border border-danger/20 bg-danger/5 p-4 text-sm text-danger">
                        <span class="material-symbols-outlined text-[20px]">error</span>
                        <div>
                            <p class="font-semibold">Authentication failed</p>
                            <p class="mt-1 text-xs opacity-90">{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Email address</label>
                        <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}" placeholder="you@example.com"
                            class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3.5 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                    </div>
                    <div>
                        <label for="password" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Password</label>
                        <input id="password" name="password" type="password" required placeholder="••••••••"
                            class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3.5 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex cursor-pointer items-center gap-2 text-stone">
                            <input id="remember-me" name="remember" type="checkbox"
                                class="h-4 w-4 rounded border-line text-gold-soft focus:ring-gold-soft" />
                            Remember me
                        </label>
                        <a href="{{ route('password.request') }}" class="font-medium text-ink underline-offset-4 transition-colors hover:text-gold-soft hover:underline">Forgot password?</a>
                    </div>

                    <button type="submit"
                        class="w-full rounded-full bg-ink py-4 text-sm font-semibold uppercase tracking-widest text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">
                        Sign in
                    </button>
                </form>

                <p class="mt-8 border-t border-line/70 pt-6 text-center text-sm text-stone">
                    Not a member yet?
                    <a href="{{ route('register') }}" class="font-semibold text-ink underline-offset-4 hover:text-gold-soft hover:underline">Create an account</a>
                </p>
            </div>
        </div>

        <!-- Cover -->
        <div class="relative hidden min-h-[60vh] lg:block lg:w-1/2">
            <img class="absolute inset-0 h-full w-full object-cover" src="{{ $authCover }}" alt="{{ $site['long_name'] }}" />
            <div class="absolute inset-0 bg-gradient-to-t from-onyx/80 via-onyx/20 to-transparent"></div>
            <div class="absolute bottom-16 left-16 right-16">
                <p class="font-display text-3xl font-medium leading-snug text-background">
                    "A sanctuary of refined elegance, where every detail anticipates your arrival."
                </p>
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.25em] text-gold">The {{ $site['name'] }} experience</p>
            </div>
        </div>
    </div>
@endsection