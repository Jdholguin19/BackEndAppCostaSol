<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CTG | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../assets/css/style_main.css" rel="stylesheet">
<link href="../assets/css/style_ctg.css" rel="stylesheet">
</head>
<body>

<!-- Header Section -->
<div class="ctg-header">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <div>
      <h1 class="ctg-title">CTG</h1>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="ctg-container">
  <!-- Controls -->
  <div class="ctg-controls">
    <select id="selOrdenacion" class="order-select">
      <option value="fecha">Ordenar por Fecha</option>
      <option value="urgencia">Ordenar por Urgencia</option>
    </select>
    <button class="new-ctg-btn" id="btnNuevo">
      <i class="bi bi-plus"></i>
      Nuevo
    </button>
  </div>

  <!-- Tabs -->
  <div class="ctg-tabs" id="estadoTabs">
    <button class="ctg-tab active" data-id="0">Todos</button>
  </div>

  <!-- List -->
  <div id="ctgList">
    <div class="loading-state">
      <div class="spinner-border"></div>
      <p class="mt-2">Cargando CTGs...</p>
    </div>
  </div>
</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
  <div class="bottom-nav-content">
    <a href="../menu_front.php" class="nav-item">
      <i class="bi bi-house"></i>
      <span>Inicio</span>
    </a>
    <a href="../notificaciones.php" class="nav-item">
      <i class="bi bi-bell"></i>
      <span>Notificaciones</span>
    </a>
    <a href="../citas.php" class="nav-item">
      <i class="bi bi-calendar"></i>
      <span>Cita</span>
    </a>
    <a href="ctg.php" class="nav-item active">
      <i class="bi bi-file-text"></i>
      <span>CTG</span>
    </a>
    <a href="../pqr/pqr.php" class="nav-item">
      <i class="bi bi-chat-dots"></i>
      <span>PQR</span>
    </a>
  </div>
</div>

<script>
/* ---------- constantes ---------- */
const END_EST  = '../../api/ctg/ctg_estados.php';
const END_CTG  = (estado, orderBy) => `../../api/ctg/ctg_list.php?estado_id=${estado}&order_by=${orderBy}`;

/* ---------- referencias DOM ---------- */
const tabs = document.getElementById('estadoTabs');
const list = document.getElementById('ctgList');
const selOrdenacion = document.getElementById('selOrdenacion');
const btnNuevo = document.getElementById('btnNuevo');

// Obtener el token de localStorage
const token = localStorage.getItem('cs_token');

// Verificar si hay token antes de hacer cualquier solicitud a APIs protegidas
if (!token) {
     list.innerHTML = `
        <div class="error-state">
            <div class="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <p class="mt-2">Debes iniciar sesión para ver los CTGs.</p>
            </div>
        </div>`;
} else {

    /* ---------- cargar estados ---------- */
    fetch(END_EST)
      .then(r=>r.json())
      .then(d=>{
          if(!d.ok) return;
          const fragment = document.createDocumentFragment();
          for (let i = 0; i < d.estados.length; i++) {
              const st = d.estados[i];
              const button = document.createElement('button');
              button.className = 'ctg-tab';
              button.setAttribute('data-id', st.id);
              button.textContent = st.nombre;
              fragment.appendChild(button);
          }
          tabs.appendChild(fragment);
      });

    /* ---------- plantilla de tarjeta ---------- */
    function card(p){
       const short  = p.descripcion.length>140 ? p.descripcion.slice(0,137)+'…' : p.descripcion;
       const fecha = new Date(p.fecha_ingreso).toLocaleDateString();
       
       return `
        <div class="ctg-card" onclick="verDetalle(${p.id})">
            <div class="ctg-header-card">
                <h3 class="ctg-subtitle">${p.subtipo || 'CTG'}</h3>
                <span class="ctg-date">${fecha}</span>
            </div>
            <div class="ctg-badges">
                <span class="ctg-badge tipo">${p.tipo}</span>
                <span class="ctg-badge estado ${p.estado.toLowerCase()}">${p.estado}</span>
            </div>
            <p class="ctg-description">${short}</p>
            <div class="ctg-footer">
                <div class="ctg-meta">
                    <i class="bi bi-clock"></i>
                    <span>Última actualización: ${fecha}</span>
                </div>
                <div class="ctg-responses">
                    <span>${p.n_respuestas || 0} respuestas</span>
                </div>
            </div>
        </div>
       `;
    }

    /* ---------- cargar CTGs ---------- */
    function cargarCTGs(estado = 0, orderBy = 'fecha') {
        list.innerHTML = `
            <div class="loading-state">
                <div class="spinner-border"></div>
                <p class="mt-2">Cargando CTGs...</p>
            </div>`;

        fetch(END_CTG(estado, orderBy), {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
          .then(r => {
               if (r.status === 401) {
                    list.innerHTML = `
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
          .then(d=>{
              if(!d.ok) {
                  list.innerHTML = `
                    <div class="error-state">
                        <div class="alert">
                            <i class="bi bi-exclamation-triangle"></i>
                            <p class="mt-2">Error al cargar los CTGs: ${d.mensaje || 'Desconocido'}</p>
                        </div>
                    </div>`;
                  return;
              }
              
              if(d.ctg.length === 0) {
                  list.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-info-circle text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No hay CTGs disponibles</p>
                    </div>`;
                  return;
              }
              
              list.innerHTML = d.ctg.map(card).join('');
          })
           .catch(err => {
               console.error(err);
                if (err !== 'No autorizado') {
                    list.innerHTML = `
                        <div class="error-state">
                            <div class="alert">
                                <i class="bi bi-exclamation-triangle"></i>
                                <p class="mt-2">Error al conectar con el servidor de CTGs</p>
                            </div>
                        </div>`;
                }
           });
    }

    /* ---------- eventos ---------- */
    // Cambio de tab
    tabs.addEventListener('click', e => {
        if(e.target.classList.contains('ctg-tab')) {
            tabs.querySelectorAll('.ctg-tab').forEach(t => t.classList.remove('active'));
            e.target.classList.add('active');
            const estado = e.target.getAttribute('data-id');
            const orderBy = selOrdenacion.value;
            cargarCTGs(estado, orderBy);
        }
    });

    // Cambio de ordenación
    selOrdenacion.addEventListener('change', e => {
        const activeTab = tabs.querySelector('.ctg-tab.active');
        const estado = activeTab ? activeTab.getAttribute('data-id') : 0;
        const orderBy = e.target.value;
        cargarCTGs(estado, orderBy);
    });

    // Botón nuevo
    btnNuevo.addEventListener('click', () => {
        window.location.href = 'ctg_nuevo.php';
    });

    /* ---------- funciones auxiliares ---------- */
    function verDetalle(id) {
        window.location.href = `ctg_detalle.php?id=${id}`;
    }

    /* ---------- carga inicial ---------- */
    cargarCTGs();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>