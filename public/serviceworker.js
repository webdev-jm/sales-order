var staticCacheName = "sms-pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/images/icons/icon-72x72.png',
    '/images/icons/icon-96x96.png',
    '/images/icons/icon-128x128.png',
    '/images/icons/icon-144x144.png',
    '/images/icons/icon-152x152.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-384x384.png',
    '/images/icons/icon-512x512.png',
    '/vendor/fontawesome-free/css/all.min.css',
    '/vendor/overlayScrollbars/css/OverlayScrollbars.min.css',
    '/vendor/overlayScrollbars/js/OverlayScrollbars.min.js',
    '/vendor/adminlte/dist/css/adminlte.min.css',
    '/vendor/fullcalendar/main.min.css',
    '/vendor/fullcalendar/main.min.js',
    '/vendor/select2/css/select2.min.css',
    '/vendor/ekko-lightbox/ekko-lightbox.css',
    '/vendor/jquery/jquery.min.js',
    '/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/vendor/select2/js/select2.min.js',
    '/vendor/ekko-lightbox/ekko-lightbox.min.js',
    '/vendor/adminlte/dist/js/adminlte.min.js',
    '/vendor/adminlte/dist/img/AdminLTELogo.png',
    '/vendor/livewire/livewire.js',
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});