@extends('layouts.guest')

@section('title', 'Login Siswa - 7 Kebiasaan Baik')

@section('content')
<h2 class="text-xl font-bold text-slate-800 mb-1">Masuk ke Akun Anda</h2>
<p class="text-xs text-slate-500 mb-6">Gunakan NIS (Nomor Induk Siswa) dan password sekolah Anda.</p>

<form action="{{ route('login') }}" method="POST" class="space-y-4">
    @csrf

    <div>
        <label for="nis" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">NIS (Nomor Induk Siswa)</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                <i class="fa-solid fa-id-card"></i>
            </span>
            <input type="text" id="nis" name="nis" value="{{ old('nis') }}" required autofocus
                placeholder="Contoh: 12345678"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
        </div>
    </div>

    <div>
        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                <i class="fa-solid fa-lock"></i>
            </span>
            <input type="password" id="password" name="password" required
                placeholder="••••••••"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
        </div>
    </div>

    <div class="flex items-center justify-between text-xs pt-1">
        <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
            <span>Ingat Saya</span>
        </label>
    </div>

    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-emerald-600/30 hover:from-emerald-500 hover:to-teal-500 transition-all flex items-center justify-center gap-2 active:scale-98">
        <span>Masuk Sekarang</span>
        <i class="fa-solid fa-arrow-right text-xs"></i>
    </button>

    <div class="pt-4 text-center border-t border-slate-100">
        <p class="text-xs text-slate-500">
            Belum punya akun? 
            <a href="{{ route('register') }}" class="font-bold text-emerald-600 hover:underline">Daftar Akun Baru</a>
        </p>
    </div>
</form>
@endsection
