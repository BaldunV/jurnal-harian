<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nis' => ['required', 'string'],
            'password' => ['required', 'string'],
            'login_as' => ['required', 'in:siswa,staff'],
        ], [
            'nis.required' => 'NIS / Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $nis = trim($credentials['nis']);
        $throttleKey = 'login|'.$request->ip().'|'.Str::lower($nis);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([
                'nis' => 'Terlalu banyak percobaan login. Coba lagi dalam '.RateLimiter::availableIn($throttleKey).' detik.',
            ])->onlyInput('nis', 'login_as');
        }

        $validPassword = Auth::validate([
            'nis' => $nis,
            'password' => $credentials['password'],
        ]);
        $user = User::where('nis', $nis)->first();

        if (! $validPassword || ! $user) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'nis' => 'NIS atau password yang dimasukkan salah.',
            ])->onlyInput('nis', 'login_as');
        }

        $validPortal = $credentials['login_as'] === 'siswa'
            ? $user->role === 'siswa'
            : in_array($user->role, ['admin', 'guru'], true);

        if (! $validPortal) {
            return back()->withErrors([
                'nis' => 'Silakan gunakan portal login yang sesuai dengan peran akun Anda.',
            ])->onlyInput('nis', 'login_as');
        }

        RateLimiter::clear($throttleKey);
        $remember = $request->boolean('remember');

        return $this->finishLogin($request, $user, $remember)
            ->with('success', 'Login berhasil.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari aplikasi.');
    }

    private function finishLogin(Request $request, User $user, bool $remember)
    {
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    private function redirectByRole(User $user)
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('teacher.index'),
            default => redirect()->route('dashboard'),
        };
    }
}
