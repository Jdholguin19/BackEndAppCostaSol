<?php /* Front/fase_detalle.php */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Etapas de Construcción</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
.accordion-button:not(.collapsed){background:#20805e;color:#fff}
.badge-estado{font-size:.75rem}
.badge-Hecho   {color:#28a745}
.badge-Proceso {color:#ffc107}
.badge-Planificado{color:#6c757d}
.fotos-grid img{width:70px;height:70px;object-fit:cover;border-radius:.25rem;margin:.25rem}
</style>
</head>
<body class="bg-light">

<div class="container py-4">
  <h1 class="h4 mb-4">Etapas de Construcción</h1>

  <div id="accordion" class="accordion"></div>

  <div id="spinner" class="text-center py-5">
    <div class="spinner-border text-success"></div>
  </div>
</div>

<script>
/* ---- obtener parámetros manzana y villa de la URL ---- */
const params = new URLSearchParams(location.search);
const mz = params.get('manzana'); const vl = params.get('villa');
if(!mz || !vl){document.body.innerHTML='<p class="text-danger">Parámetros manzana y villa faltan.</p>'; throw'';}

const ENDPOINT = `../api/etapas_manzana_villa.php?manzana=${encodeURIComponent(mz)}&villa=${encodeURIComponent(vl)}`;

function badge(estado){
  return `<span class="badge-estado badge bg-transparent">${estado}</span>`;
}

fetch(ENDPOINT).then(r=>r.json()).then(d=>{
  document.getElementById('spinner').remove();
  if(!d.ok){document.getElementById('accordion').innerHTML='<p class="text-danger">Error</p>';return;}

  const acc=document.getElementById('accordion');
  d.etapas.forEach((e,i)=>{
    const fotosHTML = e.fotos.length
        ? `<div class="fotos-grid d-flex flex-wrap mt-3">
              ${e.fotos.map(u=>`<a href="${u}" target="_blank"><img src="${u}"></a>`).join('')}
           </div>`
        : '';
    acc.insertAdjacentHTML('beforeend',`
      <div class="accordion-item">
        <h2 class="accordion-header" id="h${i}">
          <button class="accordion-button${i?' collapsed':''}" type="button" data-bs-toggle="collapse"
                  data-bs-target="#c${i}">
            <div class="flex-grow-1">${e.etapa}</div>${badge(e.estado)}
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
}).catch(()=>{document.getElementById('spinner').innerHTML='<p class="text-danger">Error al cargar</p>';});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
