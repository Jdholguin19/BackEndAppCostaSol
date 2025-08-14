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
include 'includes/bottom_nav.php'; 
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
                notificationsListEl.innerHTML = d.notificaciones.map(notif => {
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
                        <a href="${detailPageUrl}" class="notification-card">
                            <div class="notification-header">
                                <h3 class="notification-title">${notificationTitle} <br>- Mz ${notif.manzana} - Villa ${notif.villa} - </h3>
                                <span class="notification-type ${typeClass}">${notif.tipo_solicitud}</span>
                            </div>
                            <p class="notification-message">${notif.mensaje}</p>
                            ${notif.url_adjunto ? `
                                <div class="notification-attachment">
                                    <a href="${notif.url_adjunto}" target="_blank" onclick="event.stopPropagation();">
                                        <i class="bi bi-paperclip"></i> Ver adjunto
                                    </a>
                                </div>
                            ` : ''}
                            <div class="notification-meta">
                                <span class="notification-user">Por: ${notif.usuario}</span>
                                <span class="notification-date">${fechaFormateada}</span>
                            </div>
                        </a>
                    `;
                }).join('');
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