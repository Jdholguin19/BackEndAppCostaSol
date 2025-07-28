<!doctype html><html lang="es"><head>
<meta charset="utf-8"><title>Citas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{background:#f5f6f8}
.badge-estado{font-size:.8rem}
.badge-estado.PROGRAMADO {background:#ffc107}
.badge-estado.REALIZADO  {background:#198754}
.badge-estado.CANCELADO  {background:#dc3545}
.card-cita img{width:42px;height:42px;border-radius:50%;object-fit:cover}
</style></head><body>

<div class="container py-4">
  <!-- barra -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <button class="btn btn-link" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <h1 class="h4 mb-0">Citas</h1>

    <!-- botón para la siguiente etapa (nuevo agendamiento) -->
    <a href="cita_nueva.php" class="btn btn-primary btn-sm">
      <i class="bi bi-plus"></i> Agendar
    </a>
  </div>

  <h2 class="h6 mb-3">Próximas Citas</h2>
  <div id="citasWrap">
    <div class="text-center py-5"><div class="spinner-border"></div></div>
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
  <div class="card card-cita mb-3 shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <h5 class="mb-1">${c.proposito}</h5>
        <button class="btn btn-link btn-sm text-danger"
                onclick="cancelar(${c.id})">Cancelar</button>
      </div>

      <p class="mb-1"><i class="bi bi-calendar me-1"></i>${fechaLarga(c.fecha)}</p>
      <p class="mb-1"><i class="bi bi-clock me-1"></i>${c.hora}</p>
      <p class="mb-2"><i class="bi bi-geo me-1"></i>${c.proyecto}</p>

      <div class="d-flex align-items-center gap-2 mb-2">
        <img src="${c.url_foto || 'https://via.placeholder.com/42'}">
        <span>${c.responsable}</span>
        ${badge}
      </div>

      ${c.estado==='PROGRAMADO'
        ? `<div class="alert alert-info py-2 small mb-0">
            Su cita está registrada y será confirmada pronto.
           </div>`
        : ''}
    </div>
  </div>`;
}

/* cargar citas */
fetch(`../api/citas_list.php?id_usuario=${u.id}`)
  .then(r=>r.json())
  .then(d=>{
     const wrap = document.getElementById('citasWrap');
     if(!d.ok){ wrap.innerHTML = '<p class="text-danger">Error</p>'; return;}
     if(!d.citas.length){ wrap.innerHTML='<p class="text-muted">No tiene citas agendadas.</p>'; return;}
     wrap.innerHTML = d.citas.map(card).join('');
  });

/* cancelar (solo maqueta) */
function cancelar(id){ alert('Cancelar cita #'+id+' (pendiente)'); }
</script>
</body></html>
