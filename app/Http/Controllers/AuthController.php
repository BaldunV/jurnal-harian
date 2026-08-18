<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (Auth::attempt(['nis' => $credentials['nis'], 'password' => $credentials['password']], $request->has('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            $validPortal = $credentials['login_as'] === 'siswa'
                ? $user->role === 'siswa'
                : in_array($user->role, ['admin', 'guru'], true);

            if (! $validPortal) {
                Auth::logout();

                return back()->withErrors(['nis' => 'Silakan gunakan portal login yang sesuai dengan peran akun Anda.'])->onlyInput('nis');
            }

            return $this->redirectByRole($user)->with('success', 'Login berhasil. Selamat datang kembali!');
        }

        return back()->withErrors([
            'nis' => 'NIS atau password yang dimasukkan salah.',
        ])->onlyInput('nis');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari aplikasi.');
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
