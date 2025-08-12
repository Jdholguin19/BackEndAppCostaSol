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

<!-- Bottom Navigation -->
<div class="bottom-nav">
  <div class="bottom-nav-content">
    <a href="menu_front.php" class="nav-item">
      <i class="bi bi-house"></i>
      <span>Inicio</span>
    </a>
    <a href="notificaciones.php" class="nav-item">
      <i class="bi bi-bell"></i>
      <span>Notificaciones</span>
    </a>
    <a href="citas.php" class="nav-item active">
      <i class="bi bi-calendar"></i>
      <span>Cita</span>
    </a>
    <a href="ctg/ctg.php" class="nav-item">
      <i class="bi bi-file-text"></i>
      <span>CTG</span>
    </a>
    <a href="pqr/pqr.php" class="nav-item">
      <i class="bi bi-chat-dots"></i>
      <span>PQR</span>
    </a>
  </div>
</div>

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
      <button class="cancel-button"
              onclick="cancelar(${c.id})">
        <i class="bi bi-x-circle"></i> Cancelar
      </button>
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
fetch(`../api/citas_list.php?id_usuario=${u.id}`)
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

/* cancelar (solo maqueta) */
function cancelar(id){ alert('Cancelar cita #'+id+' (pendiente)'); }
</script>
</body>
</html>
