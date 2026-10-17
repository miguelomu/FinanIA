/**
 * Service Worker para FinanIA PWA
 */

const CACHE_NAME = 'finania-v1';
const ASSETS_TO_CACHE = [
  '/FinanIA/',
  '/FinanIA/assets/css/style.css',
  '/FinanIA/assets/js/toast.js',
  '/FinanIA/assets/js/modal.js',
  '/FinanIA/assets/js/buscador.js',
  '/FinanIA/assets/img/pwa/icon-192.png',
  '/FinanIA/assets/img/pwa/icon-512.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE).catch(err => {
        console.log('Error cacheando assets:', err);
      });
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  if (event.request.method !== 'GET' || url.pathname.includes('/api/')) {
    return;
  }

  if (url.pathname.match(/\.(css|js)$/)) {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        return cached || fetch(event.request).then((response) => {
          return caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, response.clone());
            return response;
          });
        });
      })
    );
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        if (response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        return caches.match(event.request).then((cached) => {
          if (cached) return cached;
          
          if (event.request.destination === 'document') {
            return new Response(
              '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Sin conexión</title>' +
              '<style>body{font-family:sans-serif;background:#0a1929;color:#e6edf3;' +
              'display:flex;align-items:center;justify-content:center;min-height:100vh;' +
              'margin:0;text-align:center;padding:20px;}h1{color:#d4af37;font-size:2rem;' +
              'margin-bottom:15px;}p{color:#9fb3c8;line-height:1.6;}</style></head><body>' +
              '<div><h1>📡 Sin conexión</h1><p>No tienes internet.<br>Vuelve a intentar.' +
              '</p><p style="margin-top:20px;"><button onclick="location.reload()" ' +
              'style="padding:12px 24px;background:#d4af37;border:none;border-radius:8px;' +
              'font-weight:bold;cursor:pointer;">Reintentar</button></p></div></body></html>',
              { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
            );
          }
        });
      })
  );
});