<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

            if (!$validPortal) {
                Auth::logout();
                return back()->withErrors(['nis' => 'Silakan gunakan portal login yang sesuai dengan peran akun Anda.'])->onlyInput('nis');
            }

            return $this->redirectByRole($user)->with('success', 'Login berhasil. Selamat datang kembali!');
        }

        return back()->withErrors([
            'nis' => 'NIS atau password yang dimasukkan salah.',
        ])->onlyInput('nis');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'unique:users,nis', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'kelas' => ['required', 'in:X PPLG,X TJKT,X AKL,X ACP,XI PPLG,XI TJKT,XI AKL,XI ACP,XII PPLG,XII TJKT,XII AKL,XII ACP'],
            'worship_type' => ['required', 'in:muslim,non_muslim'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah terdaftar di sistem. Gunakan NIS lain atau login.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'nis' => $validated['nis'],
            'name' => $validated['name'],
            'kelas' => $validated['kelas'],
            'role' => 'siswa',
            'worship_type' => $validated['worship_type'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil! Mari bangun 7 Kebiasaan Baik.');
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
