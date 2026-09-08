@extends('layouts.blank')

@section('title', 'Reset Password')

@section('content')
    <div class="flex min-h-screen flex-col items-center justify-center px-6 py-14">
        <div class="w-full max-w-md">
            <a href="/" class="inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-gold-soft" style="font-variation-settings:'FILL' 1;">hotel_class</span>
                <span class="font-display text-2xl font-semibold tracking-tight text-ink">{{ $site['name'] }}<span class="text-gold">.</span></span>
            </a>

            <p class="eyebrow mt-10">Account recovery</p>
            <h1 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink">Set a new password</h1>
            <p class="mt-2 text-sm text-stone">Minimum 8 characters.</p>

            @if ($errors->any())
                <div class="mt-6 flex items-start gap-3 rounded-xl border border-danger/20 bg-danger/5 p-4 text-sm text-danger">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="mt-8 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />
                <div>
                    <label for="email" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Email address</label>
                    <input id="email" name="email" type="email" required value="{{ old('email', $email) }}" placeholder="you@example.com"
                        class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3.5 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                </div>
                <div>
                    <label for="password" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">New password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="••••••••"
                        class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3.5 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                </div>
                <div>
                    <label for="password_confirmation" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="••••••••"
                        class="mt-2 w-full rounded-xl border border-line bg-surface px-4 py-3.5 text-sm text-ink placeholder:text-stone/50 outline-none transition-colors focus:border-gold" />
                </div>

                <button type="submit"
                    class="w-full rounded-full bg-ink py-4 text-sm font-semibold uppercase tracking-widest text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">
                    Reset password
                </button>
            </form>
        </div>
    </div>
@endsection
