const CACHE_NAME = 'travel-pwa-cache-v2';

self.addEventListener('install', event => {
    self.skipWaiting(); // Memaksa SW baru untuk langsung aktif
});

self.addEventListener('activate', event => {
    event.waitUntil(clients.claim()); // Mengambil alih halaman tanpa perlu reload
});

self.addEventListener('fetch', event => {
    // Hanya tangani request GET
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Jangan cache halaman peringatan ngrok
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                
                // Simpan ke cache untuk offline
                const responseToCache = response.clone();
                caches.open(CACHE_NAME)
                    .then(cache => {
                        cache.put(event.request, responseToCache);
                    });
                return response;
            })
            .catch(() => {
                // Jika jaringan mati, coba ambil dari cache
                return caches.match(event.request);
            })
    );
});
