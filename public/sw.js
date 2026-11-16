/*
 * Bookworm's service worker.
 *  - App files (built CSS/JS, images, covers): from the cache first, so the app opens fast.
 *  - Pages: from the network; when offline, the last copy or the offline page.
 *  - PDFs you open in the reader are kept, so you can keep reading offline.
 *    They are private: logging out clears them (see resources/js/app.js).
 *  - Push notifications.
 */
const VERSION = 'v1';
const STATIC = `bw-static-${VERSION}`;
const PAGES = 'bw-pages';
const PDFS = 'bw-pdfs';
const SHELL = ['/offline', '/images/app/icon-192.png', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(STATIC).then((cache) => cache.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key.startsWith('bw-static-') && key !== STATIC).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

const isReaderPage = (url) => /^\/books\/\d+\/pdfs\/\d+\/read$/.test(url.pathname);
const isPdfFile = (url) => /^\/books\/\d+\/pdfs\/\d+\/file$/.test(url.pathname);
const isHighlights = (url) => /^\/books\/\d+\/pdfs\/\d+\/highlights$/.test(url.pathname);
const isStatic = (url) => url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/') || url.pathname.startsWith('/covers/') || url.pathname.startsWith('/fonts/');

async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const hit = await cache.match(request);
    if (hit) return hit;
    const response = await fetch(request);
    if (response.ok) cache.put(request, response.clone());
    return response;
}

async function networkFirst(request, cacheName, fallback) {
    const cache = await caches.open(cacheName);
    try {
        const response = await fetch(request);
        if (response.ok) cache.put(request, response.clone());
        return response;
    } catch (error) {
        const hit = await cache.match(request);
        if (hit) return hit;
        if (fallback) return (await caches.match(fallback)) || Response.error();
        throw error;
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (isStatic(url)) {
        event.respondWith(cacheFirst(request, STATIC));
    } else if (isPdfFile(url)) {
        // Always the whole file (never a byte range), so it can be kept.
        event.respondWith(cacheFirst(new Request(url.href, { credentials: 'same-origin' }), PDFS));
    } else if (isHighlights(url)) {
        event.respondWith(networkFirst(request, PDFS));
    } else if (request.mode === 'navigate') {
        // Reader pages are kept for offline reading; other pages just fall back to the offline page.
        event.respondWith(isReaderPage(url)
            ? networkFirst(request, PAGES, '/offline')
            : fetch(request).catch(async () => (await caches.match(request)) || caches.match('/offline')));
    }
});

self.addEventListener('message', (event) => {
    if (event.data === 'clear-private') {
        event.waitUntil(Promise.all([caches.delete(PDFS), caches.delete(PAGES)]));
    }
});

/* ---------------- push notifications ---------------- */

self.addEventListener('push', (event) => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch {
        data = { body: event.data?.text() };
    }
    event.waitUntil(self.registration.showNotification(data.title || 'Bookworm', {
        body: data.body || '',
        icon: '/images/app/icon-192.png',
        badge: '/images/app/icon-192.png',
        tag: data.tag || undefined,
        renotify: Boolean(data.tag),
        data: { url: data.url || '/notifications' },
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const target = new URL(event.notification.data?.url || '/', self.location.origin).href;
    event.waitUntil((async () => {
        const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        const open = windows.find((client) => client.url === target) || windows[0];
        if (open) {
            await open.focus();
            if (open.url !== target) return open.navigate(target);
            return undefined;
        }
        return self.clients.openWindow(target);
    })());
});
