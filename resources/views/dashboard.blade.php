@extends('layouts.app')

@section('title', 'Dashboard Jurnal Harian')

@section('content')

<style>
/* UIverse.io Inspired Button Styles */

/* Button 1: Glowing Save Button */
.btn-save-glow {
    position: relative;
    padding: 1rem 2rem;
    font-size: 0.875rem;
    font-weight: 700;
    color: white;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    border-radius: 9999px;
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
}

.btn-save-glow::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.btn-save-glow:hover::before {
    opacity: 1;
}

.btn-save-glow:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
}

.btn-save-glow:active {
    transform: translateY(0);
}

.btn-save-glow .btn-content {
    position: relative;
    z-index: 1;
}

/* Button 2: 3D Push Button for Time Presets */
.btn-time-preset {
    position: relative;
    padding: 0.5rem 1rem;
    font-size: 0.625rem;
    font-weight: 700;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.1s ease;
    box-shadow: 0 4px 0 0 rgba(0, 0, 0, 0.1);
}

.btn-time-preset:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 0 0 rgba(0, 0, 0, 0.1);
}

.btn-time-preset:active {
    transform: translateY(2px);
    box-shadow: 0 2px 0 0 rgba(0, 0, 0, 0.1);
}

/* Variant Amber for Bangun Pagi */
.btn-time-preset-amber {
    background: linear-gradient(to bottom, #fcd34d 0%, #f59e0b 100%);
    color: #78350f;
}

.btn-time-preset-amber:hover {
    background: linear-gradient(to bottom, #fde047 0%, #f59e0b 100%);
}

/* Variant Emerald for Tidur */
.btn-time-preset-emerald {
    background: linear-gradient(to bottom, #6ee7b7 0%, #10b981 100%);
    color: #064e3b;
}

.btn-time-preset-emerald:hover {
    background: linear-gradient(to bottom, #86efac 0%, #10b981 100%);
}

/* Button 3: Glassmorphism Badge Button (matching Streak style) */
.btn-badge-glass {
    position: relative;
    padding: 1rem 1.25rem;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 1rem;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    /* Sama seperti gradient-border-hero */
    background: linear-gradient(135deg, #047857 0%, #10b981 50%, #14b8a6 100%);
    background-size: 200% 200%;
    box-shadow: 0 8px 30px rgba(2, 44, 34, 0.35);
    border: none;
    color: white;
}

.btn-badge-glass:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 35px rgba(16, 185, 129, 0.35),
                0 4px 10px rgba(0, 0, 0, 0.15);
    background-position: 100% 0;
}

.btn-badge-glass:active {
    transform: translateY(0);
}

/* Button 4: Glassmorphism Button */
.btn-glass {
    padding: 0.75rem 1.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-glass:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

/* Button 5: Animated Gradient Button */
.btn-refresh-gradient {
    position: relative;
    padding: 0.5rem 0;
    width: 100%;
    font-size: 0.6875rem;
    font-weight: 700;
    color: white;
    background: linear-gradient(270deg, #10b981, #14b8a6, #06b6d4);
    background-size: 600% 600%;
    border: none;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.btn-refresh-gradient:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
}

/* Button 6: Bounce Effect Button */
.btn-modal-close {
    padding: 0.625rem 1.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border: none;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-modal-close:hover {
    background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
    transform: scale(1.05);
}

.btn-modal-close:active {
    transform: scale(0.95);
}

/* Dark mode adjustments */
.dark .btn-modal-close {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.dark .btn-modal-close:hover {
    background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
}

/* Button 7: Detail Button with Slide Effect */
.btn-detail-slide {
    position: relative;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #334155;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-detail-slide::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    transition: left 0.3s ease;
    z-index: 0;
}

.btn-detail-slide:hover::before {
    left: 0;
}

.btn-detail-slide:hover {
    border-color: #cbd5e1;
    transform: translateX(2px);
}

.btn-detail-slide span {
    position: relative;
    z-index: 1;
}

/* Dark mode for detail button */
.dark .btn-detail-slide {
    background: #1e293b;
    color: #e2e8f0;
    border-color: #475569;
}

.dark .btn-detail-slide::before {
    background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
}

.dark .btn-detail-slide:hover {
    border-color: #64748b;
}

/* Toy Camera Checkbox Style */
.toy-camera-container {
    --cam-main: #00d2d3;
    --cam-dark: #01a3a4;
    --cam-accent: #ff9f43;
    --cam-shutter: #ff4757;
    --photo-bg: #ffffff;
    --toy-size: 4em;
    --elastic: cubic-bezier(0.68, -0.6, 0.32, 1.6);
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
}

.toy-camera-wrapper {
    position: relative;
    width: var(--toy-size);
    height: calc(var(--toy-size) * 0.9);
    perspective: 1200px;
    cursor: pointer;
}

.toy-camera-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.toy-camera-body {
    position: absolute;
    inset: 0;
    background: var(--cam-main);
    border-radius: 1.2em;
    transform-style: preserve-3d;
    transform: translateZ(20px);
    transition: all 0.5s var(--elastic);
    box-shadow:
        0 0.6em 0 var(--cam-dark),
        0 1em 2em rgba(0, 0, 0, 0.15),
        inset 0 0.2em 0.3em rgba(255, 255, 255, 0.4);
    z-index: 10;
}

.toy-camera-body::before {
    content: "";
    position: absolute;
    top: 20%;
    left: 0;
    width: 100%;
    height: 15%;
    background: var(--cam-accent);
    box-shadow: inset 0 0.1em 0.2em rgba(0, 0, 0, 0.1);
}

.toy-camera-button {
    position: absolute;
    top: -0.6em;
    right: 15%;
    width: 1.2em;
    height: 0.8em;
    background: var(--cam-shutter);
    border-radius: 0.3em 0.3em 0 0;
    box-shadow:
        0 0.2em 0 #cc3643,
        inset 0 0.1em 0.1em rgba(255, 255, 255, 0.3);
    transition: all 0.2s ease;
}

.toy-camera-lens {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 60%;
    height: 65%;
    background: #2d3436;
    border-radius: 50%;
    transform: translate(-50%, -40%) translateZ(15px);
    border: 0.3em solid var(--cam-dark);
    box-shadow:
        0 0.4em 0.8em rgba(0, 0, 0, 0.3),
        inset 0 0.2em 0.5em rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.toy-camera-lens::after {
    content: "";
    position: absolute;
    top: 15%;
    left: 20%;
    width: 30%;
    height: 20%;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: rotate(-25deg);
}

.toy-camera-photo {
    position: absolute;
    bottom: 10%;
    left: 50%;
    width: 75%;
    height: 80%;
    background: var(--photo-bg);
    border-radius: 0.4em;
    transform: translateX(-50%) translateZ(-10px) translateY(0);
    transition: all 0.7s var(--elastic);
    box-shadow:
        0 0.2em 0.5em rgba(0, 0, 0, 0.1),
        inset 0 0 0 0.2em #f1f2f6;
    display: flex;
    flex-direction: column;
    padding: 0.4em;
    opacity: 0;
    z-index: 1;
}

.photo-image {
    width: 100%;
    height: 70%;
    background: #74b9ff;
    border-radius: 0.2em;
    position: relative;
    overflow: hidden;
}

.photo-image::after {
    content: "";
    position: absolute;
    top: 15%;
    right: 15%;
    width: 0.5em;
    height: 0.5em;
    background: #ffeaa7;
    border-radius: 50%;
}

.photo-text {
    width: 60%;
    height: 0.2em;
    background: #dfe6e9;
    margin-top: 0.4em;
    border-radius: 0.1em;
}

.toy-camera-wrapper:hover .toy-camera-body {
    transform: translateZ(40px) rotateX(10deg);
}

.toy-camera-input:active + .toy-camera-body {
    transform: translateZ(10px) scale(0.95);
}

.toy-camera-input:active + .toy-camera-body .toy-camera-button {
    transform: translateY(0.3em);
    box-shadow: 0 0.1em 0 #cc3643;
}

.toy-camera-input:checked + .toy-camera-body {
    transform: translateZ(30px) translateY(-10%);
}

.toy-camera-input:checked + .toy-camera-body .toy-camera-photo {
    opacity: 1;
    transform: translateX(-50%) translateZ(-30px) translateY(120%) rotate(5deg);
    animation: photo-bounce 0.8s var(--elastic);
}

.toy-camera-input:checked + .toy-camera-body .toy-camera-lens::before {
    content: "";
    position: absolute;
    inset: 0;
    background: white;
    opacity: 0;
    animation: lens-flash 0.4s ease-out;
}

.toy-camera-input:focus-visible + .toy-camera-body {
    outline: 3px solid #70a1ff;
    outline-offset: 15px;
}

@keyframes photo-bounce {
    0% {
        transform: translateX(-50%) translateZ(-30px) translateY(0);
    }
    100% {
        transform: translateX(-50%) translateZ(-30px) translateY(120%) rotate(5deg);
    }
}

@keyframes lens-flash {
    0% {
        opacity: 1;
        transform: scale(0.1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.5);
    }
    100% {
        opacity: 0;
        transform: scale(2);
    }
}

.toy-camera-shadow {
    position: absolute;
    bottom: -1em;
    left: 50%;
    width: 90%;
    height: 1.5em;
    background: radial-gradient(circle, rgba(0, 0, 0, 0.15) 0%, transparent 70%);
    transform: translateX(-50%);
    transition: all 0.5s ease;
    pointer-events: none;
}

.toy-camera-input:checked ~ .toy-camera-shadow {
    width: 120%;
    opacity: 0.1;
}
</style>


<!-- Header Banner Modern -->
<div class="modern-hero-gradient relative rounded-3xl p-6 sm:p-8 text-white shadow-2xl overflow-hidden">
    <!-- Shader Waves Background (React) -->
    <div class="react-shader absolute inset-0 z-0" aria-hidden="true"></div>
    
    <!-- Animated Mesh Background -->
    <div class="mesh-background absolute inset-0"></div>
    
    <!-- Animated Blobs -->
    <div class="animated-blob w-72 h-72 bg-emerald-400/30 top-0 right-0" style="animation-delay: 0s;"></div>
    <div class="animated-blob w-64 h-64 bg-teal-400/25 bottom-0 left-0" style="animation-delay: 5s;"></div>
    <div class="animated-blob w-56 h-56 bg-emerald-500/20 top-1/2 left-1/3" style="animation-delay: 10s;"></div>
    
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-3">
            <div class="modern-badge inline-flex">
                @include('partials.icon', ['name' => 'calendar-days', 'class' => 'w-3.5 h-3.5'])
                <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight flex items-center gap-2 flex-wrap">
                Hai, <span class="bg-gradient-to-r from-white via-emerald-100 to-teal-100 bg-clip-text text-transparent">{{ $user->name }}</span>!
                @include('partials.icon', ['name' => 'hand', 'class' => 'w-7 h-7 text-amber-300'])
            </h1>
            <p class="text-emerald-50/90 text-sm max-w-xl font-medium leading-relaxed">
                Disiplin adalah jembatan antara cita-cita dan pencapaian. Mari lengkapi kebiasaan baik hari ini!
            </p>
        </div>

        <!-- Streak & Badge Pill Modern -->
        <div class="flex items-center gap-4 flex-wrap">
            <div class="gradient-border-hero">
                <div class="gradient-border-hero-inner p-4 flex items-center gap-3 min-w-[200px]">
                    <div class="w-12 h-12 rounded-xl {{ $streak > 0 ? 'bg-gradient-to-br from-amber-400/50 to-amber-500/35 border border-amber-300/40' : 'bg-white/20 border border-white/30' }} flex items-center justify-center shrink-0 shadow-lg transform hover:scale-110 transition-transform">
                        @if($streak > 0)
                            @include('partials.icon', ['name' => 'flame', 'class' => 'w-6 h-6 text-amber-200'])
                        @else
                            @include('partials.icon', ['name' => 'flame', 'class' => 'w-6 h-6 text-amber-200'])
                        @endif
                    </div>
                    <div>
                        <div class="text-[10px] uppercase font-bold tracking-wider text-white/90">Streak Kamu</div>
                        @if($streak > 0)
                            <div class="text-2xl font-extrabold animated-counter" id="streak-counter">{{ $streak }} Hari</div>
                        @else
                            <div class="text-2xl font-extrabold text-white" id="streak-counter">0 Hari</div>
                            <div class="text-[10px] text-white/85 font-medium mt-0.5 max-w-[170px] leading-tight">Ayo mulai! Isi jurnal hari ini untuk menyalakan streak pertamamu
                                @include('partials.icon', ['name' => 'flame', 'class' => 'w-3 h-3 inline-block align-[-1px]'])
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <button onclick="toggleModal('badges-modal')" class="btn-badge-glass flex items-center gap-2">
                @include('partials.icon', ['name' => 'trophy', 'class' => 'w-4 h-4'])
                <span>Lencana</span>
            </button>
        </div>
    </div>
</div>


<!-- Quick Stats Row Modern -->
@php
    $totalActive   = \App\Models\Journal::where('user_id', $user->id)->where('is_fully_completed', true)->count();
    $totalJournals = \App\Models\Journal::where('user_id', $user->id)->count();
    $avgScore      = $totalJournals > 0
        ? round(\App\Models\Journal::where('user_id', $user->id)->avg('completed_count'), 1)
        : 0;
    $bestHabitsMap = [
        'bangun_pagi'   => ['label' => 'Bangun Pagi',  'icon' => 'sunrise'],
        'beribadah'     => ['label' => 'Beribadah',    'icon' => 'hand-heart'],
        'berolahraga'   => ['label' => 'Olahraga',     'icon' => 'footprints'],
        'makan_sehat'   => ['label' => 'Makan Sehat',  'icon' => 'salad'],
        'gemar_belajar' => ['label' => 'Belajar',      'icon' => 'book-open'],
        'bermasyarakat' => ['label' => 'Sosial',       'icon' => 'handshake'],
        'tidur_cepat'   => ['label' => 'Tidur Cepat',  'icon' => 'moon-star'],
    ];
    $bestKey = null; $bestCount = -1;
    foreach ($bestHabitsMap as $key => $meta) {
        $c = \App\Models\Journal::where('user_id', $user->id)->where($key, true)->count();
        if ($c > $bestCount) { $bestCount = $c; $bestKey = $key; }
    }
    $bestHabitMeta = $bestKey ? $bestHabitsMap[$bestKey] : null;
@endphp
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <!-- Stat 1: Hari Lengkap -->
    <div class="stats-card group">
        <div class="flex items-center gap-3 mb-3">
            <div class="modern-icon-container bg-gradient-to-br from-emerald-500/15 to-teal-500/10">
                @include('partials.icon', ['name' => 'calendar-check-2', 'class' => 'w-5 h-5 text-emerald-600 dark:text-emerald-400'])
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Selesai</div>
                <div class="text-2xl font-extrabold animated-counter">{{ $totalActive }}</div>
            </div>
        </div>
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            Hari Lengkap
        </div>
    </div>
    
    <!-- Stat 2: Rata-rata -->
    <div class="stats-card group">
        <div class="flex items-center gap-3 mb-3">
            <div class="modern-icon-container bg-gradient-to-br from-teal-500/15 to-emerald-500/10">
                @include('partials.icon', ['name' => 'trending-up', 'class' => 'w-5 h-5 text-teal-600 dark:text-teal-400'])
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Rata-rata</div>
                <div class="text-2xl font-extrabold animated-counter">{{ $avgScore }}<span class="text-sm text-slate-400 dark:text-slate-500 font-semibold">/7</span></div>
            </div>
        </div>
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
            Kebiasaan/Hari
        </div>
    </div>
    
    <!-- Stat 3: Habit Terbaik -->
    <div class="stats-card group">
        <div class="flex items-center gap-3 mb-3">
            <div class="modern-icon-container bg-gradient-to-br from-amber-500/15 to-amber-400/10">
                @include('partials.icon', ['name' => $bestHabitMeta ? $bestHabitMeta['icon'] : 'star', 'class' => 'w-5 h-5 text-amber-600 dark:text-amber-400'])
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Habit Terbaik</div>
                <div class="text-lg font-extrabold text-slate-900 dark:text-white leading-tight">{{ $bestHabitMeta ? $bestHabitMeta['label'] : '-' }}</div>
            </div>
        </div>
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            Paling Konsisten
        </div>
    </div>
</div>

<!-- Daily Quote + Mood Tracker Modern -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Daily Motivational Quote Modern -->
    <div class="modern-card p-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-transparent rounded-bl-full pointer-events-none"></div>
        <div class="flex items-start gap-4 relative z-10">
            <div class="modern-icon-container bg-gradient-to-br from-emerald-500/15 to-teal-500/10 shrink-0">
                @include('partials.icon', ['name' => 'quote', 'class' => 'w-5 h-5 text-emerald-600 dark:text-emerald-400'])
            </div>
            <div class="flex-1">
                <div class="modern-badge mb-3 text-emerald-700 dark:text-emerald-300 bg-gradient-to-r from-emerald-500/10 to-teal-500/10 border-emerald-500/20">
                    @include('partials.icon', ['name' => 'sparkles', 'class' => 'w-3 h-3 text-amber-400'])
                    <span>Motivasi Hari Ini</span>
                </div>
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 leading-relaxed italic" id="daily-quote-text" style="transition: opacity 0.25s ease, transform 0.25s ease;">Memuat kutipan...</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-2 font-medium" id="daily-quote-author"></p>
            </div>
        </div>
        <button onclick="nextQuote()" class="btn-refresh-gradient flex items-center justify-center gap-1.5 py-2">
            @include('partials.icon', ['name' => 'refresh-cw', 'class' => 'w-3 h-3'])
            Kutipan berikutnya
        </button>
    </div>

    <!-- Mood Tracker Modern -->
    <div class="modern-card p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="modern-icon-container bg-gradient-to-br from-teal-500/15 to-emerald-500/10">
                @include('partials.icon', ['name' => 'smile-plus', 'class' => 'w-5 h-5 text-teal-600 dark:text-teal-400'])
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Mood Tracker</div>
                <div class="text-sm font-bold text-slate-900 dark:text-white">Bagaimana Perasaanmu?</div>
            </div>
        </div>
        <div class="grid grid-cols-5 gap-2">
            <button type="button" onclick="selectMood('excellent', this)" data-mood="excellent"
                class="mood-btn flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-transparent hover:border-emerald-300 hover:bg-emerald-50 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10 transition-all group" title="Sangat Baik">
                <span class="group-hover:scale-125 transition-transform inline-block">
                    @include('partials.icon', ['name' => 'laugh', 'class' => 'w-7 h-7 text-emerald-500'])
                </span>
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 leading-tight text-center">Sangat<br>Baik</span>
            </button>
            <button type="button" onclick="selectMood('good', this)" data-mood="good"
                class="mood-btn flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-transparent hover:border-teal-300 hover:bg-teal-50 dark:hover:border-teal-500/40 dark:hover:bg-teal-500/10 transition-all group" title="Baik">
                <span class="group-hover:scale-125 transition-transform inline-block">
                    @include('partials.icon', ['name' => 'smile', 'class' => 'w-7 h-7 text-teal-500'])
                </span>
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 group-hover:text-teal-600 dark:group-hover:text-teal-400 leading-tight text-center">Baik</span>
            </button>
            <button type="button" onclick="selectMood('neutral', this)" data-mood="neutral"
                class="mood-btn flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-transparent hover:border-amber-300 hover:bg-amber-50 dark:hover:border-amber-500/40 dark:hover:bg-amber-500/10 transition-all group" title="Biasa">
                <span class="group-hover:scale-125 transition-transform inline-block">
                    @include('partials.icon', ['name' => 'meh', 'class' => 'w-7 h-7 text-amber-500'])
                </span>
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 group-hover:text-amber-600 dark:group-hover:text-amber-400 leading-tight text-center">Biasa</span>
            </button>
            <button type="button" onclick="selectMood('sad', this)" data-mood="sad"
                class="mood-btn flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-transparent hover:border-rose-300 hover:bg-rose-50 dark:hover:border-rose-500/40 dark:hover:bg-rose-500/10 transition-all group" title="Kurang">
                <span class="group-hover:scale-125 transition-transform inline-block">
                    @include('partials.icon', ['name' => 'frown', 'class' => 'w-7 h-7 text-rose-500'])
                </span>
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 group-hover:text-rose-600 dark:group-hover:text-rose-400 leading-tight text-center">Kurang</span>
            </button>
            <button type="button" onclick="selectMood('tired', this)" data-mood="tired"
                class="mood-btn flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-transparent hover:border-slate-300 hover:bg-slate-50 dark:hover:border-slate-500/40 dark:hover:bg-slate-500/10 transition-all group" title="Lelah">
                <span class="group-hover:scale-125 transition-transform inline-block">
                    @include('partials.icon', ['name' => 'cloud-fog', 'class' => 'w-7 h-7 text-slate-500'])
                </span>
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300 leading-tight text-center">Lelah</span>
            </button>
        </div>
        <p class="text-[10px] text-slate-400 dark:text-slate-500 text-center mt-3 font-medium" id="mood-save-status">Pilih emoji untuk mencatat suasana hatimu hari ini.</p>
    </div>
</div>

<!-- Progress Bar Card Modern -->
<div class="modern-card p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="modern-icon-container bg-gradient-to-br from-emerald-500/15 to-teal-500/10">
                @include('partials.icon', ['name' => 'list-checks', 'class' => 'w-5 h-5 text-emerald-600 dark:text-emerald-400'])
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Progress Hari Ini</div>
                <div class="text-base font-extrabold text-slate-900 dark:text-white">Kebiasaan Selesai</div>
            </div>
        </div>
        <div class="text-right">
            <div class="flex items-baseline gap-1">
                <span class="text-3xl font-extrabold animated-counter" id="progress-count">{{ $journal->completed_count }}</span>
                <span class="text-sm font-bold text-slate-400 dark:text-slate-500">/ 7</span>
            </div>
        </div>
    </div>

    <!-- Progress Bar Modern -->
    @php
        $percent = round(($journal->completed_count / 7) * 100);
    @endphp
    <div class="relative w-full h-5 bg-slate-100 dark:bg-slate-700/60 rounded-full overflow-hidden p-0.5 border border-slate-200/60 dark:border-slate-600/60">
        <div id="progress-bar-fill" class="h-full rounded-full transition-all duration-700 ease-out relative overflow-hidden" style="width: {{ $percent }}%;">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 via-teal-500 to-teal-400"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse"></div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-transparent to-white/20 pointer-events-none rounded-full"></div>
    </div>

    <div class="flex items-center justify-between mt-3">
        <div class="flex items-center gap-2">
            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400" id="progress-percentage-text">{{ $percent }}% Selesai</span>
            @if($percent == 100)
                <span class="modern-badge text-emerald-600 bg-emerald-50 dark:bg-emerald-500/15 border-emerald-500/30">
                    @include('partials.icon', ['name' => 'check', 'class' => 'w-3 h-3'])
                    <span>Sempurna!</span>
                </span>
            @endif
        </div>
        <button type="button" onclick="requestNotificationPermission()" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 flex items-center gap-1.5 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 px-3 py-1.5 rounded-lg transition-all">
            @include('partials.icon', ['name' => 'bell', 'class' => 'w-3.5 h-3.5'])
            <span>Aktifkan Pengingat</span>
        </button>
    </div>
</div>

<!-- Form Checklist 7 Kebiasaan Baik (Livewire) -->
<livewire:journal-form :journal="$journal" :user="$user" />

<!-- Mini Habit Heatmap (Konsistensi 7 Hari Terakhir) -->
@php
    $heatmapHabits = [
        ['key' => 'bangun_pagi',   'icon' => 'sunrise', 'label' => 'Bangun Pagi'],
        ['key' => 'beribadah',     'icon' => 'hand-heart', 'label' => 'Ibadah'],
        ['key' => 'berolahraga',   'icon' => 'footprints', 'label' => 'Olahraga'],
        ['key' => 'makan_sehat',   'icon' => 'salad', 'label' => 'Makan Sehat'],
        ['key' => 'gemar_belajar', 'icon' => 'book-open', 'label' => 'Belajar'],
        ['key' => 'bermasyarakat', 'icon' => 'handshake', 'label' => 'Sosial'],
        ['key' => 'tidur_cepat',   'icon' => 'moon-star', 'label' => 'Tidur'],
    ];
    // Balik urutan: kiri = hari terlama, kanan = hari terbaru
    $heatmapJournals = $recentJournals->reverse()->values();
@endphp
<div class="bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-base flex items-center gap-2">
                @include('partials.icon', ['name' => 'flame', 'class' => 'w-4 h-4 text-amber-500'])
                <span>Konsistensi 7 Hari Terakhir</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Visualisasi kebiasaan harianmu — hijau = selesai
                @include('partials.icon', ['name' => 'check', 'class' => 'w-3 h-3 inline-block align-[-1px] text-emerald-500'])
            </p>
        </div>
        <div class="flex items-center gap-3 text-[10px] text-slate-400 dark:text-slate-500 font-medium">
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-emerald-400 inline-block"></span> Selesai</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-slate-200 dark:bg-slate-600 inline-block"></span> Belum</span>
        </div>
    </div>
    <div class="overflow-x-auto pb-1">
        <table class="w-full min-w-[360px]">
            <thead>
                <tr>
                    <th class="text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 py-1 pr-3 w-24"></th>
                    @foreach($heatmapJournals as $hj)
                        <th class="text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 pb-2 px-0.5 min-w-[34px]">
                            {{ \Carbon\Carbon::parse($hj->date)->translatedFormat('D') }}<br>
                            <span class="font-normal text-slate-300 dark:text-slate-600">{{ \Carbon\Carbon::parse($hj->date)->format('d') }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-700/60">
                @foreach($heatmapHabits as $habit)
                    <tr class="group">
                        <td class="py-1.5 pr-3">
                            <div class="flex items-center gap-1.5">
                                <span class="leading-none">
                                    @include('partials.icon', ['name' => $habit['icon'], 'class' => 'w-4 h-4 text-slate-400 dark:text-slate-500'])
                                </span>
                                <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-200 transition-colors">{{ $habit['label'] }}</span>
                            </div>
                        </td>
                        @foreach($heatmapJournals as $hj)
                            @php $done = $hj->{$habit['key']}; @endphp
                            <td class="text-center px-0.5 py-1.5">
                                <div class="mx-auto w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold transition-all duration-200 hover:scale-110
                                    {{ $done
                                        ? 'bg-emerald-400 text-white shadow-sm shadow-emerald-400/40'
                                        : 'bg-slate-100 dark:bg-slate-700/60 text-slate-300 dark:text-slate-500' }}"
                                    title="{{ \Carbon\Carbon::parse($hj->date)->translatedFormat('d M') }}: {{ $done ? 'Selesai ✓' : 'Belum' }}">
                                    {{ $done ? '✓' : '·' }}
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Riwayat Pengisian 7 Hari Terakhir -->
<div class="bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-3xl p-6 shadow-sm border border-slate-200/80 mt-6">
    <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
        <div>
            <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-base flex items-center gap-2">
                @include('partials.icon', ['name' => 'history', 'class' => 'w-4 h-4 text-emerald-500'])
                <span>Riwayat Pengisian Kebiasaan Selesai</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar pencapaian jurnal harian Anda selama 7 hari terakhir.</p>
        </div>
        <a href="{{ route('history') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
            <span>Lihat Kalender</span>
            @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-3 h-3'])
        </a>
    </div>

    <div class="space-y-3">
        @forelse($recentJournals as $rj)
            @php
                $formattedDate = \Carbon\Carbon::parse($rj->date)->translatedFormat('l, d F Y');
                $isTodayItem = ($rj->date->toDateString() === $today->toDateString());
            @endphp
            <div class="p-4 rounded-2xl border flex items-center justify-between gap-4 transition-all {{ $rj->is_fully_completed ? 'bg-emerald-50/60 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30' : ($rj->completed_count > 0 ? 'bg-amber-50/60 border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/30' : 'bg-slate-50 border-slate-200/60 dark:bg-slate-700/40 dark:border-slate-600/60') }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-xs {{ $rj->is_fully_completed ? 'bg-emerald-500 text-white' : ($rj->completed_count > 0 ? 'bg-amber-400 text-amber-950' : 'bg-slate-200 dark:bg-slate-600 text-slate-500 dark:text-slate-400') }}">
                        @if($rj->is_fully_completed)
                            @include('partials.icon', ['name' => 'circle-check', 'class' => 'w-5 h-5'])
                        @elseif($rj->completed_count > 0)
                            @include('partials.icon', ['name' => 'clock-3', 'class' => 'w-5 h-5'])
                        @else
                            @include('partials.icon', ['name' => 'circle-x', 'class' => 'w-5 h-5'])
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ $formattedDate }}</h4>
                            @if($isTodayItem)
                                <span class="text-[9px] font-extrabold bg-emerald-600 text-white px-2 py-0.5 rounded-full uppercase">Hari Ini</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Status: <span class="font-extrabold {{ $rj->is_fully_completed ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300' }}">{{ $rj->completed_count }}/7 Kebiasaan Terisi</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs hidden sm:inline-flex items-center gap-1.5">
                        @if($rj->bangun_pagi)@include('partials.icon', ['name' => 'sunrise', 'class' => 'w-4 h-4 text-amber-500'])@endif
                        @if($rj->beribadah)@include('partials.icon', ['name' => 'hand-heart', 'class' => 'w-4 h-4 text-emerald-500'])@endif
                        @if($rj->berolahraga)@include('partials.icon', ['name' => 'footprints', 'class' => 'w-4 h-4 text-teal-500'])@endif
                        @if($rj->makan_sehat)@include('partials.icon', ['name' => 'salad', 'class' => 'w-4 h-4 text-teal-500'])@endif
                        @if($rj->gemar_belajar)@include('partials.icon', ['name' => 'book-open', 'class' => 'w-4 h-4 text-emerald-500'])@endif
                        @if($rj->bermasyarakat)@include('partials.icon', ['name' => 'handshake', 'class' => 'w-4 h-4 text-teal-500'])@endif
                        @if($rj->tidur_cepat)@include('partials.icon', ['name' => 'moon-star', 'class' => 'w-4 h-4 text-emerald-500'])@endif
                    </span>
                    <button type="button" onclick="showDateDetail('{{ $rj->date->toDateString() }}')" class="btn-detail-slide">
                        <span>Rincian</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center py-6 text-slate-400 dark:text-slate-500 text-xs">Belum ada riwayat pengisian.</div>
        @endforelse
    </div>
</div>

<!-- Modal Detail Jurnal Tanggal (Ringkasan Riwayat) -->
<div id="detail-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 dark:border-slate-700 rounded-3xl p-6 max-w-lg w-full shadow-2xl relative animate-fade-in">
        <button onclick="toggleModal('detail-modal')" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 flex items-center justify-center">
            @include('partials.icon', ['name' => 'x', 'class' => 'w-4 h-4'])
        </button>

        <div class="mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white" id="modal-date-title">Detail Jurnal</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400" id="modal-date-subtitle">Rincian pelaksanaan kebiasaan harian</p>
        </div>

        <div id="modal-content-body" class="space-y-3 max-h-96 overflow-y-auto pr-1">
            <!-- Dynamic content loaded via JavaScript -->
        </div>

        <div class="mt-6 border-t border-slate-100 dark:border-slate-700 pt-4 flex justify-end">
            <button onclick="toggleModal('detail-modal')" class="btn-modal-close">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Lencana & Achievements -->
<div id="badges-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 dark:border-slate-700 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl relative animate-fade-in">
        <button onclick="toggleModal('badges-modal')" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 flex items-center justify-center">
            @include('partials.icon', ['name' => 'x', 'class' => 'w-4 h-4'])
        </button>

        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-500/15 text-amber-500 dark:text-amber-400 flex items-center justify-center mx-auto mb-2 shadow-inner">
                @include('partials.icon', ['name' => 'trophy', 'class' => 'w-8 h-8'])
            </div>
            <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">Lencana & Prestasi</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kumpulkan seluruh lencana dengan mempertahankan streak harian!</p>
        </div>

        <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
            @foreach($badges as $badge)
                <div class="p-3.5 rounded-2xl border flex items-center gap-4 transition-all {{ $badge['unlocked'] ? 'bg-emerald-50/60 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30' : 'bg-slate-50 border-slate-200/60 dark:bg-slate-700/40 dark:border-slate-600/60 opacity-60' }}">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-sm {{ $badge['unlocked'] ? 'bg-white dark:bg-slate-700' : 'bg-slate-200 dark:bg-slate-600 filter grayscale' }}">
                        @include('partials.icon', ['name' => $badge['icon'], 'class' => 'w-6 h-6 text-amber-500'])
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-xs text-slate-800 dark:text-slate-100">{{ $badge['name'] }}</h4>
                            @if($badge['unlocked'])
                                <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-500/20 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                    Terbuka
                                    @include('partials.icon', ['name' => 'party-popper', 'class' => 'w-3 h-3'])
                                </span>
                            @else
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 inline-flex items-center gap-1">
                                    Terkunci
                                    @include('partials.icon', ['name' => 'lock', 'class' => 'w-3 h-3'])
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $badge['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <button onclick="toggleModal('badges-modal')" class="btn-modal-close w-full">
            Tutup
        </button>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/apps/login-react.tsx')
<script>
    function setPresetJam(type, time) {
        const input = document.getElementById(type === 'bangun' ? 'bangun-pagi-time' : 'input-tidur-note');
        if (!input) return;
        
        // Konversi format AM/PM ke 24 jam jika diperlukan
        let convertedTime = time;
        if (time.includes('AM') || time.includes('PM')) {
            convertedTime = convertTo24Hour(time);
        }
        
        input.value = type === 'bangun' ? convertedTime : time;
        updateTimeStatusBadges();
        autoSaveJournal();
    }

    // Fungsi helper untuk konversi 12-hour ke 24-hour format
    function convertTo24Hour(time12h) {
        const [timeStr, modifier] = time12h.split(' ');
        let [hours, minutes] = timeStr.split(':');
        
        if (hours === '12') {
            hours = '00';
        }
        
        if (modifier === 'PM') {
            hours = parseInt(hours, 10) + 12;
        }
        
        return `${String(hours).padStart(2, '0')}:${minutes}`;
    }

    document.addEventListener('DOMContentLoaded', () => {

        updateTimeStatusBadges();

        // --- Daily Quote Init ---
        displayQuote(currentQuoteIdx);

        // --- Mood Tracker: restore saved mood from localStorage ---
        const todayKey = new Date().toDateString();
        const savedMood = localStorage.getItem('mood_' + todayKey);
        if (savedMood) {
            const savedBtn = document.querySelector(`[data-mood="${savedMood}"]`);
            if (savedBtn) selectMood(savedMood, savedBtn);
        }

        // --- Interactive Checkbox Micro-Animations ---
        document.querySelectorAll('#journal-form input[type="checkbox"]').forEach(input => {
            input.addEventListener('change', (e) => {
                triggerCheckAnimation(e.target);
            });
        });
    });

    function triggerCheckAnimation(input) {
        if (!input || !input.checked) return;

        // Emerald pulse highlight on habit card
        const card = input.closest('.journal-habit-card');
        if (card) {
            card.classList.remove('card-check-highlight');
            void card.offsetWidth;
            card.classList.add('card-check-highlight');
            setTimeout(() => card.classList.remove('card-check-highlight'), 750);
        }

    }

    // =============================================
    // DAILY QUOTE SYSTEM
    // =============================================
    const dailyQuotes = [
        { text: "Disiplin adalah jembatan antara tujuan dan pencapaian.", author: "Jim Rohn" },
        { text: "Keberhasilan bukan kebetulan. Ia adalah kerja keras, ketekunan, belajar, berkorban dan yang terpenting, mencintai apa yang kamu lakukan.", author: "Pelé" },
        { text: "Setiap hari adalah kesempatan baru untuk menjadi versi terbaik dari dirimu.", author: "" },
        { text: "Mulailah dari mana kamu berada. Gunakan apa yang kamu miliki. Lakukan apa yang kamu bisa.", author: "Arthur Ashe" },
        { text: "Orang-orang sukses melakukan setiap hari apa yang orang biasa hanya mau lakukan sesekali.", author: "" },
        { text: "Kebiasaan kecil yang dilakukan setiap hari lebih kuat dari keputusan besar yang jarang dilakukan.", author: "James Clear" },
        { text: "Kamu tidak harus hebat untuk memulai, tapi kamu harus memulai untuk menjadi hebat.", author: "Zig Ziglar" },
        { text: "Keunggulan bukan suatu tindakan, melainkan sebuah kebiasaan.", author: "Aristoteles" },
        { text: "Jangan biarkan apa yang tidak bisa kamu lakukan menghambat apa yang bisa kamu lakukan.", author: "John Wooden" },
        { text: "Sukses adalah jumlah dari usaha-usaha kecil yang diulang setiap hari.", author: "Robert Collier" },
        { text: "Bangun sekarang, atau selamanya menyesal. Setiap momen yang hilang tidak akan pernah kembali.", author: "" },
        { text: "Kemenangan adalah milik mereka yang paling gigih.", author: "Napoleon Bonaparte" },
        { text: "Perbedaan antara yang berhasil dan yang gagal adalah kebiasaan yang konsisten.", author: "" },
        { text: "Jangan tunda hingga esok apa yang bisa kamu lakukan hari ini.", author: "Benjamin Franklin" },
    ];

    let currentQuoteIdx = (new Date().getDate() + new Date().getMonth()) % dailyQuotes.length;

    function displayQuote(idx) {
        const q = dailyQuotes[idx];
        const textEl = document.getElementById('daily-quote-text');
        const authorEl = document.getElementById('daily-quote-author');
        if (!textEl) return;
        textEl.style.opacity = '0';
        textEl.style.transform = 'translateY(8px)';
        setTimeout(() => {
            textEl.textContent = '\u201c' + q.text + '\u201d';
            if (authorEl) authorEl.textContent = q.author ? '\u2014 ' + q.author : '';
            textEl.style.opacity = '1';
            textEl.style.transform = 'translateY(0)';
        }, 220);
    }

    function nextQuote() {
        currentQuoteIdx = (currentQuoteIdx + 1) % dailyQuotes.length;
        displayQuote(currentQuoteIdx);
    }

    // =============================================
    // MOOD TRACKER
    // =============================================
    const moodLabels = {
        'excellent': 'Sangat Baik',
        'good':      'Baik',
        'neutral':   'Biasa saja',
        'sad':       'Kurang baik',
        'tired':     'Lelah',
    };

    // State terpilih mengikuti DESAIN.md Â§5.7: bg primary-50 + border primary-500
    const MOOD_SELECTED_CLASSES = [
        'border-emerald-500', 'bg-emerald-50',
        'dark:border-emerald-500/50', 'dark:bg-emerald-500/15',
        'scale-105',
    ];

    function selectMood(value, btn) {
        // Reset semua tombol mood
        document.querySelectorAll('.mood-btn').forEach(b => {
            b.classList.remove(...MOOD_SELECTED_CLASSES);
            b.classList.add('border-transparent');
        });
        // Highlight tombol yang dipilih
        btn.classList.remove('border-transparent');
        btn.classList.add(...MOOD_SELECTED_CLASSES);
        // Update status teks
        const statusEl = document.getElementById('mood-save-status');
        if (statusEl) statusEl.innerHTML = '@include('partials.icon', ['name' => 'circle-check', 'class' => 'w-3 h-3 inline-block align-[-1px] mr-1 text-emerald-500'])Suasana hati dicatat: ' + (moodLabels[value] || value);
        // Simpan ke localStorage hari ini
        localStorage.setItem('mood_' + new Date().toDateString(), value);
    }

    function onBangunPagiChange() {
        updateTimeStatusBadges();
        autoSaveJournal();
    }

    function updateTimeStatusBadges() {
        const bangunBox = document.getElementById('checkbox-bangun-pagi');
        const bangunTime = document.getElementById('bangun-pagi-time');
        const bangunBadge = document.getElementById('bangun-status-badge');
        if (bangunBadge) {
            const v = bangunTime ? bangunTime.value : '';
            const checked = !!(bangunBox && bangunBox.checked);
            const ok = checked && v >= '03:00' && v <= '10:00';
            setStatusBadge(bangunBadge, ok, checked);
        }

        const tidurBox = document.getElementById('checkbox-tidur-cepat');
        const tidurNote = document.getElementById('input-tidur-note');
        const tidurBadge = document.getElementById('tidur-status-badge');
        if (tidurBadge) {
            const raw = tidurNote ? tidurNote.value : '';
            const m = raw.match(/^(\d{2}):(\d{2})/);
            const v = m ? m[1] + ':' + m[2] : '';
            const checked = !!(tidurBox && tidurBox.checked);
            const ok = checked && v >= '20:00' && v <= '23:59';
            setStatusBadge(tidurBadge, ok, checked);
        }
    }

    function setStatusBadge(el, ok, checked) {
        el.classList.remove(
            'bg-emerald-100', 'text-emerald-700', 'dark:bg-emerald-500/20', 'dark:text-emerald-300',
            'bg-rose-100', 'text-rose-700', 'dark:bg-rose-500/20', 'dark:text-rose-300',
            'bg-amber-100', 'text-amber-700', 'dark:bg-amber-500/20', 'dark:text-amber-300'
        );
        if (ok) {
            el.classList.add('bg-emerald-100', 'text-emerald-700', 'dark:bg-emerald-500/20', 'dark:text-emerald-300');
            el.textContent = 'Sesuai';
        } else if (checked) {
            el.classList.add('bg-rose-100', 'text-rose-700', 'dark:bg-rose-500/20', 'dark:text-rose-300');
            el.textContent = 'Tidak Sesuai';
        } else {
            el.classList.add('bg-amber-100', 'text-amber-700', 'dark:bg-amber-500/20', 'dark:text-amber-300');
            el.textContent = 'Belum';
        }
    }

    function autoFillTidurCepat() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const timeString = hours + ':' + minutes + ' WIB';

        const inputNote = document.getElementById('input-tidur-note');
        const checkbox = document.getElementById('checkbox-tidur-cepat');

        if (inputNote) {
            inputNote.value = timeString;
        }
        if (checkbox) {
            checkbox.checked = true;
        }
        updateTimeStatusBadges();
        autoSaveJournal();
    }

    function onTidurCepatChange() {
        updateTimeStatusBadges();
        autoSaveJournal();
    }

    function unlockForm() {
        alert('Kunci jurnal dibuka. Anda dapat mengedit atau merevisi kembali isi jurnal.');
    }

    function showDateDetail(date) {
        const modalBody = document.getElementById('modal-content-body');
        const titleElem = document.getElementById('modal-date-title');
        
        if (!modalBody || !titleElem) return;

        modalBody.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm">@include('partials.icon', ['name' => 'loader-circle', 'class' => 'w-8 h-8 mx-auto mb-2 animate-spin'])<p>Memuat data jurnal...</p></div>';
        toggleModal('detail-modal');

        fetch("{{ url('/api/journal') }}/" + date)
        .then(res => res.json())
        .then(data => {
            if (!data.found) {
                titleElem.innerText = "Jurnal Tanggal " + date;
                modalBody.innerHTML = `
                    <div class="text-center py-6">
                        <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-2">
                            @include('partials.icon', ['name' => 'calendar-x-2', 'class' => 'w-6 h-6'])
                        </div>
                        <p class="font-bold text-slate-700 dark:text-slate-200 text-sm">Belum Ada Catatan</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Siswa tidak mengisi jurnal pada tanggal ini.</p>
                    </div>
                `;
                return;
            }

            titleElem.innerText = "Jurnal " + data.formatted_date;
            const j = data.journal;

            const habitList = [
                { name: 'Bangun Pagi', status: j.bangun_pagi, icon: '@include('partials.icon', ['name' => 'sunrise', 'class' => 'w-6 h-6'])', note: j.bangun_pagi_time ? 'Jam bangun: ' + j.bangun_pagi_time.slice(0, 5) : null },
                { name: 'Beribadah', status: j.beribadah, icon: '@include('partials.icon', ['name' => 'hand-heart', 'class' => 'w-6 h-6'])', note: formatPrayerDetails(j.ibadah_details) },
                { name: 'Berolahraga', status: j.berolahraga, icon: '@include('partials.icon', ['name' => 'footprints', 'class' => 'w-6 h-6'])', note: j.olahraga_note },
                { name: 'Makan Sehat', status: j.makan_sehat, icon: '@include('partials.icon', ['name' => 'salad', 'class' => 'w-6 h-6'])', note: j.makan_note },
                { name: 'Gemar Belajar', status: j.gemar_belajar, icon: '@include('partials.icon', ['name' => 'book-open', 'class' => 'w-6 h-6'])', note: j.belajar_note },
                { name: 'Bermasyarakat', status: j.bermasyarakat, icon: '@include('partials.icon', ['name' => 'handshake', 'class' => 'w-6 h-6'])', note: j.masyarakat_note },
                { name: 'Tidur Cepat', status: j.tidur_cepat, icon: '@include('partials.icon', ['name' => 'moon-star', 'class' => 'w-6 h-6'])', note: j.tidur_note },
            ];

            let html = '';
            habitList.forEach((h, index) => {
                html += `
                    <div class="p-3.5 rounded-xl border flex items-start gap-3 ${h.status ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30' : 'bg-slate-50 border-slate-200/60 dark:bg-slate-700/40 dark:border-slate-600/60'}">
                        <div class="text-xl">${h.icon}</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-xs text-slate-800 dark:text-slate-100">${index+1}. ${h.name}</h4>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md inline-flex items-center gap-1 ${h.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-slate-200 text-slate-500 dark:bg-slate-600 dark:text-slate-400'}">
                                    ${h.status ? '@include('partials.icon', ['name' => 'circle-check', 'class' => 'w-3 h-3'])Selesai' : '@include('partials.icon', ['name' => 'circle-x', 'class' => 'w-3 h-3'])Belum'}
                                </span>
                            </div>
                            ${h.note ? `<p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 italic bg-white/60 dark:bg-slate-700/60 p-2 rounded-lg border border-slate-100 dark:border-slate-600">"${h.note}"</p>` : ''}
                        </div>
                    </div>
                `;
            });

            modalBody.innerHTML = html;
        })
        .catch(err => {
            modalBody.innerHTML = '<p class="text-xs text-rose-500">Gagal mengambil data jurnal.</p>';
        });
    }

    function formatPrayerDetails(details) {
        if (!details) return null;
        if (details.subuh !== undefined) {
            const list = [];
            if (details.subuh) list.push('Subuh');
            if (details.dzuhur) list.push('Dzuhur');
            if (details.ashar) list.push('Ashar');
            if (details.maghrib) list.push('Maghrib');
            if (details.isya) list.push('Isya');
            return 'Sholat terisi: ' + (list.length > 0 ? list.join(', ') : 'Belum ada');
        }
        return null;
    }

    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    let _pressAudio = null;
    function getPressAudio() {
        if (!_pressAudio) {
            _pressAudio = new Audio("{{ asset('sounds/press.mp3') }}");
            _pressAudio.preload = 'auto';
        }
        return _pressAudio;
    }

    function playPress() {
        try {
            const a = getPressAudio();
            a.currentTime = 0;
            a.play().catch(() => {});
        } catch (e) {}
    }

    function playPressSound(cb) {
        if (!cb.checked) return;
        try {
            const a = getPressAudio();
            a.currentTime = 0;
            a.play().catch(() => {});
        } catch (e) {}
    }

    let _cameraAudio = null;
    function getCameraAudio() {
        if (!_cameraAudio) {
            _cameraAudio = new Audio("{{ asset('sounds/camera.mp3') }}");
            _cameraAudio.preload = 'auto';
        }
        return _cameraAudio;
    }

    function playCameraSound(cb) {
        if (!cb.checked) return;
        try {
            const a = getCameraAudio();
            a.currentTime = 0;
            a.play().catch(() => {});
        } catch (e) {}
    }

    let _sholatAudio = null;
    function getSholatAudio() {
        if (!_sholatAudio) {
            _sholatAudio = new Audio("{{ asset('sounds/button-pressed.mp3') }}");
            _sholatAudio.preload = 'auto';
        }
        return _sholatAudio;
    }

    function playSholatSound(cb) {
        if (!cb.checked) return;
        try {
            const a = getSholatAudio();
            a.currentTime = 0;
            a.play().catch(() => {});
        } catch (e) {}
    }

    (function () {
        try {
            getCameraAudio();
            getSholatAudio();
            getPressAudio();
        } catch (e) {}
    })();

    function updatePrayerLogic() {
        const checkboxes = document.querySelectorAll('.prayer-checkbox');
        let allChecked = true;
        let count = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                count++;
            } else {
                allChecked = false;
            }
        });

        const progressText = document.getElementById('sholat-progress-text');
        if (progressText) {
            progressText.innerText = count + '/' + checkboxes.length + ' Sholat';
        }

        const masterIcon = document.getElementById('master-ibadah-icon');
        const statusBadge = document.getElementById('ibadah-status-badge');

        if (allChecked) {
            masterIcon.classList.remove('bg-slate-200', 'text-slate-400', 'dark:bg-slate-600');
            masterIcon.classList.add('bg-emerald-500', 'text-white');
            if (statusBadge) {
                statusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300';
                statusBadge.innerHTML = 'Sudah Lengkap @include('partials.icon', ['name' => 'circle-check', 'class' => 'w-3 h-3 inline-block align-[-1px]'])';
            }
        } else {
            masterIcon.classList.remove('bg-emerald-500', 'text-white');
            masterIcon.classList.add('bg-slate-200', 'text-slate-400', 'dark:bg-slate-600');
            if (statusBadge) {
                statusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-md bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300';
                statusBadge.innerHTML = 'Belum Lengkap @include('partials.icon', ['name' => 'clock-3', 'class' => 'w-3 h-3 inline-block align-[-1px]'])';
            }
        }

        autoSaveJournal();
    }

    // =============================================
    // SIMPAN JURNAL VIA LIVEWIRE + ANTRIAN OFFLINE
    // =============================================
    let __journalSaveTimer = null;

    function serializeJournalForm() {
        const form = document.getElementById('journal-form');
        if (!form) return {};
        const data = {};
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.name) return;
            if (el.type === 'checkbox') {
                data[el.name] = el.checked;
            } else if (el.type === 'radio') {
                if (el.checked) data[el.name] = el.value;
            } else {
                data[el.name] = el.value;
            }
        });
        return data;
    }

    function getOfflineQueue() {
        try { return JSON.parse(localStorage.getItem('jurnal_offline_form') || 'null'); } catch (e) { return null; }
    }

    function setOfflineQueue(data) {
        try { localStorage.setItem('jurnal_offline_form', JSON.stringify(data)); } catch (e) {}
    }

    function clearOfflineQueue() {
        try { localStorage.removeItem('jurnal_offline_form'); } catch (e) {}
    }

    function showOfflineBanner() {
        let banner = document.getElementById('offline-save-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'offline-save-banner';
            banner.style.cssText = 'position:fixed;bottom:88px;left:50%;transform:translateX(-50%);z-index:999;background:#0f172a;color:#fff;font-size:11px;font-weight:700;padding:9px 16px;border-radius:9999px;box-shadow:0 8px 24px rgba(15,23,42,.35);display:flex;align-items:center;gap:8px;max-width:92vw;';
            banner.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#fbbf24;flex-shrink:0"></span><span>Tersimpan offline - akan disinkronkan otomatis</span>';
            document.body.appendChild(banner);
        }
        banner.style.display = 'flex';
    }

    function hideOfflineBanner() {
        const banner = document.getElementById('offline-save-banner');
        if (banner) banner.style.display = 'none';
    }

    function getJournalComponent() {
        return (window.Livewire && Livewire.first('journal-form')) || null;
    }

    function updateProgressUI(completedCount, streak) {
        const countElem = document.getElementById('progress-count');
        const fillElem = document.getElementById('progress-bar-fill');
        const textElem = document.getElementById('progress-percentage-text');
        const streakElem = document.getElementById('streak-counter');

        if (countElem) countElem.innerText = completedCount;
        if (streakElem && streak !== undefined) streakElem.innerText = streak + ' Hari';

        const percent = Math.round((completedCount / 7) * 100);
        if (fillElem) fillElem.style.width = percent + '%';
        if (textElem) textElem.innerText = percent + '% Selesai';
    }

    function autoSaveJournal() {
        clearTimeout(__journalSaveTimer);
        __journalSaveTimer = setTimeout(function () {
            const data = serializeJournalForm();
            if (!navigator.onLine) {
                setOfflineQueue(data);
                showOfflineBanner();
                return;
            }
            const comp = getJournalComponent();
            if (!comp) return;
            comp.call('saveFromClient', data)
                .then(function (res) {
                    if (res && res.success) {
                        clearOfflineQueue();
                        hideOfflineBanner();
                        updateProgressUI(res.completed_count, res.streak);
                    } else if (res && res.is_locked) {
                        alert(res.message || 'Jurnal telah terkunci.');
                    }
                })
                .catch(function () {
                    setOfflineQueue(data);
                    showOfflineBanner();
                });
        }, 500);
    }

    function flushOfflineQueue() {
        if (!navigator.onLine) return;
        const queued = getOfflineQueue();
        if (!queued) return;
        const comp = getJournalComponent();
        if (!comp) return;
        comp.call('saveFromClient', queued)
            .then(function (res) {
                if (res && res.success) {
                    clearOfflineQueue();
                    hideOfflineBanner();
                    updateProgressUI(res.completed_count, res.streak);
                }
            })
            .catch(function () {});
    }

    function submitJournalForm() {
        playPress();
        const data = serializeJournalForm();
        if (!navigator.onLine) {
            setOfflineQueue(data);
            showOfflineBanner();
            alert('Kamu sedang offline. Jurnal tersimpan di perangkat ini dan akan disinkronkan otomatis saat online.');
            return;
        }
        const comp = getJournalComponent();
        if (!comp) return;
        comp.call('saveAndLock', data);
    }

    window.addEventListener('online', flushOfflineQueue);
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            if (navigator.onLine) flushOfflineQueue();
            if (getOfflineQueue()) showOfflineBanner();
        }, 1500);
    });

    function requestNotificationPermission() {
        if ('Notification' in window) {
            Notification.requestPermission().then(permission => {
if (permission === 'granted') {
                alert('Notifikasi berhasil diaktifkan! Kami akan mengingatkan Anda untuk mengisi jurnal di malam hari.');
                    new Notification("Pengingat Jurnal", {
                        body: "Pengingat Jurnal Malam aktif! Jangan lupa isi checklist sebelum tidur.",
                        icon: "https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                    });
                } else {
                    alert('Izin notifikasi ditolak. Anda dapat mengaktifkannya di pengaturan peramban Anda.');
                }
            });
        } else {
            alert('Peramban Anda tidak mendukung Notifikasi Browser.');
        }
    }
</script>
<script src="{{ asset('js/rive.min.js') }}"></script>
<script>
    (() => {
        'use strict';

        const duoCanvas = document.getElementById('duo-lingo-canvas');

        if (!duoCanvas || typeof rive === 'undefined') {
            return;
        }

        if (rive.RuntimeLoader) {
            rive.RuntimeLoader.setWasmUrl(@json(asset('js/rive.wasm')));
        }

        let duoInstance = null;
        let duoTriggerCycle = null;
        let duoTriggerTimer = null;
        let duoTriggerIndex = 0;

        function stopDuoTriggerLoop() {
            if (duoTriggerTimer !== null) {
                clearTimeout(duoTriggerTimer);
                clearInterval(duoTriggerTimer);
                duoTriggerTimer = null;
            }
            duoTriggerCycle = null;
        }

        function startDuoTriggerLoop() {
            stopDuoTriggerLoop();

            const check = () => {
                const sm = duoInstance?.animator?.stateMachines?.[0];

                if (!sm) {
                    return 'retry';
                }

                const inputs = sm?.inputs ?? [];

                if (!inputs.length) {
                    return 'done';
                }

                duoTriggerCycle = inputs.filter(
                    (input) => typeof input.fire === 'function'
                );

                return duoTriggerCycle.length > 0 ? 'start' : 'done';
            };

            const status = check();

            if (status === 'retry') {
                if (duoTriggerTimer === null) {
                    duoTriggerTimer = setTimeout(() => {
                        duoTriggerTimer = null;
                        startDuoTriggerLoop();
                    }, 1000);
                }
                return;
            }

            if (status !== 'start') {
                return;
            }

            if (duoTriggerTimer !== null) {
                clearTimeout(duoTriggerTimer);
                duoTriggerTimer = null;
            }

            const fire = () => {
                if (!duoTriggerCycle || !duoTriggerCycle.length) {
                    return;
                }

                duoTriggerCycle[duoTriggerIndex % duoTriggerCycle.length].fire();
                duoTriggerIndex++;
            };

            fire();
            duoTriggerTimer = setInterval(fire, 3000);
        }

        function resizeDuoCanvas() {
            if (
                duoInstance &&
                duoCanvas.offsetWidth > 0 &&
                duoCanvas.offsetHeight > 0 &&
                typeof duoInstance.resizeDrawingSurfaceToCanvas === 'function'
            ) {
                duoInstance.resizeDrawingSurfaceToCanvas();
            }
        }

        duoInstance = new rive.Rive({
            src: @json(asset('rive/shake-it-duo.riv')),
            canvas: duoCanvas,
            autoplay: true,
            layout: new rive.Layout({
                fit: rive.Fit.Contain,
                alignment: rive.Alignment.Center,
            }),
            onLoad: () => {
                resizeDuoCanvas();

                const contents = duoInstance.contents;
                const artboards = contents?.artboards ?? [];
                const target = artboards.find(
                    (artboard) => artboard.stateMachines.length > 0
                );

                if (target && typeof duoInstance.play === 'function') {
                    duoInstance.play(target.stateMachines[0].name);
                }

                setTimeout(() => {
                    if (duoInstance && duoInstance.loaded) {
                        startDuoTriggerLoop();
                    }
                }, 500);
            },
            onLoadError: (error) => {
                console.error('Duo Lingo gagal:', error);
            },
        });

        duoCanvas.addEventListener('click', () => {
            if (duoTriggerCycle && duoTriggerCycle.length) {
                duoTriggerCycle[duoTriggerIndex % duoTriggerCycle.length].fire();
                duoTriggerIndex++;
            }
        });

        window.addEventListener('resize', resizeDuoCanvas);
    })();
</script>
@endpush
