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
<link href="//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.css" type="text/css" rel="stylesheet" />

<link href="assets/css/style_fdetale.css" rel="stylesheet">

</head>
<body>

<!-- Header Section -->
<hr style="color:rgba(69, 67, 67, 0.33); margin-top: 25px; ">
<div class="fase-header">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <div>
      <h2 class="fase-title">Etapas de Construcción</h2>
      <p class="fase-subtitle" id="faseSubtitle">Cargando...</p>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="stages-container">
    <div id="stages-list"></div>
    
    <div id="spinner" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>
  </div>
</div>

<?php 
$active_page = 'inicio';
include '../api/bottom_nav.php'; 
?>

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

function createStageCard(stage, index) {
    const photosHTML = stage.fotos.length > 0 
        ? `<div class="stage-photos">
             ${stage.fotos.map(url => `<a href="${url}" data-featherlight="image"><img src="${url}" alt="Foto de construcción"></a>`).join('')}
           </div>`
        : '';

    return `
        <div class="stage-card" onclick="toggleStage(${index})">
            <div class="stage-header">
                <h3 class="stage-name">${stage.etapa}</h3>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="stage-status status-${stage.estado}">${stage.estado}</span>
                    <i class="bi bi-chevron-down chevron-icon"></i>
                </div>
            </div>
            <div class="stage-content" id="content-${index}">
                <p class="stage-description">${stage.descripcion || 'Sin descripción disponible'}</p>
                ${photosHTML}
            </div>
        </div>
    `;
}

function toggleStage(index) {
    const card = document.querySelector(`#stages-list .stage-card:nth-child(${index + 1})`);
    const content = document.getElementById(`content-${index}`);
    const chevron = card.querySelector('.chevron-icon');
    
    if (content.classList.contains('show')) {
        content.classList.remove('show');
        card.classList.remove('expanded');
    } else {
        content.classList.add('show');
        card.classList.add('expanded');
    }
}

fetch(ENDPOINT).then(r=>r.json()).then(d=>{
  document.getElementById('spinner').remove();
  
  // Debug: mostrar los datos recibidos
  console.log('Datos recibidos del API:', d);
  
  if(!d.ok){
    document.getElementById('stages-list').innerHTML='<p class="text-danger">Error al cargar las etapas</p>';
    return;
  }

  const stagesList = document.getElementById('stages-list');
  
  d.etapas.forEach((stage, index) => {
    console.log(`Etapa ${index}: ${stage.etapa} - Porcentaje: ${stage.porcentaje} - Estado: ${stage.estado}`);
    stagesList.insertAdjacentHTML('beforeend', createStageCard(stage, index));
  });

  // Inicializar Featherlight después de que el contenido se ha cargado
  // Esto asegura que Featherlight encuentre los elementos con data-featherlight

}).catch((error) => {
  console.error('Error al cargar etapas:', error);
  document.getElementById('spinner').innerHTML = '<p class="text-danger">Error al cargar las etapas</p>';
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//code.jquery.com/jquery-latest.js"></script> <!-- jQuery primero -->
    <script src="//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script> <!-- Featherlight después -->
</body>
</html>