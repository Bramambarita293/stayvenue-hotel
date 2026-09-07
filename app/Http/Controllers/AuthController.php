<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan Halaman Login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses Submit Form Login
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        unset($credentials['remember']);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Tampilkan Halaman Register
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Proses Submit Form Register
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        Auth::login($user);

        return redirect('/');
    }
}