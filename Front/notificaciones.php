<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notificaciones</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_notifications.css" rel="stylesheet">
</head>
<body>

<!-- Header Section -->
<div class="notifications-header">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <div>
      <h2 class="notifications-title">Notificaciones</h2>
    </div>
  </div>
</div>


<!-- Main Content -->
<div class="main-content">
  <div class="notifications-container">
    <div id="notificationsList">
      <!-- Las notificaciones se cargarán aquí -->
      <div class="loading-state">
        <div class="spinner-border text-primary"></div>
        <p class="mt-2">Cargando notificaciones...</p>
      </div>
    </div>
  </div>
</div>

<?php 
$active_page = 'notificaciones';
include '../api/bottom_nav.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const notificationsListEl = document.getElementById('notificationsList');

/* ------- obtener notificaciones ------- */
// Obtener el token de localStorage
const token = localStorage.getItem('cs_token');

// Verificar si hay token antes de hacer la solicitud
if (!token) {
    notificationsListEl.innerHTML = `
        <div class="error-state">
            <div class="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <p class="mt-2">Debes iniciar sesión para ver las notificaciones.</p>
            </div>
        </div>`;
} else {
    // Llama a la API de notificaciones global
    fetch('../../api/notificaciones.php', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
    .then(r => {
        if (r.status === 401) {
            notificationsListEl.innerHTML = `
                <div class="error-state">
                    <div class="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p class="mt-2">Tu sesión ha expirado o no estás autorizado. Por favor, inicia sesión de nuevo.</p>
                    </div>
                </div>`;
            return Promise.reject('No autorizado');
        }
        return r.json();
    })
    .then(d => {
        if (d.ok) {
            if (d.notificaciones.length > 0) {
                // Separar notificaciones en leídas y no leídas
                const noLeidas = d.notificaciones.filter(n => !n.leido);
                const leidas = d.notificaciones.filter(n => n.leido);
                
                // Función para generar HTML de notificación
                const generarNotificacion = (notif) => {
                    // --- Lógica para construir el enlace y la presentación dinámicamente ---
                    let detailPageUrl = '#'; // URL por defecto si el tipo es desconocido
                    let cardClass = ''; // Clase CSS opcional para la tarjeta
                    let notificationTitle = 'Nueva respuesta'; // Título base de la notificación
                    let typeClass = '';

                    if (notif.tipo_solicitud === 'CTG') {
                        detailPageUrl = `ctg/ctg_detalle.php?id=${notif.solicitud_id}`;
                        typeClass = 'type-ctg';
                        notificationTitle = 'Respuesta a su CTG';
                    } else if (notif.tipo_solicitud === 'PQR') {
                        detailPageUrl = `pqr/pqr_detalle.php?id=${notif.solicitud_id}`;
                        typeClass = 'type-pqr';
                        notificationTitle = 'Nuevo mensaje a su PQR';
                    } else if (notif.tipo_solicitud === 'Cita') {
                        detailPageUrl = `citas.php`; // Enlace a la lista de citas
                        typeClass = 'type-cita';
                        notificationTitle = 'Nueva Cita Programada';
                    } else if (notif.tipo_solicitud === 'Noticia') {
                        detailPageUrl = `menu_front.php`; // Enlace a la lista de noticias
                        typeClass = 'type-noticia';
                        notificationTitle = 'Nueva Noticia';
                    }
                    // --- FIN Lógica para construir el enlace y la presentación dinámicamente ---

                    // Formatear fecha
                    const fecha = new Date(notif.fecha_respuesta);
                    const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    return `
                        <a href="${detailPageUrl}" class="notification-card ${notif.leido ? 'read' : 'unread'}" data-notification-id="${notif.id}" data-notification-type="notificacion">
                            <div class="notification-header">
                                <h3 class="notification-title">
                                    ${notificationTitle}
                                    ${notif.manzana && notif.villa ? `<br>- Mz ${notif.manzana} - Villa ${notif.villa} -` : ''}
                                </h3>
                                <span class="notification-type ${typeClass}">${notif.tipo_solicitud}</span>
                            </div>
                            <div class="notification-body">
                                <p class="notification-message">${notif.mensaje}</p>
                                ${(() => {
                                    if (!notif.url_adjunto) return '';

                                    // Check if the attachment is an image by extension
                                    if (/\.(jpeg|jpg|gif|png)$/i.test(notif.url_adjunto)) {
                                        return `
                                        <div class="notification-attachment-image">
                                            <img src="${notif.url_adjunto}" alt="Adjunto">
                                        </div>`;
                                    } else {
                                        // For non-image files, show nothing.
                                        return '';
                                    }
                                })()}
                            </div>
                            <div class="notification-meta">
                                <span class="notification-user">Por: ${notif.usuario}</span>
                                <span class="notification-date">${fechaFormateada}</span>
                            </div>
                        </a>
                    `;
                };

                // Construir HTML con las dos secciones
                let htmlContent = '';

                // Sección de notificaciones no leídas
                if (noLeidas.length > 0) {
                    htmlContent += '<div class="unread-section">';
                    htmlContent += '<h4 class="section-title">📬 No Leídas</h4>';
                    htmlContent += noLeidas.map(generarNotificacion).join('');
                    htmlContent += '</div>';
                }

                // Sección de notificaciones leídas
                if (leidas.length > 0) {
                    htmlContent += '<div class="read-section">';
                    htmlContent += '<h4 class="section-title">📭 Leídas</h4>';
                    htmlContent += leidas.map(generarNotificacion).join('');
                    htmlContent += '</div>';
                }

                notificationsListEl.innerHTML = htmlContent;
                
                // Agregar event listeners para marcar notificaciones como leídas
                document.querySelectorAll('.notification-card').forEach(card => {
                    card.addEventListener('click', async (event) => {
                        event.preventDefault(); // Prevenir navegación inmediata
                        
                        const notifId = card.getAttribute('data-notification-id');
                        const notifType = card.getAttribute('data-notification-type');
                        let href = card.getAttribute('href');
                        
                        // Si la URL destino es menu_front.php, agregar parámetros para marcar como leída
                        if (href.includes('menu_front.php')) {
                            const separator = href.includes('?') ? '&' : '?';
                            href += separator + `notif_id=${notifId}&notif_type=${notifType}`;
                        }
                        
                        // Navegar a la página (con parámetros si es necesario)
                        if (href && href !== '#') {
                            window.location.href = href;
                        }
                    });
                });
            } else {
                notificationsListEl.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-bell"></i>
                        <p>No hay notificaciones</p>
                    </div>`;
            }
        } else {
            notificationsListEl.innerHTML = `
                <div class="error-state">
                    <div class="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p class="mt-2">Error al cargar notificaciones: ${d.mensaje || 'Error desconocido'}</p>
                    </div>
                </div>`;
        }
    })
    .catch(err => {
        console.error(err);
        if (err !== 'No autorizado') {
             notificationsListEl.innerHTML = `
                <div class="error-state">
                    <div class="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p class="mt-2">Error al conectar con el servidor de notificaciones</p>
                    </div>
                </div>`;
        }
    });
}
</script>

</body>
</html>