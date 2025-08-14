<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Citas | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">
<link href="assets/css/style_citas.css" rel="stylesheet">
</head>
<body>

<!-- Header Section -->
<div class="citas-header">
<hr style="color:rgba(69, 67, 67, 0); margin-top: 1px; "></hr>
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <div>
      <h1 class="citas-title">Citas</h1>
    </div>
    <a href="cita_nueva.php" class="agendar-button">
      <i class="bi bi-plus"></i>
      Agendar
    </a>
  </div>
  <hr style="color:rgba(69, 67, 67, 0); margin-top: 1px; "></hr>
</div>

<!-- Main Content -->
<div class="container">
  <h2 class="section-title">Próximas Citas</h2>
  <div id="citasWrap">
    <div class="loading-container">
      <div class="spinner-border"></div>
    </div>
  </div>
</div>

<?php 
$active_page = 'citas';
include 'includes/bottom_nav.php'; 
?>

<script>
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
if(!u.id) location.href='login.php';

function fechaLarga(sqlDate){
  const [y,m,d] = sqlDate.split('-').map(Number);      // y=2025, m=06, d=30
  return new Date(y, m - 1, d)                         // local => sin corrimiento
         .toLocaleDateString('es-EC',{
           weekday:'long', year:'numeric',
           month:'long',  day:'numeric'
         });
}

/* plantilla */
function card(c){
  const badge = `<span class="badge badge-estado ${c.estado}">${c.estado}</span>`;
  return `
  <div class="card card-cita">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <h5>${c.proposito}</h5>
      <div>
        ${c.estado==='PROGRAMADO'
          ? `<button class="cancel-button"
                    onclick="cancelar(${c.id})">
              <i class="bi bi-x-circle"></i> Cancelar
            </button>`
          : ''}
        ${c.estado==='CANCELADO'
          ? `<button class="delete-button"
                    onclick="eliminarCita(${c.id})">
              <i class="bi bi-trash"></i> Eliminar
            </button>`
          : ''}
      </div>
    </div>

    <p><i class="bi bi-calendar"></i>${fechaLarga(c.fecha)}</p>
    <p><i class="bi bi-clock"></i>${c.hora}</p>
    <p><i class="bi bi-geo-alt"></i>${c.proyecto}</p>

    <div class="responsable-section">
      <img src="${c.url_foto || 'https://via.placeholder.com/42'}" class="responsable-avatar">
      <span class="responsable-name">${c.responsable}</span>
      ${badge}
    </div>

    ${c.estado==='PROGRAMADO'
      ? `<div class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>
          Su cita está registrada y será confirmada pronto.
         </div>`
      : ''}
  </div>`;
}

/* cargar citas */
let apiUrl = '';
if (u.is_responsable && u.id === 3) {
    apiUrl = `../api/cita/citas_list.php?rol=admin_responsable`;
} else if (u.is_responsable) {
    apiUrl = `../api/cita/citas_list.php?rol=responsable&id_responsable=${u.id}`;
} else {
    apiUrl = `../api/cita/citas_list.php?rol=usuario&id_usuario=${u.id}`;
}
fetch(apiUrl)
  .then(r=>r.json())
  .then(d=>{
     const wrap = document.getElementById('citasWrap');
     if(!d.ok){ 
       wrap.innerHTML = '<div class="error-state"><i class="bi bi-exclamation-triangle me-2"></i>Error al cargar las citas</div>'; 
       return;
     }
     if(!d.citas.length){ 
       wrap.innerHTML='<div class="empty-state"><i class="bi bi-calendar-x me-2"></i>No tiene citas agendadas.</div>'; 
       return;
     }
     wrap.innerHTML = d.citas.map(card).join('');
  });

/* cancelar cita */
async function cancelar(idCita){
  if (!confirm('¿Está seguro que desea cancelar esta cita?')) {
    return;
  }

  const idUsuario = u.id; // Obtener el ID del usuario del objeto 'u' global
  let requestBody = `id_cita=${idCita}&id_usuario=${idUsuario}`;
  if (u.is_responsable && u.id === 3) {
    requestBody += `&is_admin_responsible=true`;
  }

  try {
    const response = await fetch('../api/cita/cita_cancelar.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: requestBody
    });

    const data = await response.json();

    if (data.ok) {
      alert('Cita cancelada exitosamente.');
      // Opcional: Recargar las citas o eliminar la tarjeta de la cita cancelada del DOM
      // Para simplificar, recargaremos la página para que la lista se actualice
      location.reload();
    } else {
      alert('Error al cancelar la cita: ' + (data.message || 'Error desconocido.'));
    }
  } catch (error) {
    console.error('Error en la solicitud de cancelación:', error);
    alert('Error de conexión al intentar cancelar la cita.');
  }
}

/* eliminar cita */
async function eliminarCita(idCita){
  if (!confirm('¿Está seguro que desea eliminar esta cita permanentemente?')) {
    return;
  }

  const idUsuario = u.id; // Obtener el ID del usuario del objeto 'u' global
  let requestBody = `id_cita=${idCita}&id_usuario=${idUsuario}`;
  if (u.is_responsable && u.id === 3) {
    requestBody += `&is_admin_responsible=true`;
  }

  try {
    const response = await fetch('../api/cita/cita_eliminar.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: requestBody
    });

    const data = await response.json();

    if (data.ok) {
      alert('Cita eliminada exitosamente.');
      location.reload();
    } else {
      alert('Error al eliminar la cita: ' + (data.message || 'Error desconocido.'));
    }
  } catch (error) {
    console.error('Error en la solicitud de eliminación:', error);
    alert('Error de conexión al intentar eliminar la cita.');
  }
}
</script>
</body>
</html>
