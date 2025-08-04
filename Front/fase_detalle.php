<?php /* Front/fase_detalle.php */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Etapas de Construcción</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">

<style>
    /* Specific styles for fase_detalle */
    .accordion-button:not(.collapsed) {
      
        background: #39a452 !important;
        color: #fff !important;
    }

    .badge-estado {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
    }

    .badge-Hecho {
        background-color:rgb(0, 255, 60) !important;
        color: #155724 !important;
    }

    .badge-Proceso {
        background-color:rgb(255, 196, 0) !important;
        color: #856404 !important;
    }

    .badge-Planificado {
        background-color:rgb(0, 128, 255) !important;
        color: #6c757d !important;
    }

    .fotos-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .fotos-grid img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .fase-header {
        background: #ffffff;
        padding: 1rem 1.5rem 0.5rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .fase-title {
        font-family: 'Playfair Display';
        font-size: 1.2rem;
        font-weight: 600;
        color: #2d5a3d;
        margin: 0;
        text-align: center;
    }

    .fase-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0.5rem 0 0 0;
        text-align: center;
    }

    .accordion-item {
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        margin-bottom: 0.5rem;
        overflow: hidden;
    }

    .accordion-button {
        font-family: 'Playfair Display';
        background: #ffffff;
        border: 5px solid rgba(199, 197, 197, 0.21);
        border-radius: 12px;

        padding: 1rem 1.5rem;
        font-weight: 600;
        color: #1a1a1a;
        transition: all 0.2s ease;
    }

    .accordion-button:not(.collapsed) {
        box-shadow: none;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color:rgb(164, 210, 180);
    }

    .accordion-body {
        font-family: 'Playfair Display';
        padding: 1rem 1.5rem;
        background: #f9fafb;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .accordion-body p {
        font-family: 'Playfair Display';
        margin-bottom: 0.5rem;
        color: #6b7280;
    }

    .back-button {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #2d5a3d;
        font-size: 1.25rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 50%;
        transition: background-color 0.2s ease;
    }

    .back-button:hover {
        background-color: #f3f4f6;
    }
</style>
</head>
<body>

<!-- Header Section -->
<div class="fase-header">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <div>
      <h2 class="fase-title">Vorver</h1>
      <p></p>

    </div>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div>
      <h2 class="fase-title">Etapas de Construcción</h1>
      <p class="fase-subtitle" id="faseSubtitle">Cargando...
        
      </p>
    </div>

  <div id="accordion"></div>

  <div id="spinner" class="text-center py-5">
    <div class="spinner-border"></div>
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
    <a href="citas.php" class="nav-item">
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
/* ---- obtener parámetros manzana y villa de la URL ---- */
const params = new URLSearchParams(location.search);
const mz = params.get('manzana'); 
const vl = params.get('villa');

if(!mz || !vl){
    document.body.innerHTML='<p class="text-danger">Parámetros manzana y villa faltan.</p>'; 
    throw '';
}

// Update subtitle with property info
document.getElementById('faseSubtitle').textContent = `Manzana ${mz} - Villa ${vl}`;

const ENDPOINT = `../api/etapas_manzana_villa.php?manzana=${encodeURIComponent(mz)}&villa=${encodeURIComponent(vl)}`;

function badge(estado){
  return `<span class="badge-estado badge bg-transparent">${estado}</span>`;
}

fetch(ENDPOINT).then(r=>r.json()).then(d=>{
  document.getElementById('spinner').remove();
  if(!d.ok){
    document.getElementById('accordion').innerHTML='<p class="text-danger">Error al cargar las etapas</p>';
    return;
  }

  const acc = document.getElementById('accordion');
  d.etapas.forEach((e,i)=>{
    const fotosHTML = e.fotos.length
        ? `<div class="fotos-grid">
              ${e.fotos.map(u=>`<a href="${u}" target="_blank"><img src="${u}" alt="Foto de construcción"></a>`).join('')}
           </div>`
        : '';
        
    acc.insertAdjacentHTML('beforeend',`
      <div class="accordion-item">
        <h2 class="accordion-header" id="h${i}">
          <button class="accordion-button${i?' collapsed':''}" type="button" data-bs-toggle="collapse"
                  data-bs-target="#c${i}">
            <div class="flex-grow-1">${e.etapa}</div>${badge(e.estado)}<i style="color: #39a452;" class="bi bi-chevron-down"></i>
          </button>
        </h2>
        <div id="c${i}" class="accordion-collapse collapse${i?'':' show'}" data-bs-parent="#accordion">
          <div class="accordion-body">
            <p class="mb-2">${e.descripcion||'– Sin descripción –'}</p>
            ${fotosHTML}
          </div>
        </div>
      </div>`);
  });
}).catch(()=>{
  document.getElementById('spinner').innerHTML='<p class="text-danger">Error al cargar las etapas</p>';
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
