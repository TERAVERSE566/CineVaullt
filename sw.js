// CineVault Service Worker — PWA Support
const CACHE_NAME = 'cinevault-v1';
const STATIC_ASSETS = [
  '/moviz/home.php',
  '/moviz/cinevault.css',
  '/moviz/script.js',
  '/moviz/manifest.json'
];

self.addEventListener('install', function(e) {
  e.waitUntil(
    caches.open(CACHE_NAME).then(function(cache) {
      return cache.addAll(STATIC_ASSETS).catch(function() {});
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', function(e) {
  e.waitUntil(
    caches.keys().then(function(keys) {
      return Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)));
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', function(e) {
  // Only cache GET requests to same origin
  if (e.request.method !== 'GET') return;
  if (e.request.url.includes('/api/')) return; // Never cache API calls

  e.respondWith(
    fetch(e.request).catch(function() {
      return caches.match(e.request);
    })
  );
});
