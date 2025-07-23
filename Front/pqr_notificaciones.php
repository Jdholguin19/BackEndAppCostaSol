<!doctype html><html lang="es"><head>
<meta charset="utf-8"><title>Notificaciones PQR</title>

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
</style>
</head><body>

<div class="container py-4">
  <button class="btn btn-link text-dark btn-back" onclick="history.back()">
    <i class="bi bi-arrow-left"></i>
  </button>
  <h1 class="h5 mb-4">Notificaciones de Respuestas PQR</h1>

  <div id="notificationsList">
    <!-- Las notificaciones se cargarán aquí -->
    <div class="text-center text-muted">Cargando notificaciones...</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const notificationsListEl = document.getElementById('notificationsList');

/* ------- obtener notificaciones ------- */
fetch('../api/notificaciones.php')
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      if (d.notificaciones.length > 0) {
        notificationsListEl.innerHTML = d.notificaciones.map(notif => `
          <a href="pqr_detalle.php?id=${notif.pqr_id}" class="notification-card-link">
            <div class="card mb-3">
              <div class="card-body">
                <h6 class="card-title">Nueva respuesta en PQR #${notif.pqr_id}</h6>
                <p class="card-text mb-2">${notif.mensaje}</p>
                <p class="card-subtitle text-muted small">Por: ${notif.usuario} el ${notif.fecha_respuesta}</p>
                 ${notif.url_adjunto ? `<p><a href="${notif.url_adjunto}" target="_blank" onclick="event.stopPropagation();">Ver adjunto</a></p>` : ''} <!-- Evitar que el click en el adjunto active el enlace de la tarjeta -->
              </div>
            </div>
          </a>
        `).join('');
      } else {
        notificationsListEl.innerHTML = '<div class="text-center text-muted">No hay notificaciones</div>';
      }
    } else {
      notificationsListEl.innerHTML = '<div class="alert alert-danger">Error al cargar notificaciones</div>';
    }
  })
  .catch(() => {
    notificationsListEl.innerHTML = '<div class="alert alert-danger">Error al conectar con el servidor de notificaciones</div>';
  });
</script>

</body></html>