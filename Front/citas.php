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
include '../api/bottom_nav.php'; 
?>

<script>
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
if(!u.id) location.href='login.php';

// Lógica para el botón dinámico de Agendar
document.addEventListener('DOMContentLoaded', () => {
    const agendarButton = document.querySelector('.agendar-button');
    if (agendarButton && u.is_responsable) {
        agendarButton.href = 'cita_responsable.php';
    }
});

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
  const badge = `<span class="badge badge-estado ${c.estado}" data-cita-id="${c.id}" data-responsable-id="${c.responsable_id}">${c.estado}</span>`;

  let horaMostrada = c.hora;
  if (c.intervalo_minutos) {
    try {
      const [h, m] = c.hora.split(':').map(Number);
      const fechaInicio = new Date();
      fechaInicio.setHours(h, m, 0, 0);
      
      const fechaFin = new Date(fechaInicio.getTime() + c.intervalo_minutos * 60000);
      
      const horaFin = fechaFin.toTimeString().substring(0, 5);
      horaMostrada = `${c.hora} a ${horaFin}`;
    } catch (e) {
      // Fallback en caso de error
      horaMostrada = c.hora;
    }
  }

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

    <div class="card-meta-row">
      <p><i class="bi bi-calendar"></i>${fechaLarga(c.fecha)}</p>
      <p><i class="bi bi-clock"></i>${horaMostrada}</p>
    </div>
    <p><i class="bi bi-geo-alt"></i>${c.proyecto}</p>
    ${c.observaciones ? `<p><i class="bi bi-chat-right-text"></i>${c.observaciones}</p>` : ''}

    <div class="responsable-section">
      <img src="${c.url_foto || 'https://via.placeholder.com/42'}" class="responsable-avatar">
      <span class="responsable-name">${c.responsable}</span>
      ${badge}
    </div>

    ${c.estado==='PROGRAMADO'
      ? `<div class="alert alert-info" style="text-align: justify;">
          <i class="bi bi-info-circle me-2"></i>
          Agradecemos su puntualidad y el compromiso demostrado, ya que ello nos permite garantizarle un servicio de calidad y respeto a su tiempo.
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

// --- Lógica de actualización de estado ---
const wrap = document.getElementById('citasWrap');

function showStatusMenu(target, citaId, responsableId) {
  // Verificar si el usuario es el responsable asignado
  if (!u.is_responsable || u.id != responsableId) {
    return;
  }

  // Eliminar cualquier popup existente
  document.querySelectorAll('.status-popup').forEach(p => p.remove());

  const popup = document.createElement('div');
  popup.className = 'status-popup';

  const statuses = ['PROGRAMADO', 'REALIZADO', 'CANCELADO'];
  statuses.forEach(status => {
    const option = document.createElement('button');
    option.className = 'status-option';
    option.textContent = status;
    option.onclick = () => updateStatus(citaId, status);
    popup.appendChild(option);
  });

  document.body.appendChild(popup);

  // Posicionar el popup
  const rect = target.getBoundingClientRect();
  popup.style.left = `${rect.left}px`;
  popup.style.top = `${rect.bottom + window.scrollY}px`;

  // Cerrar el popup al hacer clic afuera
  setTimeout(() => {
    document.addEventListener('click', function closePopup(event) {
      if (!popup.contains(event.target)) {
        popup.remove();
        document.removeEventListener('click', closePopup);
      }
    });
  }, 0);
}

async function updateStatus(citaId, newStatus) {
  const token = localStorage.getItem('cs_token');
  if (!token) return;

  try {
    const response = await fetch('../api/cita/cita_update_estado.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        cita_id: citaId,
        estado: newStatus
      })
    });

    const data = await response.json();

    if (data.ok) {
      alert('Estado actualizado correctamente.');
      location.reload(); // Recargar para ver el cambio
    } else {
      alert('Error al actualizar el estado: ' + (data.mensaje || 'Error desconocido.'));
    }
  } catch (error) {
    console.error('Error en la solicitud de actualización de estado:', error);
    alert('Error de conexión al intentar actualizar el estado.');
  }
}

wrap.addEventListener('click', (e) => {
  const badge = e.target.closest('.badge-estado');
  if (badge) {
    const citaId = badge.dataset.citaId;
    const responsableId = badge.dataset.responsableId;
    showStatusMenu(badge, citaId, responsableId);
  }
});
</script>
</body>
</html>
