@php
    $isMobile = ($variant ?? 'desktop') === 'mobile';
@endphp

<!-- Card Header with Orbit -->
<div class="relative text-center mb-8 card-enter">
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-emerald-200/30 dark:bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

    <!-- Logo Aplikasi -->
    <img src="{{ asset('images/logo-login.png') }}" alt="Logo SMK BPPI" class="relative w-16 h-16 mx-auto mb-3 object-contain drop-shadow-sm">

    <div class="relative inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold mb-3 border border-emerald-200/80 dark:border-emerald-500/30 shadow-sm">
        @include('partials.icon', ['name' => 'sparkles', 'class' => 'w-2.5 h-2.5 text-emerald-400/80'])
        @include('partials.icon', ['name' => 'key', 'class' => 'w-3 h-3 text-emerald-500'])
        Portal Masuk Jurnal Harian
        @include('partials.icon', ['name' => 'sparkles', 'class' => 'w-2.5 h-2.5 text-emerald-400/80'])
    </div>

    <h2 class="relative font-display text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
        Masuk ke Akun Anda
    </h2>
    <p class="relative text-sm font-medium text-slate-500 dark:text-slate-400 mt-2 leading-relaxed max-w-sm mx-auto">
        Pilih peran terlebih dahulu, lalu masukkan ID dan password Anda.
    </p>
</div>

<!-- Alerts -->
@if(session('success'))
    <div class="mb-4 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs flex items-start gap-3 shadow-sm animate-shake-soft">
        <span class="shrink-0 w-7 h-7 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
            @include('partials.icon', ['name' => 'circle-check', 'class' => 'w-4 h-4 text-emerald-600 dark:text-emerald-400'])
        </span>
        <div class="pt-0.5">
            <p class="font-bold uppercase tracking-wider">Berhasil</p>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="mb-4 p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs shadow-sm animate-shake-soft">
        <div class="font-bold flex items-center gap-2 mb-1 uppercase tracking-wider">
            <span class="w-5 h-5 rounded-lg bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center text-rose-600 dark:text-rose-400 text-[10px]">
                @include('partials.icon', ['name' => 'circle-alert', 'class' => 'w-3 h-3'])
            </span>
            <span>Terjadi Kesalahan:</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 ml-2 text-rose-600 dark:text-rose-400 font-medium">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="login-form" action="{{ route('login') }}" method="POST" class="space-y-5" data-portal="siswa">
    @csrf

    <!-- ======== PORTAL SELECTION (Segmented 3D Cards) ======== -->
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                Masuk Sebagai
            </span>
            <span class="text-[10px] font-semibold text-slate-600 dark:text-slate-400">Pilih portal Anda</span>
        </div>

        <div class="grid grid-cols-2 gap-3" role="radiogroup" aria-label="Pilih portal login">

            {{-- Portal Siswa --}}
            <label class="group cursor-pointer select-none" data-portal="siswa">
                <input type="radio" name="login_as" value="siswa" class="peer sr-only" {{ old('login_as', 'siswa') === 'siswa' ? 'checked' : '' }}>
                <div class="portal-card relative block rounded-2xl border-2 border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/60 p-3.5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/10 focus-within:ring-4 focus-within:ring-emerald-500/20 peer-checked:border-emerald-500 peer-checked:bg-gradient-to-br peer-checked:from-emerald-50 peer-checked:to-teal-50/80 dark:peer-checked:from-emerald-500/15 dark:peer-checked:to-teal-500/10 peer-checked:shadow-xl peer-checked:shadow-emerald-500/20 peer-checked:ring-4 peer-checked:ring-emerald-500/15">

                    <span class="portal-check absolute -top-2 -right-2 w-5 h-5 rounded-full bg-emerald-500 text-white items-center justify-center text-[9px] shadow-md shadow-emerald-500/40 hidden peer-checked:flex">
                        @include('partials.icon', ['name' => 'check', 'class' => 'w-3 h-3'])
                    </span>

                    <span class="mx-auto w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 flex items-center justify-center text-base mb-1.5 transition-all duration-300 group-hover:scale-105 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-md peer-checked:shadow-emerald-500/30 peer-checked:scale-110">
                        @include('partials.icon', ['name' => 'graduation-cap', 'class' => 'w-5 h-5'])
                    </span>
                    <span class="block font-display font-extrabold text-xs text-slate-800 dark:text-slate-100">Siswa</span>
                    <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Isi Jurnal Harian</span>
                </div>
            </label>

            {{-- Portal Admin / Guru --}}
            <label class="group cursor-pointer select-none" data-portal="staff">
                <input type="radio" name="login_as" value="staff" class="peer sr-only" {{ old('login_as') === 'staff' ? 'checked' : '' }}>
                <div class="portal-card relative block rounded-2xl border-2 border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/60 p-3.5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/10 focus-within:ring-4 focus-within:ring-emerald-500/20 peer-checked:border-emerald-500 peer-checked:bg-gradient-to-br peer-checked:from-emerald-50 peer-checked:to-teal-50/80 dark:peer-checked:from-emerald-500/15 dark:peer-checked:to-teal-500/10 peer-checked:shadow-xl peer-checked:shadow-emerald-500/20 peer-checked:ring-4 peer-checked:ring-emerald-500/15">

                    <span class="portal-check absolute -top-2 -right-2 w-5 h-5 rounded-full bg-emerald-500 text-white items-center justify-center text-[9px] shadow-md shadow-emerald-500/40 hidden peer-checked:flex">
                        @include('partials.icon', ['name' => 'check', 'class' => 'w-3 h-3'])
                    </span>

                    <span class="mx-auto w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 flex items-center justify-center text-base mb-1.5 transition-all duration-300 group-hover:scale-105 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-md peer-checked:shadow-emerald-500/30 peer-checked:scale-110">
                        @include('partials.icon', ['name' => 'shield-user', 'class' => 'w-5 h-5'])
                    </span>
                    <span class="block font-display font-extrabold text-xs text-slate-800 dark:text-slate-100">Admin / Guru</span>
                    <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Pantau & Rekap</span>
                </div>
            </label>
        </div>

        @error('login_as')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror

        <!-- Quick portal tip -->
        <div class="mt-2.5 px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
            <span class="flex items-center gap-1.5 min-w-0">
                @include('partials.icon', ['name' => 'info', 'class' => 'w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0'])
                <span id="portal-hint-text" class="truncate">Gunakan Nomor Induk Siswa (NIS) yang terdaftar di sekolah.</span>
            </span>
            <button type="button" id="quick-demo-btn" class="shrink-0 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline ml-2 cursor-pointer">
                Contoh ID
            </button>
        </div>
    </div>

    <!-- ======== NIS / USERNAME INPUT ======== -->
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label id="nis-label" for="nis" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                NIS Siswa
            </label>
            <button type="button" id="nis-demo-link" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:underline underline-offset-2 transition-colors cursor-pointer" title="Isi otomatis dengan ID contoh">
                @include('partials.icon', ['name' => 'wand-sparkles', 'class' => 'w-2.5 h-2.5'])
                Contoh ID
            </button>
        </div>
        <div class="relative auth-icon-wrap">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors" id="nis-icon-wrap">
                <span id="nis-icon">@include('partials.icon', ['name' => 'id-card', 'class' => 'w-4 h-4'])</span>
            </span>
            <input type="text" id="nis" name="nis" value="{{ old('nis') }}" required {{ $isMobile ? '' : 'autofocus' }}
                placeholder="Contoh: 12345678"
                class="auth-input !pl-10 !py-3.5 pr-10">

            <!-- Quick clear button -->
            <button type="button" id="clear-nis-btn" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-300 dark:text-slate-600 hover:text-slate-500 dark:hover:text-slate-300 transition-colors hidden cursor-pointer" title="Hapus teks">
                @include('partials.icon', ['name' => 'circle-x', 'class' => 'w-4 h-4'])
            </button>
        </div>
        @error('nis')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- ======== PASSWORD INPUT ======== -->
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                Password
            </label>
            <span id="capslock-warning" class="hidden text-[10px] font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1 animate-pulse">
                @include('partials.icon', ['name' => 'triangle-alert', 'class' => 'w-3.5 h-3.5'])
                Caps Lock Aktif
            </span>
        </div>

        <div class="relative auth-icon-wrap">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors">
                @include('partials.icon', ['name' => 'lock', 'class' => 'w-4 h-4'])
            </span>
            <input type="password" id="password" name="password" required
                placeholder="••••••••"
                class="auth-input !pl-10 !py-3.5 !pr-11">

            <button type="button" id="toggle-password" title="Tampilkan / sembunyikan password" aria-label="Tampilkan / sembunyikan password"
                class="absolute inset-y-0 right-0 pr-3.5 pl-2 flex items-center text-slate-400 dark:text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors focus:outline-none cursor-pointer">
@include('partials.icon', ['name' => 'eye', 'class' => 'w-4 h-4'])
            </button>
        </div>
        @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- ======== REMEMBER & HELP ======== -->
    <div class="flex items-center justify-between text-xs pt-0.5">
        <label class="flex items-center gap-2 text-slate-600 dark:text-slate-300 cursor-pointer select-none">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-600 dark:text-emerald-500 focus:ring-emerald-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800 transition-colors">
            <span class="font-medium text-xs">Ingat Saya</span>
        </label>

        <a href="javascript:void(0)" onclick="alert('Jika Anda lupa password atau belum terdaftar, silakan hubungi Guru Wali Kelas atau Administrator Sekolah.')" class="font-medium text-xs text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 transition-colors">
            Butuh bantuan?
        </a>
    </div>

    <!-- ======== SUBMIT BUTTON ======== -->
    <button type="submit" id="login-submit-btn"
        class="btn-shine group relative overflow-hidden w-full py-3.5 rounded-2xl font-bold text-sm text-white shadow-lg shadow-emerald-600/25 hover:shadow-xl hover:shadow-emerald-600/35 hover:brightness-110 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 active:scale-[.98] active:brightness-95 disabled:opacity-75 disabled:cursor-not-allowed mt-2.5 cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/40">

        <span id="login-bg-emerald" class="absolute inset-0 bg-gradient-to-r from-primary-700 via-primary-600 to-primary-700 bg-[length:200%_auto] transition-opacity duration-300 opacity-100"></span>

        <span class="relative z-10 flex items-center gap-2.5">
            <span id="login-spinner" class="hidden">@include('partials.icon', ['name' => 'loader-circle', 'class' => 'w-4 h-4 animate-spin'])</span>
            <span id="login-submit-label" class="tracking-wide">Masuk Sekarang</span>
            <span id="login-arrow" class="transition-transform duration-300 group-hover:translate-x-1.5">@include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])</span>
        </span>
    </button>

    </form>

@if($isMobile)
    <p class="text-center text-[11px] text-slate-500 dark:text-slate-400 mt-6">
        &copy; {{ date('Y') }} Jurnal Siswa Mandiri
    </p>
@endif