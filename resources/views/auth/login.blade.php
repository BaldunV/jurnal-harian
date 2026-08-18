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

<style>
    /* Check badge pop animation */
    @keyframes portalCheckPop {
        0%   { transform: scale(.4) rotate(-20deg); opacity: 0; }
        100% { transform: scale(1) rotate(0deg); opacity: 1; }
    }
    .portal-check-pop { animation: portalCheckPop .4s cubic-bezier(.16, 1, .3, 1); }

    /* CTA "Geser ke Atas" bounce (satu-satunya animasi di layar mobile) */
    @keyframes sheetHintBounce {
        0%, 100% { transform: translateY(0); opacity: .8; }
        50%      { transform: translateY(-4px); opacity: 1; }
    }
    .sheet-hint-bounce { animation: sheetHintBounce 1.6s ease-in-out infinite; }
</style>

@push('scripts')
<script src="{{ asset('js/rive.min.js') }}"></script>
<script>
    // ==========================================
    // Rive Canvas Integration (Snake & Scroll hint)
    // ==========================================
    (function () {
        'use strict';

        function log() {
            if (typeof console !== 'undefined') {
                console.log.apply(console, ['[RiveLogin]'].concat(Array.prototype.slice.call(arguments)));
            }
        }

        // Tunggu DOM ready dan Rive library siap
        function initWhenReady() {
            if (typeof rive === 'undefined') {
                log('Rive library belum dimuat. Retry dalam 200ms...');
                return setTimeout(initWhenReady, 200);
            }

            log('Rive version:', rive.version || 'unknown');

            // Set WASM URL secara eksplisit (gunakan path absolut agar tidak ambigu)
            var wasmUrl = '{{ asset('js/rive.wasm') }}';
            log('WASM URL:', wasmUrl);
            rive.RuntimeLoader.setWasmUrl(wasmUrl);

            var coverLayout = new rive.Layout({
                fit: rive.Fit.Cover,
                alignment: rive.Alignment.center
            });

            var targets = [
                {
                    id: 'snake-rive',
                    src: '{{ asset('rive/cloudy-walk.riv') }}',
                    layout: coverLayout,
                    autoplay: true,
                    interactive: true,
                    watchdog: true
                },
                {
                    id: 'snake-rive-mobile',
                    src: '{{ asset('rive/cloudy-walk.riv') }}',
                    layout: coverLayout,
                    autoplay: true,
                    interactive: false,
                    watchdog: true
                },
                {
                    id: 'scroll-hint-rive',
                    src: '{{ asset('rive/scroll-down-indicator.riv') }}',
                    layout: coverLayout,
                    autoplay: true,
                    interactive: false,
                    watchdog: false
                }
            ];

            targets.forEach(function (target) {
                var canvas = document.getElementById(target.id);
                if (!canvas) {
                    log('Canvas tidak ditemukan: ' + target.id);
                    return;
                }

                // Pastikan canvas punya dimensi sebelum init
                if (canvas.offsetWidth === 0 || canvas.offsetHeight === 0) {
                    log('Canvas ' + target.id + ' memiliki ukuran 0. Menunggu...');
                    var checkSize = function () {
                        if (canvas.offsetWidth > 0 && canvas.offsetHeight > 0) {
                            log('Canvas ' + target.id + ' sekarang punya ukuran: ' +
                                canvas.offsetWidth + 'x' + canvas.offsetHeight);
                            initRive(canvas, target);
                        } else {
                            setTimeout(checkSize, 100);
                        }
                    };
                    checkSize();
                    return;
                }

                initRive(canvas, target);
            });
        }

        function initRive(canvas, target) {
            var layout = target.layout || new rive.Layout();

            // Urutan renderer: WebGL dulu (default), lalu Canvas2D jika gagal
            // (perangkat tanpa WebGL / hardware acceleration mati).
            var rendererList = ['webgl', 'canvas2d'];
            var attempt = 0;
            var instance = null;

            function applyFallback() {
                log('Semua renderer gagal untuk: ' + target.id + '. Memakai visual statis.');
                try {
                    var ctx = canvas.getContext('2d');
                    if (!ctx) return;
                    var w = canvas.offsetWidth, h = canvas.offsetHeight;
                    if (!w || !h) return;
                    var g = ctx.createLinearGradient(0, 0, 0, h);
                    g.addColorStop(0, '#ecfdf5');
                    g.addColorStop(1, '#a7f3d0');
                    ctx.fillStyle = g;
                    ctx.fillRect(0, 0, w, h);
                    ctx.fillStyle = 'rgba(16, 185, 129, .30)';
                    ctx.beginPath();
                    ctx.arc(w * 0.18, h * 0.80, w * 0.45, 0, Math.PI * 2);
                    ctx.arc(w * 0.85, h * 0.95, w * 0.50, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.fillStyle = 'rgba(251, 191, 36, .45)';
                    ctx.beginPath();
                    ctx.arc(w * 0.78, h * 0.20, Math.min(w, h) * 0.06, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.fillStyle = 'rgba(15, 23, 42, .55)';
                    ctx.font = 'bold ' + Math.round(Math.min(w, h) * 0.04) + 'px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText('Jurnal 7 Kebiasaan', w / 2, h * 0.90);
                } catch (e) {}
            }

            function setupInteractions(inst) {
                // Pause when scrolled out of view, resume when visible (perf + correctness).
                var canvasState = { pausedByObserver: false };
                if ('IntersectionObserver' in window) {
                    var observer = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (!inst) return;
                            if (entry.isIntersecting) {
                                canvasState.pausedByObserver = false;
                                try { inst.play(); } catch (e) {}
                            } else {
                                canvasState.pausedByObserver = true;
                                try { inst.pause(); } catch (e) {}
                            }
                        });
                    });
                    observer.observe(canvas);
                }

                // Auto-loop watchdog: cloudy-walk.riv berhenti di frame terakhir transisi.
                if (target.watchdog) {
                    setupAutoLoop(canvas, inst, target.id, canvasState);
                }

                // Interaksi mouse untuk cloudy-walk.riv
                if (target.interactive) {
                    setupMouseInteraction(canvas, inst, target.id);
                }

                // Fallback: jika animation belum start dalam 2 detik, coba play manual
                setTimeout(function () {
                    if (inst && inst.isLoaded && !inst.isPlaying) {
                        log('Fallback play manual untuk ' + target.id);
                        try { inst.play(); } catch (e) {}
                    }
                }, 2000);
            }

            function tryNext() {
                if (instance && instance.isLoaded) return;
                if (attempt >= rendererList.length) {
                    applyFallback();
                    return;
                }
                var renderer = rendererList[attempt++];
                log('Inisialisasi Rive (' + renderer + ') untuk canvas: ' + target.id);
                try {
                    instance = new rive.Rive({
                        src: target.src,
                        canvas: canvas,
                        autoplay: true,
                        layout: layout,
                        renderer: renderer,
                        stateMachines: 'State Machine 1', // Coba load state machine default
                        onLoad: function () {
                            log('Rive berhasil dimuat (' + renderer + '): ' + target.id);

                            // Debug: log state machines yang tersedia
                            if (instance.stateMachines) {
                                log('State machines available:', instance.stateMachines.length);
                                instance.stateMachines.forEach(function(sm, idx) {
                                    log('SM ' + idx + ':', sm.name || 'unnamed');
                                    if (sm.inputs) {
                                        log('  Inputs:', sm.inputs.length);
                                        sm.inputs.forEach(function(input) {
                                            log('    - ' + input.name + ' (' + input.type + ')');
                                        });
                                    }
                                });
                            } else {
                                log('No state machines found for: ' + target.id);
                            }

                            fitSurface(canvas, instance);
                            window.addEventListener('resize', function () {
                                fitSurface(canvas, instance);
                            });
                            window.addEventListener('orientationchange', function () {
                                setTimeout(function () {
                                    fitSurface(canvas, instance);
                                }, 200);
                            });

                            setupInteractions(instance);
                        },
                        onLoadError: function (err) {
                            log('Gagal memuat file .riv (' + target.id + ', ' + renderer + '):', err);
                            if (typeof err === 'object' && err.message) {
                                log('Error detail:', err.message);
                            }
                            tryNext();
                        },
                    });
                } catch (e) {
                    log('Rive init gagal (' + renderer + ') untuk ' + target.id + ':', e.message || e);
                    tryNext();
                }
            }

            tryNext();
        }

        function setupAutoLoop(canvas, instance, canvasId, canvasState) {
            if (!canvas || !instance) return;

            var SM_NAME = 'State Machine 1';
            var STALL_MS = 3000; // Durasi frame diam sebelum dianggap macet
            var lastStateChangeAt = Date.now();
            var lastPixelChangeAt = Date.now();
            var lastHash = null;
            var lastSampleAt = 0;

            instance.on(rive.EventType.StateChange, function () {
                lastStateChangeAt = Date.now();
            });

            function sampleHash() {
                var ctx = canvas.getContext && canvas.getContext('2d');
                if (!ctx) return null;
                var w = canvas.width, h = canvas.height;
                if (!w || !h) return null;
                var sx = Math.max(0, Math.floor(w / 2) - 16);
                var sy = Math.max(0, Math.floor(h / 2) - 16);
                var data = ctx.getImageData(sx, sy, 32, 32).data;
                var hash = 0;
                for (var i = 0; i < data.length; i += 4) {
                    hash = ((hash * 31) + data[i] + data[i + 1] + data[i + 2]) | 0;
                }
                return hash;
            }

            var watchdog = setInterval(function () {
                if (!instance || !instance.isLoaded) return;
                if (document.hidden || canvasState.pausedByObserver) return;

                var now = Date.now();
                if (now - lastSampleAt < 500) return;
                lastSampleAt = now;

                var hash;
                try {
                    hash = sampleHash();
                } catch (e) {
                    return;
                }
                if (hash === null) return;

                if (hash !== lastHash) {
                    lastHash = hash;
                    lastPixelChangeAt = now;
                }

                // Animasi macet: frame statis lama + tidak ada transisi state
                if (now - lastStateChangeAt > STALL_MS && now - lastPixelChangeAt > STALL_MS) {
                    log('Auto-loop: ' + canvasId + ' berhenti di frame terakhir. Restart state machine.');
                    lastHash = null;
                    lastStateChangeAt = now;
                    lastPixelChangeAt = now;
                    try { instance.stop(SM_NAME); } catch (e) {}
                    try { instance.play(SM_NAME); } catch (e) {}
                }
            }, 600);
        }

        function setupMouseInteraction(canvas, instance, canvasId) {
            if (!canvas || !instance) return;

            canvas.style.cursor = 'pointer';
            
            var lastMoveTime = 0;
            var throttleDelay = 16; // ~60fps

            function handlePointerMove(clientX, clientY) {
                var now = Date.now();
                if (now - lastMoveTime < throttleDelay) return;
                lastMoveTime = now;

                var rect = canvas.getBoundingClientRect();
                var x = (clientX - rect.left) / rect.width;
                var y = (clientY - rect.top) / rect.height;

                // Normalize to 0-1 range
                x = Math.max(0, Math.min(1, x));
                y = Math.max(0, Math.min(1, y));

                try {
                    // Method 1: Coba akses state machine
                    if (instance.stateMachines && instance.stateMachines.length > 0) {
                        var foundInput = false;
                        instance.stateMachines.forEach(function(sm) {
                            if (sm && sm.inputs) {
                                sm.inputs.forEach(function(input) {
                                    if (input && input.name) {
                                        var name = input.name.toLowerCase();
                                        if (name.includes('x') || name.includes('pointer') && name.includes('x')) {
                                            input.value = x;
                                            foundInput = true;
                                        }
                                        if (name.includes('y') || name.includes('pointer') && name.includes('y')) {
                                            input.value = y;
                                            foundInput = true;
                                        }
                                    }
                                });
                            }
                        });
                        if (!foundInput) {
                            log('No pointer inputs found in state machines');
                        }
                    }

                    // Method 2: Coba menggunakan pointer events built-in Rive
                    if (typeof instance.pointerMove === 'function') {
                        var canvasX = x * canvas.width;
                        var canvasY = y * canvas.height;
                        instance.pointerMove(canvasX, canvasY);
                    }
                    
                    // Method 3: Coba mouse event API
                    if (typeof instance.handleMouseMove === 'function') {
                        instance.handleMouseMove(x, y);
                    }
                } catch (e) {
                    log('Error setting pointer input:', e.message);
                }
            }

            // Mouse events
            canvas.addEventListener('mousemove', function(e) {
                handlePointerMove(e.clientX, e.clientY);
            });

            canvas.addEventListener('mousedown', function(e) {
                try {
                    if (typeof instance.pointerDown === 'function') {
                        var rect = canvas.getBoundingClientRect();
                        var x = (e.clientX - rect.left);
                        var y = (e.clientY - rect.top);
                        instance.pointerDown(x, y);
                    }
                } catch (e) {}
            });

            canvas.addEventListener('mouseup', function(e) {
                try {
                    if (typeof instance.pointerUp === 'function') {
                        var rect = canvas.getBoundingClientRect();
                        var x = (e.clientX - rect.left);
                        var y = (e.clientY - rect.top);
                        instance.pointerUp(x, y);
                    }
                } catch (e) {}
            });

            // Touch events
            canvas.addEventListener('touchstart', function(e) {
                if (e.touches && e.touches.length > 0) {
                    // CATATAN: TIDAK memanggil preventDefault() di sini —
                    // canvas menutupi hero full-screen; preventDefault memblokir
                    // scroll native di mobile. Scroll harus tetap berjalan.
                    try {
                        if (typeof instance.pointerDown === 'function') {
                            var rect = canvas.getBoundingClientRect();
                            var touch = e.touches[0];
                            var x = touch.clientX - rect.left;
                            var y = touch.clientY - rect.top;
                            instance.pointerDown(x, y);
                        }
                    } catch (e) {}
                }
            }, { passive: true });

            canvas.addEventListener('touchmove', function(e) {
                if (e.touches && e.touches.length > 0) {
                    var touch = e.touches[0];
                    handlePointerMove(touch.clientX, touch.clientY);
                }
            }, { passive: true });

            canvas.addEventListener('touchend', function(e) {
                try {
                    if (typeof instance.pointerUp === 'function') {
                        instance.pointerUp(0, 0);
                    }
                } catch (e) {}
            });

            log('Mouse interaction enabled for:', canvasId);
        }

        function fitSurface(canvas, instance) {
            if (!instance || !canvas) return;
            try {
                if (canvas.offsetWidth > 0 && canvas.offsetHeight > 0) {
                    instance.resizeDrawingSurfaceToCanvas();
                }
            } catch (e) {
                log('Error fitSurface:', e.message || e);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initWhenReady);
        } else {
            initWhenReady();
        }
    })();


    // ==========================================
    // Bottom Sheet: Drag Interaktif + Snap (Mobile Only)
    // ==========================================
    (function () {
        'use strict';

        function log() {
            if (typeof console !== 'undefined') {
                console.log.apply(console, ['[LoginSheet]'].concat(Array.prototype.slice.call(arguments)));
            }
        }

        var stage = document.getElementById('mobile-stage');
        var sheet = document.getElementById('login-sheet');
        var hint = document.getElementById('sheet-hint');
        var peekArea = document.getElementById('sheet-peek');
        if (!stage || !sheet) return;

        var OPEN_RATIO = 0.08;   // Sisa strip Rive di atas saat sheet terbuka penuh
        var THRESHOLD = 0.30;    // 30% tinggi layar untuk snap terbuka
        var SNAP_MS = 380;       // Durasi animasi snap (ease-out, native feel)
        var VELOCITY_PPS = 600;  // Ambang kecepatan jari (px/detik) untuk snap arah

        var openOffset = 0;      // translateY saat terbuka penuh (px)
        var peekOffset = 0;      // translateY saat peek (px)

        function measure() {
            var H = stage.clientHeight;
            var peekH = (peekArea ? peekArea.offsetHeight : 0) + 16;
            openOffset = Math.round(H * OPEN_RATIO);
            peekOffset = Math.max(H - peekH, openOffset + 60);
        }

        var state = 'peek';      // peek | open
        var dragging = false;
        var startY = 0;
        var startTranslate = 0;
        var lastMoveY = 0;
        var lastMoveT = 0;
        var velY = 0;

        function setTranslate(y, animate) {
            sheet.style.transition = animate
                ? 'transform ' + SNAP_MS + 'ms cubic-bezier(.32,.72,0,1)'
                : 'none';
            sheet.style.transform = 'translate3d(0,' + y + 'px,0)';
        }

        function applyState(s, animate) {
            state = s;
            if (animate !== false) animate = true;
            setTranslate(s === 'open' ? openOffset : peekOffset, animate);
            if (hint) {
                hint.style.opacity = s === 'open' ? '0' : '1';
                hint.style.pointerEvents = s === 'open' ? 'none' : 'auto';
            }
        }

        function onStart(clientY) {
            measure();
            dragging = true;
            startY = clientY;
            startTranslate = state === 'open' ? openOffset : peekOffset;
            lastMoveY = clientY;
            lastMoveT = Date.now();
            velY = 0;
            setTranslate(startTranslate, false);
        }

        function onMove(clientY) {
            if (!dragging) return;
            var now = Date.now();
            var dt = Math.max(1, now - lastMoveT);
            velY = ((clientY - lastMoveY) / dt) * 1000;
            lastMoveY = clientY;
            lastMoveT = now;

            var y = startTranslate + (clientY - startY); // geser ke atas (clientY mengecil) -> y mengecil (sheet naik)
            y = Math.max(openOffset, Math.min(peekOffset, y));
            setTranslate(y, false);
        }

        function onEnd() {
            if (!dragging) return;
            dragging = false;

            var endTranslate = startTranslate + (lastMoveY - startY);
            endTranslate = Math.max(openOffset, Math.min(peekOffset, endTranslate));

            var screenH = stage.clientHeight;
            var open;

            if (state === 'open') {
                // Tertutup jika ditarik ke bawah cukup jauh atau dilempar ke bawah cepat
                var pullDown = endTranslate - openOffset;
                var flickDown = velY > VELOCITY_PPS;
                open = !flickDown && pullDown < screenH * 0.12;
            } else {
                var draggedUp = peekOffset - endTranslate;
                var flickUp = velY < -VELOCITY_PPS;
                open = (draggedUp > screenH * THRESHOLD) || flickUp;
            }

            applyState(open ? 'open' : 'peek');
        }

        // Touch: mulai drag dari area peek (kedua state) atau dari kanvas (saat peek)
        stage.addEventListener('touchstart', function (e) {
            if (e.touches.length !== 1) return;
            var t = e.touches[0];
            if (state === 'open') {
                if (!peekArea || !peekArea.contains(t.target)) return;
            }
            onStart(t.clientY);
        }, { passive: true });

        stage.addEventListener('touchmove', function (e) {
            if (!dragging || e.touches.length !== 1) return;
            onMove(e.touches[0].clientY);
        }, { passive: true });

        stage.addEventListener('touchend', function (e) {
            if (!dragging) return;
            if (e.changedTouches.length === 1) {
                lastMoveY = e.changedTouches[0].clientY;
            }
            onEnd();
        }, { passive: true });

        // Re-measure saat resize / orientasi berubah
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) return;
            measure();
            applyState(state, false);
        });
        window.addEventListener('orientationchange', function () {
            setTimeout(function () {
                measure();
                applyState(state, false);
            }, 200);
        });

        // Inisialisasi: ukur lalu set posisi peek yang presisi (tanpa animasi)
        measure();
        applyState('peek', false);
        log('Bottom sheet siap. Peek:', peekOffset + 'px, Open:', openOffset + 'px');
    })();


    // ==========================================
    // Interactive Portal & Form Logic
    // (scoped per instance: sheet mobile + panel desktop)
    // ==========================================
    function initLoginForm(root) {
        if (!root) return;

        const radios = root.querySelectorAll('input[name="login_as"]');
        const hintEl = root.querySelector('#portal-hint-text');
        const nisLabel = root.querySelector('#nis-label');
        const nisInput = root.querySelector('#nis');
        const nisIcon = root.querySelector('#nis-icon');
        const quickDemoBtn = root.querySelector('#quick-demo-btn');
        const nisDemoLink = root.querySelector('#nis-demo-link');
        const clearNisBtn = root.querySelector('#clear-nis-btn');

        const portalData = {
            siswa: {
                label: 'NIS Siswa',
                placeholder: 'Contoh: 12345678',
                hint: 'Gunakan Nomor Induk Siswa (NIS) yang terdaftar di sekolah.',
                demo: '12345678',
                icon: 'id-card'
            },
            staff: {
                label: 'ID Pengguna (Admin / Guru)',
                placeholder: 'Contoh: ADMIN001 / GURU001',
                hint: 'Khusus akun Admin dan Guru/Wali Kelas dari pihak sekolah.',
                demo: 'ADMIN001',
                icon: 'shield-user'
            },
        };

        const portalIcons = {
            'id-card': `@include('partials.icon', ['name' => 'id-card', 'class' => 'w-4 h-4'])`,
            'shield-user': `@include('partials.icon', ['name' => 'shield-user', 'class' => 'w-4 h-4'])`,
        };

        function swapText(el, text) {
            if (!el) return;
            el.style.transition = 'opacity .15s ease, transform .15s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(4px)';
            setTimeout(() => {
                el.textContent = text;
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, 150);
        }

        function applyPortal(value, animate) {
            const d = portalData[value] || portalData.siswa;
            if (animate) {
                swapText(hintEl, d.hint);
                swapText(nisLabel, d.label);
            } else {
                if (hintEl) hintEl.textContent = d.hint;
                if (nisLabel) nisLabel.textContent = d.label;
            }
            if (nisInput) nisInput.placeholder = d.placeholder;
            if (nisIcon) {
                nisIcon.innerHTML = portalIcons[d.icon] || portalIcons['id-card'];
            }

            const form = root.querySelector('#login-form');
            if (form) form.dataset.portal = value;
        }

        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                applyPortal(radio.value, true);
                const card = radio.closest('label');
                const badge = card ? card.querySelector('.portal-check') : null;
                if (badge) {
                    badge.classList.remove('portal-check-pop');
                    void badge.offsetWidth;
                    badge.classList.add('portal-check-pop');
                    setTimeout(() => badge.classList.remove('portal-check-pop'), 500);
                }
            });
        });

        const checked = root.querySelector('input[name="login_as"]:checked');
        if (checked) applyPortal(checked.value, false);

        // Quick demo filler (shared by the tip bar button + "Contoh ID" label link)
        function fillDemo() {
            const currentPortal = root.querySelector('input[name="login_as"]:checked')?.value || 'siswa';
            const demoVal = portalData[currentPortal]?.demo || '12345678';
            nisInput.value = demoVal;
            nisInput.focus();
            toggleClearBtn();
        }
        if (quickDemoBtn) quickDemoBtn.addEventListener('click', fillDemo);
        if (nisDemoLink) nisDemoLink.addEventListener('click', fillDemo);

        // NIS input clear button toggle
        function toggleClearBtn() {
            if (!clearNisBtn || !nisInput) return;
            if (nisInput.value.length > 0) {
                clearNisBtn.classList.remove('hidden');
            } else {
                clearNisBtn.classList.add('hidden');
            }
        }
        if (nisInput) {
            nisInput.addEventListener('input', toggleClearBtn);
            toggleClearBtn();
        }
        if (clearNisBtn && nisInput) {
            clearNisBtn.addEventListener('click', () => {
                nisInput.value = '';
                toggleClearBtn();
                nisInput.focus();
            });
        }

        // Password visibility toggle
        const pwBtn = root.querySelector('#toggle-password');
        const pwInput = root.querySelector('#password');
        if (pwBtn && pwInput) {
            pwBtn.addEventListener('click', () => {
                const show = pwInput.type === 'password';
                pwInput.type = show ? 'text' : 'password';
                pwBtn.innerHTML = show
                    ? `@include('partials.icon', ['name' => 'eye-off', 'class' => 'w-4 h-4'])`
                    : `@include('partials.icon', ['name' => 'eye', 'class' => 'w-4 h-4'])`;
            });
        }

        // Caps Lock Detection
        const capsWarning = root.querySelector('#capslock-warning');
        if (pwInput && capsWarning) {
            ['keydown', 'keyup'].forEach(eventType => {
                pwInput.addEventListener(eventType, (e) => {
                    const isCaps = e.getModifierState && e.getModifierState('CapsLock');
                    if (isCaps) {
                        capsWarning.classList.remove('hidden');
                    } else {
                        capsWarning.classList.add('hidden');
                    }
                });
            });
            pwInput.addEventListener('blur', () => {
                capsWarning.classList.add('hidden');
            });
        }

        // Loading state on submit
        const loginForm = root.querySelector('#login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', () => {
                const btn = root.querySelector('#login-submit-btn');
                const spinner = root.querySelector('#login-spinner');
                const label = root.querySelector('#login-submit-label');
                const arrow = root.querySelector('#login-arrow');
                if (btn) btn.disabled = true;
                if (spinner) spinner.classList.remove('hidden');
                if (arrow) arrow.classList.add('hidden');
                if (label) label.textContent = 'Memproses...';
                if (typeof showPageLoader === 'function') showPageLoader();
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initLoginForm(document.getElementById('mobile-stage'));
        initLoginForm(document.getElementById('desktop-panel'));
    });
</script>
@endpush