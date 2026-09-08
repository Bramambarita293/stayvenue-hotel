@extends('layouts.blank')

@section('title', 'Forgot Password')

@section('content')
    <div class="flex min-h-screen flex-col items-center justify-center px-6 py-14">
        <div class="w-full max-w-md">
            <a href="/" class="inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-gold-soft" style="font-variation-settings:'FILL' 1;">hotel_class</span>
                <span class="font-display text-2xl font-semibold tracking-tight text-ink">{{ $site['name'] }}<span class="text-gold">.</span></span>
            </a>

            <p class="eyebrow mt-10">Account recovery</p>
            <h1 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink">Forgot your password?</h1>
            <p class="mt-2 text-sm text-stone">Enter your account email and we will send a reset link.</p>

            @if (session('status'))
                <div class="mt-6 flex items-start gap-3 rounded-xl border border-successful/30 bg-successful/10 p-4 text-sm text-successful">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 flex items-start gap-3 rounded-xl border border-danger/20 bg-danger/5 p-4 text-sm text-danger">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Email address</label>
                    <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}" placeholder="you@example.com"
                        class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3.5 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                </div>

                <button type="submit"
                    class="w-full rounded-full bg-ink py-4 text-sm font-semibold uppercase tracking-widest text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">
                    Send reset link
                </button>
            </form>

            <p class="mt-8 border-t border-line/70 pt-6 text-center text-sm text-stone">
                Remembered it?
                <a href="{{ route('login') }}" class="font-semibold text-ink underline-offset-4 hover:text-gold-soft hover:underline">Back to sign in</a>
            </p>
        </div>
    </div>
@endsection
