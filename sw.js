const CACHE_NAME = 'zanzibar-safari-v13';
const ASSETS = [
  './',
  './index.html',
  './style.css',
  './script.js',
  './manifest.json',
  './pictures/zanzibarSafarilogo.png'
];

// Install Event
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(ASSETS);
      })
  );
  self.skipWaiting();
});

// Activate Event
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(cacheName => {
          return cacheName !== CACHE_NAME;
        }).map(cacheName => {
          return caches.delete(cacheName);
        })
      );
    }).then(() => {
        return self.clients.claim(); // Take control immediately
    })
  );
});

// Fetch Event
self.addEventListener('fetch', event => {
  // Let the browser handle non-GET requests and analytics tracking normally
  if (event.request.method !== 'GET' || 
      event.request.url.includes('google-analytics') || 
      event.request.url.includes('googletagmanager')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        // Return cached version if found
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Otherwise try fetching from network
        return fetch(event.request).catch(err => {
            console.warn('Network fetch failed gracefully for:', event.request.url);
            // CRITICAL FIX: Always return a valid Response object to prevent "Uncaught Promise" errors
            return new Response('Network request failed. You may be offline.', {
                status: 503,
                statusText: 'Service Unavailable',
                headers: new Headers({ 'Content-Type': 'text/plain' })
            });
        });
      })
  );
});
