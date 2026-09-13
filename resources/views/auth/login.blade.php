@extends('layouts.blank')

@section('title', 'Sign In')

@section('content')
    <div class="flex min-h-screen flex-col lg:flex-row">
        <!-- Form -->
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-14 lg:px-16">
            <div class="w-full max-w-md">
                <a href="/" class="inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-navy-soft" style="font-variation-settings:'FILL' 1;">hotel_class</span>
                    <span class="font-display text-2xl font-semibold tracking-tight text-ink">{{ $site['name'] }}<span class="text-navy">.</span></span>
                </a>

                <p class="eyebrow mt-10">Welcome back</p>
                <h1 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink">Sign in to your account</h1>
                <p class="mt-2 text-sm text-muted-text">Continue your stay and manage your reservations.</p>

                @if (session('status'))
                    <div class="mt-6 flex items-start gap-3 rounded-xl border border-success/30 bg-success/10 p-4 text-sm text-success">
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
                        <label for="email" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-text">Email address</label>
                        <div class="relative mt-2">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px] text-muted-text/50">person</span>
                            <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}" placeholder="you@example.com"
                                class="w-full rounded-xl border border-border bg-surface py-3.5 pl-11 pr-4 text-sm text-ink placeholder:text-muted-text/50 outline-none transition-colors focus:border-navy" />
                        </div>
                    </div>
                    <div>
                        <label for="password" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-text">Password</label>
                        <div class="relative mt-2">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px] text-muted-text/50">lock</span>
                            <input id="password" name="password" type="password" required placeholder="••••••••"
                                class="w-full rounded-xl border border-border bg-surface py-3.5 pl-11 pr-11 text-sm text-ink placeholder:text-muted-text/50 outline-none transition-colors focus:border-navy" />
                            <button type="button" onclick="togglePassword(this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-text/50 hover:text-muted-text" tabindex="-1">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex cursor-pointer items-center gap-2 text-muted-text">
                            <input id="remember-me" name="remember" type="checkbox"
                                class="h-4 w-4 rounded border-border text-navy-soft focus:ring-navy-soft" />
                            Remember me
                        </label>
                        <a href="{{ route('password.request') }}" class="font-medium text-ink underline-offset-4 transition-colors hover:text-navy-soft hover:underline">Forgot password?</a>
                    </div>

                    <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-full bg-ink py-4 text-sm font-semibold uppercase tracking-widest text-background transition-all hover:-translate-y-0.5 hover:bg-navy-dark hover:text-white">
                        <span class="material-symbols-outlined text-[18px]">login</span> Sign in
                    </button>
                </form>

                <p class="mt-8 border-t border-border/70 pt-6 text-center text-sm text-muted-text">
                    Not a member yet?
                    <a href="{{ route('register') }}" class="font-semibold text-ink underline-offset-4 hover:text-navy-soft hover:underline">Create an account</a>
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
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.25em] text-navy">The {{ $site['name'] }} experience</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword(btn) {
            const input = btn.closest('.relative').querySelector('input');
            const icon = btn.querySelector('.material-symbols-outlined');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }
    </script>
@endpush