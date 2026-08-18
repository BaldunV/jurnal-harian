{{-- ===== PWA: Service Worker registration + Install Prompt (aman di browser tanpa dukungan PWA) ===== --}}
<script defer>
    (function () {
        'use strict';

        function setButtonsHidden(hidden) {
            document.querySelectorAll('[data-pwa-install]').forEach(function (btn) {
                btn.classList.toggle('hidden', hidden);
            });
        }

        /* Sembunyikan tombol jika sudah berjalan sebagai aplikasi ter-install. */
        var isStandalone = window.matchMedia && window.matchMedia('(display-mode: standalone)').matches;
        if (isStandalone || window.navigator.standalone === true) {
            setButtonsHidden(true);
        }

        window.__pwaDeferredPrompt = null;

        window.addEventListener('beforeinstallprompt', function (event) {
            event.preventDefault();
            window.__pwaDeferredPrompt = event;
            setButtonsHidden(false);
        });

        window.addEventListener('appinstalled', function () {
            window.__pwaDeferredPrompt = null;
            setButtonsHidden(true);
        });

        /* Registrasi Service Worker: hanya pada secure context; gagal diam-diam di HTTP non-localhost. */
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                try {
                    navigator.serviceWorker.register('/sw.js', { scope: '/' })
                        .then(function (registration) {
                            /* Perbarui SW aktif segera jika ada versi baru menunggu. */
                            if (registration.waiting) {
                                registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                            }
                        })
                        .catch(function () {});
                } catch (error) {}
            });
        }
    })();
</script>
