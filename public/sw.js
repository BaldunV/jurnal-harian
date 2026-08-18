/* ============================================================
 * Jurnal 7 Kebiasaan — Service Worker v4 (Offline Mode)
 *
 * Strategi:
 *  - Precache: shell offline + manifest + ikon (saat install).
 *  - Static asset (build/, images/, js/, rive/): stale-while-revalidate.
 *  - Halaman (navigasi GET, kecuali /login): stale-while-revalidate,
 *    fallback cache saat offline. Halaman tersimpan di perangkat ini.
 *  - API GET (/api/...): stale-while-revalidate, fallback cache.
 *  - Simpan jurnal offline ditangani halaman (Livewire): antrian di
 *    localStorage, disinkronkan otomatis saat online.
 *  - TIDAK PERNAH di-cache: /login, POST, request dengan header
 *    authorization, request cross-origin.
 * ============================================================ */

const CACHE_VERSION = 'v4';
const CACHE_ASSETS = 'jurnal-assets-' + CACHE_VERSION;
const CACHE_PAGES = 'jurnal-pages-' + CACHE_VERSION;
const CACHE_API = 'jurnal-api-' + CACHE_VERSION;
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
];

const ACTIVE_CACHES = [CACHE_ASSETS, CACHE_PAGES, CACHE_API];

/* Asset statis same-origin yang aman di-cache (memiliki hash/versi di filename). */
function isStaticAsset(url) {
    return /^\/(build|images|js|rive|icons|fonts)\//.test(url.pathname)
        || /^\/(favicon\.ico|offline\.html|manifest\.json)$/.test(url.pathname);
}

/* Simpan response ke cache (diam-diam jika gagal). */
function cachePut(cacheName, request, response) {
    return caches.open(cacheName)
        .then((cache) => cache.put(request, response))
        .catch(() => {});
}

function isSameOrigin(request) {
    return new URL(request.url).origin === self.location.origin;
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_ASSETS)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
            .catch(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key.startsWith('jurnal-') && !ACTIVE_CACHES.includes(key))
                        .map((key) => caches.delete(key))
                )
            )
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (!isSameOrigin(request)) return;
    if (request.headers.has('authorization')) return;

    /* Non-GET: langsung ke jaringan. */
    if (request.method !== 'GET') return;

    /* --- Navigasi halaman: SWR, fallback cache saat offline.
     *     /login tidak pernah di-cache (sesi masuk tetap butuh jaringan). --- */
    if (request.mode === 'navigate') {
        const isLoginPage = url.pathname === '/login';
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (!isLoginPage && response.ok) {
                        cachePut(CACHE_PAGES, request, response.clone());
                    }
                    return response;
                })
                .catch(() =>
                    caches.match(request)
                        .then((cached) => cached || caches.match(OFFLINE_URL))
                        .then((fallback) => fallback || new Response('Offline', { status: 503, statusText: 'Offline' }))
                )
        );
        return;
    }

    /* --- API GET: SWR, fallback cache. --- */
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const network = fetch(request)
                    .then((response) => {
                        if (response.ok) {
                            cachePut(CACHE_API, request, response.clone());
                        }
                        return response;
                    })
                    .catch(() => cached);
                return cached || network;
            })
        );
        return;
    }

    /* --- Asset statis: stale-while-revalidate --- */
    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const network = fetch(request)
                    .then((response) => {
                        if (response.ok) {
                            cachePut(CACHE_ASSETS, request, response.clone());
                        }
                        return response;
                    })
                    .catch(() => cached);
                return cached || network;
            })
        );
        return;
    }

    /* --- GET same-origin lain: jaringan, fallback offline.html untuk root. --- */
    event.respondWith(fetch(request).catch(() => {
        if (url.pathname === '/') {
            return caches.match(OFFLINE_URL);
        }
        return Response.error();
    }));
});

/* Update Service Worker: aktifkan versi baru segera setelah terpasang. */
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});