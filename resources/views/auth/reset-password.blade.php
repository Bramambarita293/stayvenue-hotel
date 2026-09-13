@extends('layouts.blank')

@section('title', 'Reset Password')

@section('content')
    <div class="flex min-h-screen flex-col items-center justify-center px-6 py-14">
        <div class="w-full max-w-md">
            <a href="/" class="inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-navy-soft" style="font-variation-settings:'FILL' 1;">hotel_class</span>
                <span class="font-display text-2xl font-semibold tracking-tight text-ink">{{ $site['name'] }}<span class="text-navy">.</span></span>
            </a>

            <p class="eyebrow mt-10">Account recovery</p>
            <h1 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink">Set a new password</h1>
            <p class="mt-2 text-sm text-muted-text">Minimum 8 characters.</p>

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
                    <label for="email" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-text">Email address</label>
                    <div class="relative mt-2">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px] text-muted-text/50">email</span>
                        <input id="email" name="email" type="email" required value="{{ old('email', $email) }}" placeholder="you@example.com"
                            class="w-full rounded-xl border border-border bg-surface py-3.5 pl-11 pr-4 text-sm text-ink placeholder:text-muted-text/50 outline-none transition-colors focus:border-navy" />
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-text">New password</label>
                    <div class="relative mt-2">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px] text-muted-text/50">lock</span>
                        <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="••••••••"
                            class="w-full rounded-xl border border-border bg-surface py-3.5 pl-11 pr-11 text-sm text-ink placeholder:text-muted-text/50 outline-none transition-colors focus:border-navy" />
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-text/50 hover:text-muted-text" tabindex="-1">
                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-text">Confirm password</label>
                    <div class="relative mt-2">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px] text-muted-text/50">lock</span>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="••••••••"
                            class="w-full rounded-xl border border-border bg-surface py-3.5 pl-11 pr-11 text-sm text-ink placeholder:text-muted-text/50 outline-none transition-colors focus:border-navy" />
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-text/50 hover:text-muted-text" tabindex="-1">
                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-full bg-ink py-4 text-sm font-semibold uppercase tracking-widest text-background transition-all hover:-translate-y-0.5 hover:bg-navy-dark hover:text-white">
                    <span class="material-symbols-outlined text-[18px]">lock_reset</span> Reset password
                </button>
            </form>
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
