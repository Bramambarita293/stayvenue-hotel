<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim link reset (throttle di route). Selalu respons sukses agar
     * tak membocorkan email terdaftar vs tidak (anti enumerasi).
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'Jika email terdaftar, link reset telah dikirim. Periksa inbox/spam Anda.');
    }

    public function showResetForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => request()->query('email', '')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', PasswordRule::min(8)->max(72), 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan login.');
    }
}
