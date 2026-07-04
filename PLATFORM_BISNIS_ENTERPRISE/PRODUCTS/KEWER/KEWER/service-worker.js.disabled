/**
 * Kewer Service Worker — PWA Support
 * Cache strategy: Cache-first untuk aset statis, Network-first untuk API/halaman dinamis
 */

const CACHE_NAME   = 'kewer-v2.3.2';
const STATIC_CACHE = 'kewer-static-v2';

// Aset yang di-cache untuk offline
const STATIC_ASSETS = [
    // PHP pages should not be cached as static assets - they are dynamic
    // Only cache CDN assets and static files
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
];

// Halaman fallback saat offline
const OFFLINE_PAGE = '/kewer/offline.php';

// ── Install: cache aset statis ────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(STATIC_ASSETS)).catch(console.warn)
    );
    self.skipWaiting();
});

// ── Activate: hapus cache lama ────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter(k => k !== CACHE_NAME && k !== STATIC_CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// ── Fetch: strategi hybrid ────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // API calls — network only (jangan cache)
    if (url.pathname.startsWith('/kewer/api/')) {
        event.respondWith(
            fetch(event.request).catch(() =>
                new Response(JSON.stringify({ error: 'Anda sedang offline. Data akan disinkronkan saat terhubung kembali.' }), {
                    headers: { 'Content-Type': 'application/json' }
                })
            )
        );
        return;
    }

    // PHP files — network only (jangan cache untuk menghindari MIME type errors)
    if (url.pathname.endsWith('.php')) {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_PAGE))
        );
        return;
    }

    // CDN aset statis — cache first
    if (url.origin !== location.origin) {
        event.respondWith(
            caches.match(event.request).then(cached => cached || fetch(event.request).then(resp => {
                const clone = resp.clone();
                caches.open(STATIC_CACHE).then(cache => cache.put(event.request, clone));
                return resp;
            }).catch(() => caches.match(OFFLINE_PAGE)))
        );
        return;
    }

    // Halaman lain — network first, fallback ke cache
    event.respondWith(
        fetch(event.request).then(resp => {
            const clone = resp.clone();
            if (resp.status === 200) {
                caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
            }
            return resp;
        }).catch(() => caches.match(event.request) || caches.match(OFFLINE_PAGE))
    );
});

// ── Background sync: proses offline queue ─────────────────────────
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-pembayaran') {
        event.waitUntil(syncOfflinePembayaran());
    }
});

async function syncOfflinePembayaran() {
    try {
        const resp = await fetch('/kewer/api/business.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'proses_semua_offline_queue' })
        });
        const r = await resp.json();
        if (r.success) {
            self.registration.showNotification('Kewer', {
                body: `${r.diproses} pembayaran offline berhasil disinkronkan.`,
                icon: '/kewer/assets/img/icon-192.png'
            });
        }
    } catch (e) {
        console.warn('Sync gagal:', e);
    }
}
