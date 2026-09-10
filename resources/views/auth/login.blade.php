@extends('layouts.guest')

@section('title', 'Login')

@section('auth-panel')
<div class="w-full min-h-screen md:flex overflow-x-clip bg-slate-50 dark:bg-slate-950 font-sans">

    <!-- ======== LEFT PANEL: Full-bleed Animasi (Desktop & Tablet) ======== -->
    <div class="relative hidden md:flex md:w-1/2 items-center justify-center bg-slate-50 dark:bg-slate-950 overflow-hidden select-none">

        <!-- FULL RIVE CANVAS -->
        <canvas id="snake-rive" class="absolute inset-0 z-10 w-full h-full" aria-label="Animasi"></canvas>

        <!-- Bottom Motivation Quote -->
        <div class="absolute bottom-8 left-8 right-8 z-20 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white dark:bg-slate-800/80 backdrop-blur-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-medium shadow-lg">
                @include('partials.icon', ['name' => 'sparkles', 'class' => 'w-3.5 h-3.5 text-amber-500'])
                <span>Bangun karakter unggul lewat kebiasaan baik setiap hari</span>
            </div>
        </div>
    </div>

    <!-- ======== MOBILE STAGE: Full-screen Rive statis + Bottom Sheet Login ======== -->
    <div id="mobile-stage" class="relative md:hidden h-screen overflow-hidden select-none bg-slate-50 dark:bg-slate-950">

        <!-- FULL RIVE CANVAS MOBILE (pose statis / tidak berulang) -->
        <canvas id="snake-rive-mobile" class="absolute inset-0 z-0 w-full h-full" aria-label="Animasi"></canvas>

        <!-- ======== BOTTOM SHEET: Login Panel ======== -->
        <div id="login-sheet"
            class="absolute inset-x-0 bottom-0 z-20 h-full flex flex-col rounded-t-[2rem] bg-gradient-to-b from-white via-emerald-50/90 to-teal-50/70 dark:from-slate-900 dark:via-slate-900/95 dark:to-slate-950 shadow-[0_-16px_48px_rgba(15,23,42,.18)] dark:shadow-black/50"
            style="transform: translate3d(0, calc(100% - 120px), 0); will-change: transform;">

            <!-- Peek Area: handle + CTA "Geser ke Atas" -->
            <div id="sheet-peek" class="relative shrink-0 touch-none">
                <div class="mx-auto mt-3 mb-2.5 w-12 h-1.5 rounded-full bg-slate-300/80 dark:bg-slate-600"></div>
                <div id="sheet-hint" class="flex flex-col items-center gap-0.5 pb-3 text-slate-500 dark:text-slate-400 transition-opacity duration-300">
                    <canvas id="scroll-hint-rive" style="width: 34px; height: 34px; transform: rotate(180deg);" aria-label="Petunjuk geser ke atas"></canvas>
                    <span class="text-[10px] font-bold tracking-wider uppercase opacity-90 sheet-hint-bounce">Geser ke Atas</span>
                </div>
            </div>

            <!-- Scrollable Form Content -->
            <div class="relative flex-1 min-h-0 overflow-y-auto overscroll-contain px-6 sm:px-10 pb-6">
                @include('auth.partials.login-form-body', ['variant' => 'mobile'])
            </div>
        </div>
    </div>

    <!-- ======== RIGHT PANEL: Modern Auth Form (Desktop Only) ======== -->
    <div id="desktop-panel" class="hidden md:flex md:flex-col md:justify-center md:w-1/2 bg-white dark:bg-slate-900 px-6 sm:px-10 md:px-12 lg:px-16 py-10 md:py-12">

        <div class="w-full max-w-md mx-auto">
            @include('auth.partials.login-form-body', ['variant' => 'desktop'])

            <!-- Bottom Copyright -->
            <p class="text-center text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-6">
                &copy; {{ date('Y') }} Jurnal Siswa Mandiri
            </p>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- Konfigurasi + template ikon untuk resources/js/login.js (tanpa logika inline) --}}
@php
    $loginAssetsJson = json_encode([
        'riveJs' => asset('js/rive.min.js'),
        'riveWasm' => asset('js/rive.wasm'),
        'cloudyWalk' => asset('rive/cloudy-walk.riv'),
        'scrollHint' => asset('rive/scroll-down-indicator.riv'),
    ]);
@endphp
<script type="application/json" id="login-assets">{!! $loginAssetsJson !!}</script>
<template id="login-icon-id-card">@include('partials.icon', ['name' => 'id-card', 'class' => 'w-4 h-4'])</template>
<template id="login-icon-shield-user">@include('partials.icon', ['name' => 'shield-user', 'class' => 'w-4 h-4'])</template>
<template id="login-icon-eye">@include('partials.icon', ['name' => 'eye', 'class' => 'w-4 h-4'])</template>
<template id="login-icon-eye-off">@include('partials.icon', ['name' => 'eye-off', 'class' => 'w-4 h-4'])</template>
@endpush
