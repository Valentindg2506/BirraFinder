const CACHE_NAME = "bartracker-v1";
const urlsToCache = [
  "./",
  "./index.php",
  "./css/style.css",
  "./js/map.js"
];

// 1. Instalación: Guardamos los archivos estáticos
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log("Archivos cacheados");
      return cache.addAll(urlsToCache);
    })
  );
});

// 2. Activación: Limpiamos cachés viejas
self.addEventListener("activate", (event) => {
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
});

// 3. Fetch: Estrategia "Network First" (Internet primero, caché si falla)
// Esto es vital para apps con bases de datos como la tuya.
self.addEventListener("fetch", (event) => {
  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Si hay internet, devolvemos la respuesta fresca
        return response;
      })
      .catch(() => {
        // Si NO hay internet, intentamos servir lo que haya en caché
        return caches.match(event.request);
      })
  );
});