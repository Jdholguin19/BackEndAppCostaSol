// service-worker.js

// Evento de instalación (no se guarda nada en caché)
self.addEventListener("install", event => {
    console.log("Service Worker instalado");
    self.skipWaiting(); // Activa inmediatamente el nuevo SW
  });
  
  // Evento de activación
  self.addEventListener("activate", event => {
    console.log("Service Worker activado");
    return self.clients.claim(); // Reclama control de las páginas
  });
  
  // Evento de fetch (solo pasa las peticiones directamente a la red)
  self.addEventListener("fetch", event => {
    // No interceptamos ni cacheamos, todo va a la red
  });
  