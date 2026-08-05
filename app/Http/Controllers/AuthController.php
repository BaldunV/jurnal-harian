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
            return Auth::user()->role === 'guru' 
                ? redirect()->route('teacher.index') 
                : redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nis' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nis.required' => 'NIS / Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt(['nis' => $credentials['nis'], 'password' => $credentials['password']], $request->has('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->role === 'guru') {
                return redirect()->route('teacher.index')->with('success', 'Selamat datang kembali, Bapak/Ibu Guru!');
            }

            return redirect()->route('dashboard')->with('success', 'Login berhasil! Selamat mencatat 7 Kebiasaan Baik hari ini.');
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
            'kelas' => ['required', 'string', 'max:50'],
            'role' => ['required', 'in:siswa,guru'],
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
            'role' => $validated['role'],
            'worship_type' => $validated['worship_type'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        if ($user->role === 'guru') {
            return redirect()->route('teacher.index')->with('success', 'Akun Guru berhasil dibuat!');
        }

        return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil! Mari bangun 7 Kebiasaan Baik.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari aplikasi.');
    }
}
