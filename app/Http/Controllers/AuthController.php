<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

    /**
     * Show forgot password form.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle sending reset link (Demo Mode).
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Alamat email tidak ditemukan di sistem kami.');
        }

        if (!$user->aktif) {
            return back()->with('error', 'Akun Anda tidak aktif. Hubungi Manajer untuk bantuan.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

        // Coba kirim email via SMTP jika kredensial valid
        try {
            Mail::send([], [], function ($message) use ($request, $resetUrl) {
                $message->to($request->email)
                        ->subject('Permintaan Atur Ulang Kata Sandi - JJ TOP Cosmindo')
                        ->html('
                            <div style="font-family: sans-serif; padding: 20px;">
                                <h3>Atur Ulang Kata Sandi</h3>
                                <p>Halo, kami menerima permintaan reset kata sandi untuk akun Anda.</p>
                                <p>Silakan klik tombol di bawah ini untuk mengatur ulang kata sandi Anda:</p>
                                <p><a href="' . $resetUrl . '" style="background-color: #1a337e; color: white; padding: 12px 20px; text-decoration: none; border-radius: 8px; display: inline-block;">Atur Ulang Kata Sandi</a></p>
                                <p><small>Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini.</small></p>
                            </div>
                        ');
            });
        } catch (\Exception $e) {
            // Log error pengiriman email, namun tetap lanjutkan ke mode demo agar presentasi tidak terganggu
            Log::warning('SMTP Error (Mode Demo aktif): ' . $e->getMessage());
        }

        return back()->with('reset_url', $resetUrl)
                     ->with('success', 'Tautan reset kata sandi berhasil dibuat!');
    }

    /**
     * Show reset password form.
     */
    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Handle resetting password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return back()->with('error', 'Token reset kata sandi tidak valid atau telah kedaluwarsa.');
        }

        if (now()->subMinutes(60)->gt($record->created_at)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->with('error', 'Token reset kata sandi telah kedaluwarsa. Silakan ajukan ulang.');
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Pengguna tidak ditemukan.');
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diubah! Silakan masuk dengan kata sandi baru.');
    }
}
