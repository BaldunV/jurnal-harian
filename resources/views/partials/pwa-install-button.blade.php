{{-- ===== PWA Install / Download Aplikasi Button (variants: prominent | header) ===== --}}
@php($pwaVariant = $variant ?? 'prominent')

@if($pwaVariant === 'header')
    <button type="button" data-pwa-install title="Download Aplikasi" aria-label="Download Aplikasi" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-500/20 hover:text-emerald-600 dark:hover:text-emerald-300 transition-all flex items-center justify-center text-sm font-semibold">
        @include('partials.icon', ['name' => 'download', 'class' => 'w-4 h-4'])
    </button>
@else
    <button type="button" data-pwa-install class="group w-full flex items-center justify-center gap-2.5 py-3.5 px-5 rounded-2xl bg-gradient-to-r from-primary-700 via-primary-600 to-primary-700 bg-[length:200%_auto] transition-[background-position] duration-300 hover:bg-right text-white font-bold text-sm shadow-lg shadow-emerald-600/25 hover:shadow-xl hover:shadow-emerald-600/35 hover:-translate-y-0.5 transition-all duration-300 active:scale-[.98]">
        @include('partials.icon', ['name' => 'download', 'class' => 'w-4 h-4'])
        <span>Download Aplikasi</span>
        <span class="ml-auto text-[10px] font-bold uppercase tracking-wider bg-white/15 rounded-full px-2.5 py-1">PWA</span>
    </button>
@endif

@once
{{-- ===== Modal Panduan Install (dibuat satu kali per halaman) ===== --}}
<div id="pwa-install-modal" class="fixed inset-0 z-[90] hidden items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="pwa-install-title">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" data-pwa-modal-close></div>
    <div class="relative w-full sm:max-w-md bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    @include('partials.icon', ['name' => 'download', 'class' => 'w-5 h-5'])
                </span>
                <div>
                    <h3 id="pwa-install-title" class="font-display font-extrabold text-base text-slate-800 dark:text-slate-100">Pasang Aplikasi Jurnal</h3>
                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-500">Buka lebih cepat, tanpa buka browser</p>
                </div>
            </div>
            <button type="button" data-pwa-modal-close aria-label="Tutup" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors flex items-center justify-center cursor-pointer">
                @include('partials.icon', ['name' => 'x', 'class' => 'w-4 h-4'])
            </button>
        </div>

        <div id="pwa-steps-ios" class="space-y-3 hidden">
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">1</span>
                <p class="text-sm text-slate-600 dark:text-slate-300">Buka halaman ini di browser <b>Safari</b> (bukan Chrome).</p>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">2</span>
                <p class="text-sm text-slate-600 dark:text-slate-300">Ketuk tombol <b>Bagikan</b> (kotak dengan panah ke atas) di bagian bawah layar.</p>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">3</span>
                <p class="text-sm text-slate-600 dark:text-slate-300">Pilih <b>&quot;Tambahkan ke Layar Utama&quot;</b> (Add to Home Screen).</p>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">4</span>
                <p class="text-sm text-slate-600 dark:text-slate-300">Ketuk <b>&quot;Tambah&quot;</b> di pojok kanan atas, lalu buka ikon aplikasi dari layar utama.</p>
            </div>
        </div>

        <div id="pwa-steps-android" class="space-y-3 hidden">
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">1</span>
                <p class="text-sm text-slate-600 dark:text-slate-300">Buka halaman ini di browser <b>Chrome</b>.</p>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">2</span>
                <p class="text-sm text-slate-600 dark:text-slate-300">Ketuk ikon menu <b>&#8942;</b> (tiga titik) di pojok kanan atas.</p>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">3</span>
                <p class="text-sm text-slate-600 dark:text-slate-300">Pilih <b>&quot;Tambahkan ke Layar Utama&quot;</b> atau <b>&quot;Instal aplikasi&quot;</b> (Install app).</p>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">4</span>
                <p class="text-sm text-slate-600 dark:text-slate-300">Ketuk <b>&quot;Instal&quot;</b> / <b>&quot;Tambah&quot;</b>, lalu buka ikon aplikasi dari layar utama.</p>
            </div>
        </div>

        <div id="pwa-steps-other" class="space-y-3 hidden">
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">1</span>
                <p class="text-sm text-slate-600 dark:text-slate-300">Buka halaman ini di browser <b>Chrome / Edge</b>.</p>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">2</span>
                <p class="text-sm text-slate-600 dark:text-slate-300">Buka menu browser, lalu pilih <b>&quot;Tambahkan ke Layar Utama&quot;</b> atau <b>&quot;Install aplikasi&quot;</b>.</p>
            </div>
        </div>

        <div id="pwa-http-warning" class="mb-3 p-3 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-300 text-xs hidden">
            <b>Halaman ini diakses lewat HTTP (bukan HTTPS).</b><br>
            Karena itu browser tidak menampilkan opsi <i>&quot;Instal aplikasi&quot;</i> dan tombol <i>&quot;Tambahkan ke Layar Utama&quot;</i> hanya membuat pintasan (tetap buka browser).<br><br>
            Agar bisa di-install sebagai aplikasi, akses halaman melalui alamat <b>HTTPS</b> (mis. <code>https://jurnal.test</code> atau alamat hosting yang sudah HTTPS), lalu ulangi lagi.
        </div>

        <div id="pwa-https-note" class="mt-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-300 text-xs hidden">
            <b>Catatan:</b> Opsi &quot;Instal aplikasi&quot; di menu browser hanya muncul saat situs diakses melalui <b>HTTPS</b>. Jika tidak muncul, akses halaman lewat HTTPS lalu coba lagi.
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var modal = document.getElementById('pwa-install-modal');
        if (!modal) return;

        function isIOS() {
            return /iPhone|iPad|iPod/.test(navigator.userAgent) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        }

        function isAndroid() {
            return /Android/i.test(navigator.userAgent);
        }

        function isStandalone() {
            return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
                window.navigator.standalone === true;
        }

        if (isStandalone()) {
            document.querySelectorAll('[data-pwa-install]').forEach(function (btn) {
                btn.classList.add('hidden');
            });
        }

        function openModal() {
            var ios = isIOS();
            var android = isAndroid();
            var isSecure = window.isSecureContext;
            var insecure = !isSecure && !ios;

            document.getElementById('pwa-steps-ios').classList.toggle('hidden', !ios);
            document.getElementById('pwa-steps-android').classList.toggle('hidden', !android || ios);
            document.getElementById('pwa-steps-other').classList.toggle('hidden', ios || android);
            document.getElementById('pwa-http-warning').classList.toggle('hidden', !insecure);
            document.getElementById('pwa-https-note').classList.toggle('hidden', isSecure || ios);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('[data-pwa-install]')) {
                e.preventDefault();
                var promptEvent = window.__pwaDeferredPrompt;
                if (promptEvent) {
                    promptEvent.prompt();
                    promptEvent.userChoice.then(function (choice) {
                        if (choice && choice.outcome === 'accepted') {
                            window.__pwaDeferredPrompt = null;
                        }
                    });
                    return;
                }
                openModal();
                return;
            }
            if (e.target.closest && e.target.closest('[data-pwa-modal-close]')) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
</script>
@endonce
