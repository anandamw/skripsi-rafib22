<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login process.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            if (!Auth::user()->aktif) {
                Auth::logout();
                return back()->with('error', 'Akun Anda telah dinonaktifkan. Hubungi Manajer.');
            }

            return redirect()->intended('dashboard')
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->nama);
        }

        return back()->with('error', 'Email atau password salah.')->withInput($request->only('email'));
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar.');
    }
}
