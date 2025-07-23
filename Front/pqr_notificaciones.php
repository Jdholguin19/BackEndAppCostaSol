
<!doctype html><html lang="es">
<!-- Notificaciones -->
<head>
<meta charset="utf-8"><title>Notificaciones PQR</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{background:#f5f6f8}
.container{max-width:760px}
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
          <div class="card mb-3">
            <div class="card-body">
              <h6 class="card-title">Nueva respuesta en PQR #${notif.pqr_id}</h6>
              <p class="card-text mb-2">${notif.mensaje}</p>
              <p class="card-subtitle text-muted small">Por: ${notif.usuario} el ${notif.fecha_respuesta}</p>
               ${notif.url_adjunto ? `<p><a href="${notif.url_adjunto}" target="_blank">Ver adjunto</a></p>` : ''}
            </div>
          </div>
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