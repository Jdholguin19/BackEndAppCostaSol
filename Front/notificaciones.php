<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Notificaciones</title> <!-- Título más genérico -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{background:#f5f6f8}
.container{max-width:760px}
/* Estilo para que la tarjeta de notificación se vea como un enlace */
.notification-card-link {
    text-decoration: none;
    color: inherit; /* Heredar el color del texto */
    display: block; /* Hacer que todo el enlace sea clickeable */
}
.notification-card-link .card {
    transition: transform .2s ease-in-out; /* Efecto suave al pasar el mouse */
}
.notification-card-link:hover .card {
    transform: translateY(-3px); /* Pequeño levantamiento al pasar el mouse */
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; /* Sombra sutil */
}

/* Estilos opcionales para diferenciar notificaciones por tipo */
.card-ctg { border-left: 5px solid #0d6efd; } /* Ejemplo: borde azul para CTG */
.card-pqr { border-left: 5px solid #d4ac1d; } /* Ejemplo: borde amarillo para PQR */

</style>
</head>
<body>

<div class="container py-4">
  <!-- Asumiendo que tienes un menú principal o una forma de volver -->
  <button class="btn btn-link text-dark btn-back" onclick="history.back()">
    <i class="bi bi-arrow-left"></i>
  </button>
  <h1 class="h5 mb-4">Mis Notificaciones</h1> <!-- Título claro -->

  <div id="notificationsList">
    <!-- Las notificaciones se cargarán aquí -->
    <div class="text-center text-muted">Cargando notificaciones...</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const notificationsListEl = document.getElementById('notificationsList');

/* ------- obtener notificaciones ------- */
// Obtener el token de localStorage
const token = localStorage.getItem('cs_token');

// Verificar si hay token antes de hacer la solicitud
if (!token) {
    notificationsListEl.innerHTML = '<div class="alert alert-warning">Debes iniciar sesión para ver las notificaciones.</div>';
} else {
    // Llama a la API de notificaciones global
    fetch('../../api/notificaciones.php', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
    .then(r => {
        if (r.status === 401) {
            notificationsListEl.innerHTML = '<div class="alert alert-warning">Tu sesión ha expirado o no estás autorizado. Por favor, inicia sesión de nuevo.</div>';
            // Opcional: Redirigir a la página de login
            // window.location.href = '../login_front.php'; // Ajusta la ruta si es necesario
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

                    if (notif.tipo_solicitud === 'CTG') {
                        detailPageUrl = `ctg/ctg_detalle.php?id=${notif.solicitud_id}`;
                        cardClass = 'card-ctg'; // Añadir una clase específica de CSS
                        notificationTitle = 'Nueva respuesta a CTG';
                    } else if (notif.tipo_solicitud === 'PQR') {
                        detailPageUrl = `pqr/pqr_detalle.php?id=${notif.solicitud_id}`;
                        cardClass = 'card-pqr'; // Añadir una clase específica de CSS
                        notificationTitle = 'Nueva respuesta a PQR';
                    }
                    // --- FIN Lógica para construir el enlace y la presentación dinámicamente ---

                    // Si en el futuro añades otro tipo (ej: 'VISITA'):
                    /*
                    else if (notif.tipo_solicitud === 'VISITA') {
                         detailPageUrl = `../visitas/visita_detalle.php?id=${notif.solicitud_id}`;
                         cardClass = 'card-visita';
                         notificationTitle = 'Actualización de Visita';
                    }
                    */


                    return `
                        <a href="${detailPageUrl}" class="notification-card-link">
                          <div class="card mb-3 ${cardClass}"> <!-- Añadir clase dinámica -->
                            <div class="card-body">
                              <h6 class="card-title">${notificationTitle} de Mz ${notif.manzana} - Villa ${notif.villa}</h6>
                              <p class="card-text mb-2">${notif.mensaje}</p>
                              <p class="card-subtitle text-muted small">Por: ${notif.usuario} el ${notif.fecha_respuesta}</p>
                               ${notif.url_adjunto ? `<p><a href="${notif.url_adjunto}" target="_blank" onclick="event.stopPropagation();">Ver adjunto</a></p>` : ''} <!-- Evitar que el click en el adjunto active el enlace de la tarjeta -->
                            </div>
                          </div>
                        </a>
                    `;
                }).join('');
            } else {
                notificationsListEl.innerHTML = '<div class="text-center text-muted">No hay notificaciones</div>';
            }
        } else {
            notificationsListEl.innerHTML = `<div class="alert alert-danger">Error al cargar notificaciones: ${d.mensaje || 'Error desconocido'}</div>`;
        }
    })
    .catch(err => {
        console.error(err);
        if (err !== 'No autorizado') {
             notificationsListEl.innerHTML = '<div class="alert alert-danger">Error al conectar con el servidor de notificaciones</div>';
        }
    });
}

// Función para volver - asume que hay una forma de volver en tu aplicación (ej: un menú)
// Si no usas history.back(), ajusta esto o elimina el botón de volver si no aplica
// document.querySelector('.btn-back').addEventListener('click', () => { history.back(); });


</script>

</body></html>