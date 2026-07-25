const VERSION = 'luzicity-pwa-v9';
const STATIC_CACHE = `${VERSION}-static`;
const PAGES_CACHE = `${VERSION}-pages`;
const RUNTIME_CACHE = `${VERSION}-runtime`;
const APP_SHELL = ['/', '/buscar', '/radio', '/offline', '/manifest.webmanifest', '/pwa/icon.svg'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(STATIC_CACHE).then((cache) => cache.addAll(APP_SHELL)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', (event) => {
    event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((key) => !key.startsWith(VERSION)).map((key) => caches.delete(key)))).then(() => self.clients.claim()).then(() => self.clients.matchAll({ type: 'window' })).then((clients) => clients.forEach((client) => client.postMessage({ type: 'LZ_PWA_UPDATED', version: VERSION }))));
});
self.addEventListener('message', (event) => { if (event.data?.type === 'SKIP_WAITING') self.skipWaiting(); });
self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).then((response) => {
            const copy = response.clone();
            caches.open(PAGES_CACHE).then((cache) => cache.put(request, copy));
            return response;
        }).catch(async () => (await caches.match(request)) || (await caches.match('/offline'))));
        return;
    }
    if (['style', 'script', 'image', 'font'].includes(request.destination)) {
        event.respondWith(caches.match(request).then((cached) => {
            const network = fetch(request).then((response) => {
                if (response.ok) caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, response.clone()));
                return response;
            }).catch(() => cached);
            return cached || network;
        }));
    }
});
