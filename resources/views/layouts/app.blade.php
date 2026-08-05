<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Jurnal 7 Kebiasaan Baik')</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Canvas Confetti CDN -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

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
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 min-h-screen">

    <!-- Top Navigation Header -->
    <header class="bg-white/90 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-md shadow-emerald-500/20">
                        <i class="fa-solid fa-heart-pulse text-lg"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-slate-900 tracking-tight text-base sm:text-lg block leading-tight">Jurnal 7 Kebiasaan</span>
                        <span class="text-[10px] font-semibold text-emerald-600 tracking-wider uppercase">Sistem Self-Tracking Siswa</span>
                    </div>
                </div>

                <!-- User Info & Logout (Desktop) -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-bold text-slate-800">{{ Auth::user()->name }}</span>
                        <span class="text-[11px] text-slate-500 font-medium">NIS: {{ Auth::user()->nis }} &bull; {{ Auth::user()->kelas }}</span>
                    </div>

                    @if(Auth::user()->role === 'guru')
                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-extrabold">Guru/Wali Kelas</span>
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar dari Aplikasi" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition-all flex items-center justify-center text-sm font-semibold">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </header>

    <!-- Main Content Layout -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Desktop Sidebar Navigation -->
            <aside class="hidden lg:block lg:col-span-3">
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 sticky top-24 space-y-1">
                    <div class="px-3 py-2 text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-1">Menu Utama</div>

                    @if(Auth::user()->role === 'siswa')
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-list-check text-base"></i>
                            <span>Dashboard Jurnal</span>
                        </a>

                        <a href="{{ route('history') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('history') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-calendar-days text-base"></i>
                            <span>Riwayat & Kalender</span>
                        </a>

                        <a href="{{ route('statistics') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('statistics') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-chart-pie text-base"></i>
                            <span>Statistik Kebiasaan</span>
                        </a>
                    @else
                        <a href="{{ route('teacher.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('teacher.index') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-users text-base"></i>
                            <span>Panel Wali Kelas</span>
                        </a>
                    @endif

                    <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('profile') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-user-gear text-base"></i>
                        <span>Profil & Pengaturan</span>
                    </a>

                    <!-- User Summary Box in Sidebar -->
                    <div class="pt-4 border-t border-slate-100 mt-4 px-2">
                        <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1">
                            <div class="font-bold text-slate-700 flex items-center justify-between">
                                <span>Status Akun:</span>
                                <span class="text-emerald-600 font-extrabold">Aktif</span>
                            </div>
                            <div class="text-slate-500">Agama/Ibadah: <span class="font-semibold text-slate-700 uppercase">{{ Auth::user()->worship_type }}</span></div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="col-span-1 lg:col-span-9 space-y-6">
                <!-- Toast Notification Container -->
                @if(session('success'))
                    <div id="toast-success" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm animate-fade-in">
                        <div class="flex items-center gap-3 font-semibold">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="document.getElementById('toast-success').remove()" class="text-emerald-500 hover:text-emerald-700">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>

        </div>
    </div>

    <!-- Mobile Bottom Navigation Bar (HP View) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 z-50 px-2 py-2">
        <div class="flex items-center justify-around">
            @if(Auth::user()->role === 'siswa')
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    <i class="fa-solid fa-list-check text-lg"></i>
                    <span class="text-[11px] mt-0.5">Jurnal</span>
                </a>

                <a href="{{ route('history') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('history') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    <i class="fa-solid fa-calendar-days text-lg"></i>
                    <span class="text-[11px] mt-0.5">Riwayat</span>
                </a>

                <a href="{{ route('statistics') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('statistics') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    <i class="fa-solid fa-chart-pie text-lg"></i>
                    <span class="text-[11px] mt-0.5">Statistik</span>
                </a>
            @else
                <a href="{{ route('teacher.index') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('teacher.index') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    <i class="fa-solid fa-users text-lg"></i>
                    <span class="text-[11px] mt-0.5">Wali Kelas</span>
                </a>
            @endif

            <a href="{{ route('profile') }}" class="flex flex-col items-center py-1 px-3 rounded-xl transition-all {{ request()->routeIs('profile') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <i class="fa-solid fa-user-gear text-lg"></i>
                <span class="text-[11px] mt-0.5">Profil</span>
            </a>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
