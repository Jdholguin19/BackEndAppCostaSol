importScripts('https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js');

// Escucha el evento 'notificationclick'
self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click Received.', event);

    // Cierra la notificacixc3xb3n
    event.notification.close();

    // Obtiene la URL a la que quieres redirigir
    // Cambiamos la URL para que apunte a menu_front.php localmente
    const targetUrl = 'http://localhost/Front/menu_front.php'; // *** MODIFICADO PARA LOCALHOST ***

    // Abre una nueva ventana o pestaxc3xb1a con la URL de destino
    event.waitUntil(
        clients.openWindow(targetUrl)
    );
});