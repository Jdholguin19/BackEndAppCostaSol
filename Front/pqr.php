<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>PQR</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
.badge-dot{display:inline-flex;align-items:center;gap:.25rem}
.badge-dot::before{content:'';width:.55rem;height:.55rem;border-radius:50%}
.badge-dot.proceso::before  {background:#d4ac1d}
.badge-dot.cerrado::before  {background:#1f9d55}
.badge-dot.abierto::before  {background:#0d6efd}

.pqr-thumb{width:56px;height:56px;object-fit:cover;border-radius:.25rem}
.btn-back {padding:.25rem .5rem;font-size:1.25rem;line-height:1}
</style>
</head>
<body class="bg-light">

<div class="container py-4">

  <!-- barra superior -->
  <div class="d-flex align-items-center justify-content-between mb-3">
      <button class="btn btn-link text-dark btn-back" id="btnBack">
        <i class="bi bi-arrow-left"></i>
      </button>

      <h1 class="h4 mb-0 flex-grow-1 text-center">PQR</h1>

      <button class="btn btn-primary btn-sm" id="btnNuevo">
        <i class="bi bi-plus"></i> Nuevo
      </button>
  </div>

  <!-- tabs de estado -->
  <ul class="nav nav-tabs mb-3" id="estadoTabs">
      <li class="nav-item"><button class="nav-link active" data-id="0">Todos</button></li>
  </ul>

  <!-- lista -->
  <div id="pqrList"></div>

</div>

<script>
/* ---------- constantes ---------- */
// Ya no necesitamos UID del localStorage para la API listado; el backend lo obtiene del token
// const UID      = JSON.parse(localStorage.getItem('cs_usuario')||'{}').id || 0;
const END_EST  = '../api/pqr_estados.php';
const END_PQR  = estado => `../api/pqr_list.php?estado_id=${estado}`;

/* ---------- referencias DOM ---------- */
const tabs = document.getElementById('estadoTabs');
const list = document.getElementById('pqrList');

// Obtener el token de localStorage
const token = localStorage.getItem('cs_token');

// Verificar si hay token antes de hacer cualquier solicitud a APIs protegidas
if (!token) {
     list.innerHTML = '<div class="alert alert-warning">Debes iniciar sesión para ver los PQRs.</div>';
     // Opcional: Redirigir a la página de login después de un breve retraso
     // setTimeout(() => { location.href = 'login_front.php'; }, 2000);

} else {

    /* ---------- cargar estados ---------- */
    // Asegúrate de que la API de estados no requiere autenticación o maneja la falta de token
    fetch(END_EST)
      .then(r=>r.json())
      .then(d=>{
          if(!d.ok) return;
          d.estados.forEach(st=>{
              const li=document.createElement('li');li.className='nav-item';
              li.innerHTML=`<button class="nav-link" data-id="${st.id}">${st.nombre}</button>`;
              tabs.appendChild(li);
          });
      });

    /* ---------- plantilla de tarjeta ---------- */
    function card(p){
       const short  = p.descripcion.length>140 ? p.descripcion.slice(0,137)+'…' : p.descripcion;
       const badgeT = `<span class="badge bg-secondary me-1">${p.tipo}</span>`;
       const estadoClass = p.estado.toLowerCase().includes('cerr') ? 'cerrado'
                         : p.estado.toLowerCase().includes('pro')  ? 'proceso'
                         : 'abierto';
       const badgeE = `<span class="badge-dot ${estadoClass}">${p.estado}</span>`;
       const fecha  = new Date(p.fecha_ingreso).toLocaleDateString();
       const thumb  = p.url_problema ? `<img src="${p.url_problema}" class="pqr-thumb me-3">` : '';

       // Mostrar Mz/Villa solo si están presentes
       const mzVilla = (p.manzana || p.villa) ? ` · Mz ${p.manzana} – Villa ${p.villa}` : '';

       return `<div class="card mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <h5 class="card-title mb-1">${p.subtipo}${mzVilla}</h5>
              <small class="text-muted">${fecha}</small>
            </div>
            <p class="mb-1">${badgeT}${badgeE}</p>
            <div class="d-flex">
              ${thumb}
              <p class="card-text small text-muted mb-0">${short}</p>
            </div>
            <div class="text-end small">
              <a href="pqr_detalle.php?id=${p.id}"
                class="link-secondary text-decoration-none">
                ${p.n_respuestas} respuestas
              </a>
            </div>
          </div>
       </div>`;
    }

    /* ---------- cargar lista ---------- */
    function load(estado=0){
        list.innerHTML='<div class="text-center py-5"><div class="spinner-border"></div></div>';

        fetch(END_PQR(estado), {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
          .then(r => {
               if (r.status === 401) {
                    list.innerHTML = '<div class="alert alert-warning">Tu sesión ha expirado o no estás autorizado. Por favor, inicia sesión de nuevo.</div>';
                    // Opcional: Redirigir a la página de login
                    // setTimeout(() => { location.href = 'login_front.php'; }, 2000);
                    return Promise.reject('No autorizado');
                }
                return r.json();
          })
          .then(d=>{
              if(!d.ok){list.innerHTML=`<p class="text-danger">Error al cargar PQRs: ${d.mensaje || 'Desconocido'}</p>`;return;}
              list.innerHTML = d.pqr.length
                  ? d.pqr.map(card).join('')
                  : '<p class="text-muted">— Sin registros —</p>';
          })
           .catch(err => {
               console.error(err);
                if (err !== 'No autorizado') {
                    list.innerHTML = '<div class="alert alert-danger">Error al conectar con el servidor de PQRs</div>';
                }
           });
    }

    /* ---------- eventos ---------- */
    tabs.addEventListener('click',e=>{
        if(!e.target.matches('.nav-link')) return;
        tabs.querySelectorAll('.nav-link').forEach(b=>b.classList.remove('active'));
        e.target.classList.add('active');
        load(e.target.dataset.id);
    });

    /* ---------- navegación ---------- */
    document.getElementById('btnBack').onclick  = () => location.href='menu_front.php';
    document.getElementById('btnNuevo').onclick = () => location.href = 'pqr_nuevo.php';

    /* primera carga */
    load();

}
</script>

</body>
</html>
