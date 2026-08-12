@extends('layouts.guest')

@section('title', 'Daftar Akun - 7 Kebiasaan Baik')

@section('content')
<h2 class="text-xl font-bold text-slate-800 mb-1">Registrasi Akun</h2>
<p class="text-xs text-slate-500 mb-6">Pendaftaran mandiri hanya untuk siswa. Akun admin dan guru dibuat oleh sekolah.</p>

<form action="{{ route('register') }}" method="POST" class="space-y-4">
    @csrf

    <div>
        <label for="nis" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">NIS / ID Pengguna</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                <i class="fa-solid fa-id-badge"></i>
            </span>
            <input type="text" id="nis" name="nis" value="{{ old('nis') }}" required
                placeholder="Nomor Induk Siswa unik"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
        </div>
    </div>

    <div>
        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                <i class="fa-solid fa-user"></i>
            </span>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                placeholder="Nama Lengkap Siswa"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
        </div>
    </div>

    <div>
        <label for="kelas" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kelas</label>
        <select id="kelas" name="kelas" required class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
            <option value="" disabled {{ old('kelas') ? '' : 'selected' }}>Pilih kelas dan jurusan</option>
            @foreach ([
                'Kelas X' => ['X PPLG', 'X TJKT', 'X AKL', 'X ACP'],
                'Kelas XI' => ['XI PPLG', 'XI TJKT', 'XI AKL', 'XI ACP'],
                'Kelas XII' => ['XII PPLG', 'XII TJKT', 'XII AKL', 'XII ACP'],
            ] as $tingkat => $kelasList)
                <optgroup label="{{ $tingkat }}">
                    @foreach ($kelasList as $kelas)
                        <option value="{{ $kelas }}" @selected(old('kelas') === $kelas)>{{ $kelas }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('kelas')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="worship_type" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pengaturan Jenis Ibadah</label>
        <select id="worship_type" name="worship_type" class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
            <option value="muslim" {{ old('worship_type') == 'muslim' ? 'selected' : '' }}>Muslim (5 Sholat Wajib)</option>
            <option value="non_muslim" {{ old('worship_type') == 'non_muslim' ? 'selected' : '' }}>Non-Muslim (Doa Pagi / Kitab / Doa Malam)</option>
        </select>
    </div>

    <div>
        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password (Min 6 Karakter)</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                <i class="fa-solid fa-lock"></i>
            </span>
            <input type="password" id="password" name="password" required
                placeholder="••••••••"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
        </div>
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Konfirmasi Password</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                <i class="fa-solid fa-shield-check"></i>
            </span>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                placeholder="Ulangi password"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
        </div>
    </div>

    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-emerald-600/30 hover:from-emerald-500 hover:to-teal-500 transition-all flex items-center justify-center gap-2 active:scale-98">
        <i class="fa-solid fa-user-plus text-xs"></i>
        <span>Daftar Akun Sekarang</span>
    </button>

    <div class="pt-4 text-center border-t border-slate-100">
        <p class="text-xs text-slate-500">
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:underline">Masuk ke Sistem</a>
        </p>
    </div>
</form>
@endsection
