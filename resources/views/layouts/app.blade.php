<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    @include('partials.pwa-meta')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Jurnal Harian')</title>

    <!-- ===== Early paint: page loader (inline, no external dependencies) ===== -->
    <style>
        #page-loader {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #f8fafc;
            transition: opacity .5s ease;
        }
        .dark #page-loader { background-color: #0f172a; }

        /* === Walking Character Loader === */
        .walk-loader {
            scale: 0.75;
            position: relative;
            width: 200px;
            height: 200px;
            translate: 10px -20px;
        }
        .walk-loader svg {
            position: absolute;
            top: 0;
            left: 0;
        }
        .walk-loader .head {
            translate: 27px -30px;
            z-index: 3;
            animation: wl-bob 1s infinite ease-in;
        }
        .walk-loader .bod {
            translate: 0 30px;
            z-index: 3;
            animation: wl-bob 1s infinite ease-in-out;
        }
        .walk-loader .legr {
            translate: 75px 135px;
            z-index: 0;
            animation: wl-rstep 1s infinite ease-in;
            animation-delay: 0.45s;
        }
        .walk-loader .legl {
            translate: 30px 155px;
            z-index: 3;
            animation: wl-lstep 1s infinite ease-in;
        }
        .dark .walk-loader {
            filter: invert(1);
        }
        #wl-gnd {
            translate: -140px 0;
            rotate: 10deg;
            z-index: -1;
            filter: blur(0.5px) drop-shadow(1px 3px 5px #000000);
            opacity: 0.25;
            animation: wl-scroll 5s infinite linear;
        }
        @keyframes wl-bob {
            0% { transform: translateY(0) rotate(3deg); }
            5% { transform: translateY(0) rotate(3deg); }
            25% { transform: translateY(5px) rotate(0deg); }
            50% { transform: translateY(0) rotate(-3deg); }
            70% { transform: translateY(5px) rotate(0deg); }
            100% { transform: translateY(0) rotate(3deg); }
        }
        @keyframes wl-lstep {
            0% { transform: translateY(0) rotate(-5deg); }
            33% { transform: translateY(-15px) translate(32px) rotate(35deg); }
            66% { transform: translateY(0) translate(25px) rotate(-25deg); }
            100% { transform: translateY(0) rotate(-5deg); }
        }
        @keyframes wl-rstep {
            0% { transform: translateY(0) translate(0) rotate(-5deg); }
            33% { transform: translateY(-10px) translate(30px) rotate(35deg); }
            66% { transform: translateY(0) translate(20px) rotate(-25deg); }
            100% { transform: translateY(0) translate(0) rotate(-5deg); }
        }
        @keyframes wl-scroll {
            0% { transform: translateY(25px) translate(50px); opacity: 0; }
            33% { opacity: 0.25; }
            66% { opacity: 0.25; }
            to { transform: translateY(-50px) translate(-100px); opacity: 0; }
        }
    </style>

    <!-- Google Fonts: Plus Jakarta Sans + Baloo 2 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Baloo+2:wght@600;700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Baloo+2:wght@600;700;800&display=swap">
    </noscript>

    <!-- Font Awesome Icons -->
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </noscript>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Custom scrollbar & mobile navigation safe area */
        body {
            padding-bottom: 5rem;
        }
        @media (min-width: 1024px) {
            body {
                padding-bottom: 0;
            }
        }

        /* Checkbox pop & bounce animation */
        .animate-check-pop {
            animation: checkPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        /* Modal & toast fade-in (used by .animate-fade-in in several views) */
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(8px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .animate-fade-in {
            animation: fadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    <!-- ===== Print: Rekap Laporan (navbar/loader disembunyikan, kartu di-flatten) ===== -->
    <style>
        @media print {
            header, footer, #page-loader, .no-print { display: none !important; }

            body { background: #ffffff !important; }

            .print-plain {
                background: #ffffff !important;
                box-shadow: none !important;
                border-color: #cbd5e1 !important;
                border-radius: 0 !important;
            }

            .print-plain thead { background: #f1f5f9 !important; }

            .print-table .status-pill {
                background: #ffffff !important;
                color: #0f172a !important;
                border: 1px solid #94a3b8 !important;
            }

            table { border-collapse: collapse; width: 100%; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-100 min-h-screen">

    <!-- Page Loading Overlay -->
    @if(!isset($hidePageLoader))
    <div id="page-loader" class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-900 transition-opacity duration-500">
        @hasSection('page-loader')
            @yield('page-loader')
        @else
            @include('partials.walk-loader')
        @endif
    </div>
    @endif

    <!-- Top Navigation Header -->
    <header class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200/80 dark:border-slate-700">
        <div class="w-full px-4 sm:px-6 lg:px-10">
            <div class="flex items-center justify-between h-16">

                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-white p-0.5 shadow-md shadow-emerald-500/10 border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Jurnal 7 Kebiasaan" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <span class="font-extrabold text-slate-900 dark:text-slate-100 tracking-tight text-base sm:text-lg block leading-tight">SMK BPPI</span>
                        <span class="text-[10px] font-semibold text-emerald-600 tracking-wider uppercase hidden sm:block">Jurnal 7 Kebiasaan</span>
                    </div>
                </div>

                <!-- User Info & Logout (Desktop) -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ Auth::user()->name }}</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">NIS: {{ Auth::user()->nis }} &bull; {{ Auth::user()->kelas }}</span>
                        </div>
                    </div>

                    @if(Auth::user()->role === 'siswa')
                        <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-xs font-extrabold {{ Auth::user()->jurusan['badge'] }}">{{ Auth::user()->jurusan['label'] }}</span>
                    @elseif(Auth::user()->role === 'admin')
                        <span class="px-2.5 py-1 bg-primary-50 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300 rounded-full text-xs font-extrabold">Admin</span>
                    @elseif(Auth::user()->role === 'guru')
                        <span class="px-2.5 py-1 bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 rounded-full text-xs font-extrabold">Guru/Wali Kelas</span>
                    @endif

                    @include('partials.pwa-install-button', ['variant' => 'header'])

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar dari Aplikasi" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-500/20 hover:text-rose-600 dark:hover:text-rose-300 transition-all flex items-center justify-center text-sm font-semibold">
                            @include('partials.icon', ['name' => 'log-out', 'class' => 'w-4 h-4'])
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </header>

    <!-- Main Content Layout -->
    <div class="w-full px-4 sm:px-6 lg:px-10 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Desktop Sidebar Navigation -->
            <aside class="hidden lg:block lg:col-span-3 xl:col-span-2">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-200/80 dark:border-slate-700 sticky top-24 space-y-1">
                    <div class="px-3 py-2 text-xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Menu Utama</div>

                    @if(Auth::user()->role === 'siswa')
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 h-12 px-4 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                            @include('partials.icon', ['name' => 'list-checks', 'class' => 'w-4 h-4'])
                            <span>Dashboard Jurnal</span>
                        </a>

                        <a href="{{ route('history') }}" class="flex items-center gap-3 h-12 px-4 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('history') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                            @include('partials.icon', ['name' => 'calendar-days', 'class' => 'w-4 h-4'])
                            <span>Riwayat & Kalender</span>
                        </a>

                        <a href="{{ route('statistics') }}" class="flex items-center gap-3 h-12 px-4 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('statistics') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                            @include('partials.icon', ['name' => 'chart-pie', 'class' => 'w-4 h-4'])
                            <span>Statistik Kebiasaan</span>
                        </a>
                        <a href="{{ route('profile') }}" class="flex items-center gap-3 h-12 px-4 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('profile') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                            @include('partials.icon', ['name' => 'user-cog', 'class' => 'w-4 h-4'])
                            <span>Profil</span>
                        </a>
                    @elseif(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 h-12 px-4 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('admin.dashboard') && request('view') !== 'registered' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                            @include('partials.icon', ['name' => 'chart-column', 'class' => 'w-4 h-4'])
                            <span>Rekap Semua Siswa</span>
                        </a>
                        <a href="{{ route('teacher.index') }}" class="flex items-center gap-3 h-12 px-4 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('teacher.index') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                            @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4'])
                            <span>Data Siswa</span>
                        </a>
                        <a href="{{ route('admin.dashboard', ['view' => 'registered']) }}#daftar-siswa-terdaftar" class="flex items-center gap-3 h-12 px-4 rounded-xl font-bold text-sm transition-all {{ request('view') === 'registered' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                            @include('partials.icon', ['name' => 'user-check', 'class' => 'w-4 h-4'])
                            <span>Siswa Terdaftar</span>
                        </a>
                    @else
                        <a href="{{ route('teacher.index') }}" class="flex items-center gap-3 h-12 px-4 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('teacher.index') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                            @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4'])
                            <span>Panel Wali Kelas</span>
                        </a>
                    @endif

                    @if(Auth::user()->role === 'admin')
                        <!-- Ringkasan akun hanya untuk admin -->
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-700 mt-4 px-2">
                            <div class="p-3 bg-slate-50 dark:bg-slate-700/40 rounded-xl text-xs space-y-1">
                                <div class="font-bold text-slate-700 dark:text-slate-200 flex items-center justify-between">
                                    <span>Status Akun:</span>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">Aktif</span>
                                </div>
                                <div class="text-slate-500 dark:text-slate-400">Peran: <span class="font-semibold text-slate-700 dark:text-slate-200 uppercase">Admin</span></div>
                            </div>
                        </div>
                    @endif
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="col-span-1 lg:col-span-9 xl:col-span-10 space-y-6">
                <!-- Toast Notification Container -->
                @if(session('success'))
                    <div id="toast-success" class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between shadow-sm animate-fade-in">
                        <div class="flex items-center gap-3 font-semibold">
                            @include('partials.icon', ['name' => 'circle-check', 'class' => 'w-5 h-5 text-emerald-500'])
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="document.getElementById('toast-success').remove()" class="text-emerald-500 hover:text-emerald-700">
                            @include('partials.icon', ['name' => 'x', 'class' => 'w-4 h-4'])
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>

        </div>
    </div>

    <!-- Mobile Bottom Navigation Bar (HP View) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-700 z-50 px-2 py-2">
        <div class="flex items-center justify-around">
            @if(Auth::user()->role === 'siswa')
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    @include('partials.icon', ['name' => 'list-checks', 'class' => 'w-5 h-5'])
                    <span class="text-[11px] mt-0.5">Jurnal</span>
                </a>

                <a href="{{ route('history') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('history') ? 'text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    @include('partials.icon', ['name' => 'calendar-days', 'class' => 'w-5 h-5'])
                    <span class="text-[11px] mt-0.5">Riwayat</span>
                </a>

                <a href="{{ route('statistics') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('statistics') ? 'text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    @include('partials.icon', ['name' => 'chart-pie', 'class' => 'w-5 h-5'])
                    <span class="text-[11px] mt-0.5">Statistik</span>
                </a>
                <a href="{{ route('profile') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('profile') ? 'text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    @include('partials.icon', ['name' => 'user-cog', 'class' => 'w-5 h-5'])
                    <span class="text-[11px] mt-0.5">Profil</span>
                </a>
            @elseif(Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') && request('view') !== 'registered' ? 'text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    @include('partials.icon', ['name' => 'chart-column', 'class' => 'w-5 h-5'])
                    <span class="text-[11px] mt-0.5">Rekap</span>
                </a>
                <a href="{{ route('teacher.index') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('teacher.index') ? 'text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    @include('partials.icon', ['name' => 'users', 'class' => 'w-5 h-5'])
                    <span class="text-[11px] mt-0.5">Siswa</span>
                </a>
                <a href="{{ route('admin.dashboard', ['view' => 'registered']) }}#daftar-siswa-terdaftar" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request('view') === 'registered' ? 'text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    @include('partials.icon', ['name' => 'user-check', 'class' => 'w-5 h-5'])
                    <span class="text-[11px] mt-0.5">Terdaftar</span>
                </a>
            @else
                <a href="{{ route('teacher.index') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('teacher.index') ? 'text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    @include('partials.icon', ['name' => 'users', 'class' => 'w-5 h-5'])
                    <span class="text-[11px] mt-0.5">Wali Kelas</span>
                </a>
            @endif

        </div>
    </nav>

    <script>
        // Page loader helpers: auto-hide after load; showPageLoader() re-shows it
        (function () {
            const loader = document.getElementById('page-loader');
            if (!loader) return;
            window.showPageLoader = function () {
                loader.style.display = 'flex';
                loader.style.opacity = '1';
            };
            const start = Date.now();
            function hide() {
                const delay = Math.max(0, 2000 - (Date.now() - start));
                setTimeout(() => {
                    loader.style.opacity = '0';
                    setTimeout(() => { loader.style.display = 'none'; }, 500);
                }, delay);
            }
            if (document.readyState === 'complete') {
                hide();
            } else {
                window.addEventListener('load', hide);
            }

            // Pengaman: jika 'load' tidak kunjung selesai (CDN/lama), paksa sembunyikan.
            setTimeout(hide, 8000);

            // Chrome (mobile/desktop) me-restore halaman dari bfcache/prerender
            // dengan loader sudah tersembunyi. Tampilkan loader lagi sebentar.
            window.addEventListener('pageshow', function (event) {
                let fromCache = event.persisted;
                if (!fromCache) {
                    try {
                        const nav = performance.getEntriesByType('navigation')[0];
                        fromCache = !!(nav && nav.activationStart > 0);
                    } catch (e) {}
                }
                if (!fromCache) return;
                loader.style.display = 'flex';
                loader.style.opacity = '1';
                setTimeout(function () {
                    loader.style.opacity = '0';
                    setTimeout(function () { loader.style.display = 'none'; }, 500);
                }, 1000);
            });
        })();
    </script>

    @stack('scripts')

    @livewireScripts
    @include('partials.pwa-register')
</body>
</html>
