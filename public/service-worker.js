const VERSION = 'luzicity-pwa-v11';
const STATIC_CACHE = `${VERSION}-static`;
const PAGES_CACHE = `${VERSION}-pages`;
const RUNTIME_CACHE = `${VERSION}-runtime`;
const OFFLINE_URL = '/offline';
const PRIVATE_PATHS = [
    '/admin',
    '/api',
    '/dashboard',
    '/login',
    '/logout',
    '/push',
];
const STATIC_ASSETS = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/pwa/icon.svg',
    '/pwa/icon-192.png',
    '/pwa/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(async (cache) => {
                await Promise.allSettled(STATIC_ASSETS.map((asset) => cache.add(asset)));
            }),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => !key.startsWith(VERSION)).map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim())
            .then(() => self.clients.matchAll({ type: 'window' }))
            .then((clients) => clients.forEach((client) => client.postMessage({
                type: 'LZ_PWA_UPDATED',
                version: VERSION,
            }))),
    );
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') self.skipWaiting();

    if (event.data?.type === 'CLEAR_CACHES') {
        event.waitUntil(
            caches.keys().then((keys) => Promise.all(
                keys.filter((key) => key.startsWith('luzicity-pwa-')).map((key) => caches.delete(key)),
            )),
        );
    }
});

const isPrivateRequest = (request, url) =>
    request.headers.has('Authorization') ||
    PRIVATE_PATHS.some((path) => url.pathname === path || url.pathname.startsWith(`${path}/`));

const canCache = (response) => {
    const cacheControl = response.headers.get('Cache-Control') || '';

    return response.ok &&
        response.type === 'basic' &&
        !/\b(no-store|private)\b/i.test(cacheControl);
};

const networkFirstPage = async (request) => {
    const cache = await caches.open(PAGES_CACHE);

    try {
        const response = await fetch(request);
        if (canCache(response)) await cache.put(request, response.clone());
        return response;
    } catch {
        return (await cache.match(request)) ||
            (await caches.match(OFFLINE_URL)) ||
            Response.error();
    }
};

const staleWhileRevalidate = async (request) => {
    const cache = await caches.open(RUNTIME_CACHE);
    const cached = await cache.match(request);
    const network = fetch(request)
        .then(async (response) => {
            if (canCache(response)) await cache.put(request, response.clone());
            return response;
        })
        .catch(() => cached);

    return cached || network.then((response) => response || Response.error());
};

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin || isPrivateRequest(request, url)) return;

    if (request.mode === 'navigate') {
        event.respondWith(networkFirstPage(request));
        return;
    }

    if (['style', 'script', 'image', 'font'].includes(request.destination)) {
        event.respondWith(staleWhileRevalidate(request));
    }
});
