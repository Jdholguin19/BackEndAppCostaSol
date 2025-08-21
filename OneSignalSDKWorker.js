importScripts('https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js');

// Evento push para manejar notificaciones entrantes
self.addEventListener('push', event => {
  console.log('Service Worker: Push recibido', event);

  const data = event.data.json(); // OneSignal envía los datos como JSON
  const title = data.headings ? data.headings.es : 'Nueva Notificación'; // Título en español
  const options = {
    body: data.contents ? data.contents.es : 'Mensaje de notificación', // Contenido en español
    icon: data.icon || '/imagenes/icons/icon-192x192.png', // Icono (usa uno por defecto si no viene)
    data: data.data // Datos adicionales (como ctg_id o pqr_id)
  };

  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

// Evento click en la notificación
self.addEventListener('notificationclick', event => {
  console.log('Service Worker: Click en notificación', event);

  event.notification.close(); // Cierra la notificación

  const notificationData = event.notification.data;
  const baseUrl = 'https://app.costasol.com.ec';
  
  // Lógica de redirección mejorada
  let targetUrl = `${baseUrl}/Front/menu_front.php`; // URL por defecto

  if (notificationData) {
    if (notificationData.ctg_id) {
      targetUrl = `${baseUrl}/Front/ctg/ctg_detalle.php?id=${notificationData.ctg_id}`;
    } else if (notificationData.pqr_id) {
      targetUrl = `${baseUrl}/Front/pqr/pqr_detalle.php?id=${notificationData.pqr_id}`;
    }
  }

  event.waitUntil(clients.openWindow(targetUrl));
});
