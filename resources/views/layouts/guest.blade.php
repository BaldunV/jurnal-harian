<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.pwa-meta')
    <title>@yield('title', 'Jurnal Siswa')</title>

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
    @vite(['resources/css/login.css'])
    <style>
        /* Ambient drifting background orbs */
        @keyframes ambientDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(34px, -26px) scale(1.08); }
        }
        @keyframes ambientDriftSlow {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-30px, 24px) scale(1.1); }
        }
        .ambient-drift { animation: ambientDrift 16s ease-in-out infinite; }
        .ambient-drift-slow { animation: ambientDriftSlow 20s ease-in-out infinite; }

        /* Card entrance */
        @keyframes cardEnter {
            0%   { opacity: 0; transform: translateY(14px) scale(.985); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .card-enter { animation: cardEnter .5s cubic-bezier(.16, 1, .3, 1) forwards; }

        /* Selection theming */
        ::selection { background: rgba(16, 185, 129, .22); }
        .dark ::selection { background: rgba(217, 70, 239, .35); }

        /* Mobile GPU/CPU perf A/B test - only disable backdrop blur */
        @media (max-width: 767px) {
            .backdrop-blur-xl {
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-100 min-h-screen flex flex-col md:justify-center items-center relative overflow-x-clip">

    <!-- Ambient Background -->
    <div class="auth-ambient fixed inset-0 -z-10 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-40 -right-32 w-[30rem] h-[30rem] rounded-full bg-emerald-300/50 dark:bg-emerald-500/10 blur-3xl ambient-drift"></div>
        <div class="absolute -bottom-44 -left-32 w-[34rem] h-[34rem] rounded-full bg-teal-300/60 dark:bg-teal-500/10 blur-3xl ambient-drift-slow"></div>
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-80 h-80 rounded-full bg-teal-200/60 dark:bg-teal-500/10 blur-3xl"></div>
        <div class="absolute top-[14%] right-[8%] w-72 h-72 rounded-full bg-amber-200/50 dark:bg-amber-500/5 blur-3xl ambient-drift" style="animation-delay:-8s"></div>
        <div class="absolute inset-0 auth-grid opacity-40 dark:opacity-20"></div>
    </div>

    @section('auth-panel')
    <div class="w-full max-w-md my-auto px-4 py-8">
        <!-- Logo Header -->
        <div class="text-center mb-8 card-enter">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Sistem Self-Tracking Siswa Sekolah / SMK</p>
        </div>

        <!-- Card Container -->
        <div class="relative bg-white/90 dark:bg-slate-900/80 backdrop-blur-xl rounded-3xl p-6 sm:p-8 shadow-2xl shadow-slate-900/5 dark:shadow-black/40 border border-slate-100 dark:border-slate-700 card-enter" style="animation-delay:.08s">
            <span class="absolute top-0 left-6 right-6 h-[3px] rounded-b-full bg-gradient-to-r from-emerald-500 via-teal-500 to-amber-400" aria-hidden="true"></span>
            @if(session('success'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm flex items-center gap-3">
                    @include('partials.icon', ['name' => 'circle-check', 'class' => 'w-5 h-5 text-emerald-500 shrink-0'])
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-300 text-sm">
                    <div class="font-bold flex items-center gap-2 mb-1">
                        @include('partials.icon', ['name' => 'circle-alert', 'class' => 'w-4 h-4']) Terjadi Kesalahan:
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>

        <p class="text-center text-xs text-slate-400 dark:text-slate-500 mt-6 card-enter" style="animation-delay:.16s">
            &copy; {{ date('Y') }} Jurnal Siswa Mandiri
        </p>
    </div>
    @endsection

    @yield('auth-panel')

    <div class="w-full max-w-md px-4 pb-6">
        @include('partials.pwa-install-button')
    </div>

    <script>
        // Page loader helpers removed.
        window.showPageLoader = function () {};
    </script>

    @stack('scripts')

    @include('partials.pwa-register')
</body>
</html>
