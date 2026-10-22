const STATIC_CACHE = 'wholemarket-static-v2';
const OFFLINE_PAGE = '/offline.html';
const STATIC_ASSETS = [
    OFFLINE_PAGE,
    '/manifest.webmanifest',
    '/storefront/style.css',
    '/storefront/icon.svg',
    '/storefront/icon-192.png',
    '/storefront/icon-512.png',
];

const isProtectedOrMutablePath = (pathname) => /\/(admin|api|billing|dashboard|enquiries|login|logout|payments|retailer|seller|wholesaler)(\/|$)/.test(pathname);
const isStaticAsset = (pathname) => /\.(?:avif|css|gif|ico|jpe?g|js|png|svg|webp)$/i.test(pathname)
    && (pathname.startsWith('/storefront/') || pathname.startsWith('/templates/'));

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys
                .filter((key) => key !== STATIC_CACHE)
                .map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    if (url.origin !== self.location.origin || isProtectedOrMutablePath(url.pathname)) {
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(fetch(event.request).catch(() => caches.match(OFFLINE_PAGE)));

        return;
    }

    if (!isStaticAsset(url.pathname)) {
        return;
    }

    event.respondWith(caches.match(event.request).then((cachedResponse) => cachedResponse || fetch(event.request)));
});
