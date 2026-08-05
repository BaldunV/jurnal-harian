@extends('layouts.app')

@section('title', 'Statistik & Analisis Kebiasaan')

@section('content')

<!-- Header Card -->
<div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-chart-pie text-emerald-500"></i>
            <span>Statistik Kebiasaan Baik</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1">Analisis persentase kepatuhan dan kebiasaan yang perlu ditingkatkan.</p>
    </div>

    <!-- Period Filter Selector -->
    <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-xl text-xs font-bold">
        <a href="{{ route('statistics', ['period' => 7]) }}" class="px-4 py-2 rounded-lg transition-all {{ $days == 7 ? 'bg-white text-emerald-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            7 Hari Terakhir
        </a>
        <a href="{{ route('statistics', ['period' => 30]) }}" class="px-4 py-2 rounded-lg transition-all {{ $days == 30 ? 'bg-white text-emerald-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            30 Hari Terakhir
        </a>
    </div>
</div>

<!-- Highlight Alert: Most Skipped Habit -->
@if($mostSkipped)
    <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-3xl p-6 text-white shadow-lg shadow-amber-500/20 flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl shrink-0 shadow-inner">
            ⚠️
        </div>
        <div>
            <div class="text-[11px] uppercase font-bold tracking-wider text-amber-100">Fokus Perbaikan Kebiasaan</div>
            <h3 class="text-lg font-extrabold mt-0.5">
                Kebiasaan Paling Sering Terlewat: <span class="underline decoration-2 underline-offset-4 font-black">{{ $mostSkipped['name'] }}</span>
            </h3>
            <p class="text-xs text-amber-100 mt-1 font-medium">
                Berhasil dilaksanakan {{ $mostSkipped['count'] }} dari {{ $totalDaysRecorded }} hari ({{ $mostSkipped['percentage'] }}%). Yuk, berikan perhatian lebih untuk kebiasaan ini!
            </p>
        </div>
    </div>
@endif

<!-- Habits Statistics Progress Grid -->
<div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80">
    <h3 class="font-extrabold text-slate-800 text-base mb-4 flex items-center gap-2">
        <i class="fa-solid fa-chart-bar text-emerald-500"></i>
        <span>Persentase Kepatuhan Per Kebiasaan ({{ $days }} Hari)</span>
    </h3>

    <div class="space-y-5">
        @foreach($stats as $key => $item)
            @php
                $barColor = 'bg-emerald-500';
                if ($item['percentage'] < 50) {
                    $barColor = 'bg-rose-500';
                } elseif ($item['percentage'] < 80) {
                    $barColor = 'bg-amber-400';
                }
            @endphp
            <div>
                <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1.5">
                    <span class="flex items-center gap-2">
                        <span class="text-lg">{{ $item['icon'] }}</span>
                        <span>{{ $item['name'] }}</span>
                    </span>
                    <span class="text-slate-800 font-extrabold">
                        {{ $item['count'] }}/{{ $totalDaysRecorded }} Hari ({{ $item['percentage'] }}%)
                    </span>
                </div>

                <div class="w-full h-3.5 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200/60">
                    <div class="h-full {{ $barColor }} rounded-full transition-all duration-700 shadow-xs" style="width: {{ $item['percentage'] }}%;"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Tips & Recommendations -->
<div class="bg-emerald-50/80 rounded-3xl p-6 border border-emerald-200 text-slate-800">
    <h3 class="font-extrabold text-sm text-emerald-900 flex items-center gap-2 mb-2">
        <i class="fa-solid fa-lightbulb text-amber-500 text-base"></i>
        <span>Tips Membangun Kebiasaan Konsisten</span>
    </h3>
    <ul class="list-disc list-inside text-xs text-slate-700 space-y-1.5 leading-relaxed font-medium">
        <li>Mulai dari langkah kecil (*micro-habits*) setiap harinya.</li>
        <li>Tetapkan pengingat di HP Anda (gunakan fitur Pengingat Jurnal Malam di Dashboard).</li>
        <li>Gunakan prinsip 2 hari: Jangan membiarkan kebiasaan terputus lebih dari 1 hari berturut-turut.</li>
    </ul>
</div>

@endsection
