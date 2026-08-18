@extends('layouts.app')

@section('title', 'Profil & Pengaturan Akun')

@section('content')

<!-- Header Card -->
<div class="bg-white dark:bg-slate-800 dark:border-slate-700 rounded-2xl p-6 shadow-sm border border-slate-200/80">
    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
        @include('partials.icon', ['name' => 'user-cog', 'class' => 'w-5 h-5 text-emerald-500'])
        <span>Profil & Pengaturan Akun</span>
    </h2>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola informasi data siswa dan pengaturan keamanan password.</p>
</div>

<!-- Identity Card -->
<div class="bg-white dark:bg-slate-800 dark:border-slate-700 rounded-3xl p-6 shadow-sm border border-slate-200/80 flex flex-col sm:flex-row items-center gap-5 sm:gap-6">
    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-teal-500 flex items-center justify-center text-white text-2xl font-extrabold shadow-lg shrink-0">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <div class="flex-1 min-w-0 text-center sm:text-left">
        <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
            @if($user->role === 'siswa')
                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold {{ $user->jurusan['badge'] }}">{{ $user->jurusan['label'] }}</span>
            @endif
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">NIS {{ $user->nis }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- Card 1: Data Diri & Preferensi -->
    <div class="bg-white dark:bg-slate-800 dark:border-slate-700 rounded-3xl p-6 shadow-sm border border-slate-200/80">
        <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-base mb-4 flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
            @include('partials.icon', ['name' => 'id-card', 'class' => 'w-5 h-5 text-emerald-500'])
            <span>Informasi Data Diri</span>
        </h3>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">NIS (Nomor Induk Siswa)</label>
                <input type="text" value="{{ $user->nis }}" disabled
                    class="w-full px-4 py-3 bg-slate-100 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl text-sm font-bold text-slate-500 dark:text-slate-400 cursor-not-allowed">
                <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 block">NIS bersifat unik dan tidak dapat diubah.</span>
            </div>

            <div>
                <label for="worship_type" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Preferensi Jenis Ibadah</label>
                <select id="worship_type" name="worship_type" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="muslim" {{ $user->worship_type == 'muslim' ? 'selected' : '' }}>Muslim (5 Sholat Wajib)</option>
                    <option value="non_muslim" {{ $user->worship_type == 'non_muslim' ? 'selected' : '' }}>Non-Muslim (Doa Pagi / Kitab / Meditasi)</option>
                </select>
                <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 block">Penyesuaian checklist kartu "Beribadah" pada dashboard.</span>
            </div>

            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-xs shadow-md transition-all">
                Simpan Perubahan Profil
            </button>
        </form>
    </div>

    <!-- Card 2: Ganti Password -->
    <div class="bg-white dark:bg-slate-800 dark:border-slate-700 rounded-3xl p-6 shadow-sm border border-slate-200/80">
        <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-base mb-4 flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
            @include('partials.icon', ['name' => 'key', 'class' => 'w-5 h-5 text-amber-500'])
            <span>Ubah Password</span>
        </h3>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="current_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Password Saat Ini</label>
                <input type="password" id="current_password" name="current_password" required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Password Baru (Min 6 Karakter)</label>
                <input type="password" id="password" name="password" required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    placeholder="Ulangi password baru"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit" class="w-full py-3 bg-slate-800 hover:bg-slate-900 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-xl font-bold text-xs shadow-md transition-all">
                Ubah Password Sekarang
            </button>
        </form>
    </div>

</div>

@endsection
