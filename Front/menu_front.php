<?php /* Front/menu_front.php */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Menú principal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* ---- Welcome ---- */
.welcome-card{background:#fff;border-radius:.75rem;padding:1rem 1.25rem;
  box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;align-items:center;gap:.75rem}
.avatar{width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid #20805e}
.welcome-name{font-weight:600;margin:0}
.welcome-mail{font-size:.8rem;color:#6c757d;margin:0}

/* ---- General cards ---- */
.menu-card{cursor:pointer;transition:.15s;box-shadow:0 2px 8px rgba(0,0,0,.08)}
.menu-card:hover{transform:translateY(-4px);box-shadow:0 4px 14px rgba(0,0,0,.12)}
.menu-icon{font-size:2.2rem;color:#20805e}

/* ---- Noticias ---- */
.news-thumb{width:140px;height:140px;object-fit:cover;border-radius:.25rem 0 0 .25rem}
@media (max-width:767.98px){.news-thumb{width:100%;height:180px;border-radius:.25rem .25rem 0 0}}
.carousel-indicators.outside{position:relative;margin-top:.5rem}

/* ---- Propiedades tabs ---- */
#propTabs .nav-link{border-radius:999px;font-size:.85rem;padding:.35rem 1rem}
#propTabs .nav-link.active{background:#20805e;color:#fff}

/* ---- Avance circular ---- */
.progress-ring{width:140px;height:140px}
.progress-ring text{font-size:2.5rem;font-weight:600;fill:#0f4833;text-anchor:middle;dominant-baseline:central}
</style>
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "e77613c2-51f8-431d-9892-8b2463ecc817",
      safari_web_id: "web.onesignal.auto.5130fec1-dc87-4e71-b719-29a6a70279c4",
      notifyButton: {
        enable: true,
      },
    });
  });
</script>
</head>
<body class="bg-light">

<div class="container py-4">

  <!-- Bienvenida -->
  <div id="welcomeRow" class="row mb-4"></div>


  <!-- Comunicados -->
  <div id="newsRow" class="row g-4 mb-5">
    <div class="d-flex justify-content-center py-5" id="newsSpinner">
      <div class="spinner-border text-success"></div>
    </div>
  </div>

  <!-- Propiedades -->
  <div id="propRow" class="row" style="display:none;">
    <div class="col-12 col-lg-10 mx-auto">
      <ul class="nav nav-pills justify-content-center mb-3" id="propTabs"></ul>
      <div class="tab-content" id="propContent"></div>

      <!-- Tarjeta Avance -->
      <div id="avanceCard" class="menu-card bg-white rounded-3 p-4 mb-5" style="display:none;"></div>
    </div>
  </div>

  <h2 class="h4 text-center mb-4">¿Qué deseas hacer hoy?</h2>

  <!-- Menú -->
  <div class="row g-4" id="menuGrid">
    <div class="d-flex justify-content-center py-5" id="menuSpinner">
      <div class="spinner-border text-success"></div>
    </div>
  </div>
</div>

<script>
/* ---------- USER ---------- */
console.log('Valor en localStorage para cs_usuario:', localStorage.getItem('cs_usuario')); // Registro
const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
if (!u.id) location.href = 'login_front.php';

/* ---------- Endpoints ---------- */
const API_NEWS  = '../api/noticias.php?limit=10';
const API_MENU  = '../api/menu.php?role_id=' + u.rol_id;
const API_PROP  = '../api/obtener_propiedades.php?id_usuario=' + u.id;
const API_FASE  = '../api/propiedad_fase.php?id_propiedad=';

/* ---------- Welcome ---------- */
document.getElementById('welcomeRow').innerHTML = `
  <div class="col-12 col-lg-10 mx-auto">
    <div class="welcome-card">
      <img src="${u.url_foto_perfil || 'https://via.placeholder.com/48'}" class="avatar" alt="">
      <div>
        <p class=\"welcome-name\">Hola, ${u.nombres || u.nombre}</p>
        <p class="welcome-mail">${u.correo}</p>
        <button onclick="logout()" class="btn btn-outline-danger">Cerrar Sesión</button>
      </div>
    </div>
  </div>`;

/* ---------- Helpers ---------- */
function icon(url){
  if (url?.startsWith('bi-')) {
    const i = document.createElement('i');
    i.className = `menu-icon bi ${url}`; return i;
  }
  const img = document.createElement('img');
  img.src = url || '/assets/img/default.svg'; img.width = 40; return img;
}
function svgCircle(percent){
  const r = 60, c = 2 * Math.PI * r, off = c - (percent/100) * c;
  return `
  <div class="progress-ring">
    <svg width="140" height="140" viewBox="0 0 140 140">
      <!-- giramos solo las circunferencias -->
      <g transform="rotate(-90 70 70)">
        <circle cx="70" cy="70" r="${r}" stroke="#e7eff1" stroke-width="12" fill="none"/>
        <circle cx="70" cy="70" r="${r}" stroke="#46a06c" stroke-width="12" fill="none"
                stroke-dasharray="${c}" stroke-dashoffset="${off}" stroke-linecap="round"/>
      </g>
      <!-- texto sin rotar -->
      <text x="70" y="74">${percent}<tspan font-size="20">%</tspan></text>
    </svg>
  </div>`;
}

function openModule(menu){
  // Si el nombre es PQR lanzamos pqr.php
  if ((menu.nombre || '').trim().toUpperCase() === 'PQR'){
      location.href = 'pqr.php';               // pqr.php leerá el usuario desde localStorage
      return;
  }

  if ( menu.id === 3 ||
       (menu.nombre || '').toUpperCase().includes('VISITA') ){
      location.href = 'citas.php';          // <<<<<<
      return;
  }

    if ( menu.id === 7 ||
       (menu.nombre || '').toUpperCase().includes('VISITA') ){
      location.href = 'panel_calendario.php';          // <<<<<<
      return;
  }

    if ( menu.id === 8 ||
       (menu.nombre || '').toUpperCase().includes('VISITA') ){
      location.href = 'pqr_notificaciones.php';          // <<<<<<
      return;
  }  


  // …aquí podrías despachar otros módulos por nombre o id
  alert('Abrir módulo '+menu.id);
}

/* ---------- Noticias ---------- */
fetch(API_NEWS).then(r=>r.json()).then(({ok,noticias})=>{
  if(!ok){document.getElementById('newsSpinner').textContent='Error noticias';return;}
  const row=document.getElementById('newsRow');row.innerHTML='';
  const wrap=document.createElement('div');wrap.className='col-12 col-lg-10 mx-auto';
  wrap.innerHTML = `
    <div class="menu-card bg-white rounded-3 p-4">
      <div class="text-center mb-3">
        <i class="menu-icon bi bi-megaphone"></i>
        <h3 class="h5 fw-bold mb-0">Comunicados</h3>
        <p class="small text-muted">o <em>noticias</em> de la app</p>
      </div>
      <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
        <div class="carousel-inner">
          ${noticias.map((n,i)=>`
            <div class="carousel-item${i?'':' active'}">
              <div class="d-flex flex-column flex-md-row align-items-stretch border rounded">
                <img src="${n.url_imagen}" class="news-thumb" alt="">
                <div class="p-3">
                  <h5 class="fw-semibold mb-1">${n.titulo}</h5>
                  <p class="small mb-2">${n.resumen}</p>
                  <a href="${n.link_noticia}" target="_blank" class="small link-success">
                    Saber más <i class="bi bi-arrow-right-short"></i></a>
                </div>
              </div>
            </div>`).join('')}
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span></button>
        <div class="carousel-indicators outside">
          ${noticias.map((_,i)=>`<button type="button" data-bs-target="#newsCarousel" data-bs-slide-to="${i}"
                     ${i?'':'class="active"'}></button>`).join('')}
        </div>
      </div>
    </div>`;
  row.appendChild(wrap);
});

/* ---------- Propiedades ---------- */
let currentProp=null;
let currentManzana = '', currentSolar = '';
function pintarAvance(etapa, porcentaje){
  const card = document.getElementById('avanceCard');
  card.innerHTML = `
    <div class="d-flex flex-column flex-md-row align-items-center gap-4">
      ${svgCircle(porcentaje)}
      <div>
        <h4 class="mb-1">Fase de Construcción</h4>
        <p class="mb-2">${etapa}</p>
        <a href="fase_detalle.php?manzana=${encodeURIComponent(currentManzana)}&villa=${encodeURIComponent(currentSolar)}"
           class="link-secondary small">
          Ver detalles <i class="bi bi-chevron-right"></i>
        </a>
      </div>
    </div>`;
  card.style.display = 'block';
}

function loadFase(id){
  const card=document.getElementById('avanceCard');
  card.style.display='none'; card.innerHTML='';
  fetch(API_FASE+id)
    .then(r=>r.json())
    .then(d=>{
      if(!d.ok){
        pintarAvance('Sin etapa asignada',0);
      }else{
        const f=d.fase;
        const etapaTexto = f.etapa_id ? `• ${f.etapa}` : 'Sin etapa asignada';
        pintarAvance(etapaTexto,f.porcentaje);
      }
    })
    .catch(()=>pintarAvance('Error al cargar',0));
}

fetch(API_PROP).then(r=>r.json()).then(({ok,propiedades})=>{
  if(!ok||!propiedades.length) return;
  document.getElementById('propRow').style.display='block';
  const tabs=document.getElementById('propTabs');
  const panes=document.getElementById('propContent');
  propiedades.forEach((p,i)=>{
    tabs.insertAdjacentHTML('beforeend',`
      <li class="nav-item">
        <button class="nav-link${i?'':' active'}" data-bs-toggle="pill"
          data-bs-target="#pane-${p.id}" type="button">${p.tipo} ${p.urbanizacion}</button>
      </li>`);
    panes.insertAdjacentHTML('beforeend',`
      <div class="tab-pane fade${i?'':' show active'}" id="pane-${p.id}">
        <div class="border rounded bg-white p-3">
          <p class="mb-1"><strong>Urbanización:</strong> ${p.urbanizacion}</p>
          <p class="mb-1"><strong>Tipo:</strong> ${p.tipo}</p>
          <p class="mb-1"><strong>Manzana:</strong> ${p.manzana}</p>
          <p class="mb-0"><strong>Solar:</strong> ${p.solar}</p>
        </div></div>`);
    if(i===0){currentProp=p.id;  currentManzana=p.manzana;
 currentSolar=p.solar; loadFase(p.id);}
  });
  tabs.addEventListener('shown.bs.tab',e=>{
    const id=e.target.dataset.bsTarget.replace('#pane-','');
    if(id!==currentProp){currentProp=id;  
      const sel = propiedades.find(x => x.id==id);
      currentManzana=sel.manzana;
      currentSolar=sel.solar;
loadFase(id);}
  });
});

/* ---------- Menú ---------- */
fetch(API_MENU).then(r=>r.json()).then(({ok,menus})=>{
  const grid=document.getElementById('menuGrid');
  document.getElementById('menuSpinner').remove();
  if(!ok){grid.textContent='Error menú';return;}
  menus.forEach(m=>{
    const col=document.createElement('div');col.className='col-6 col-md-4 col-lg-3';
    const c=document.createElement('div');
    c.className='menu-card bg-white rounded-3 p-3 text-center h-100';
    //c.onclick=()=>alert('Abrir módulo '+m.id);
    c.onclick = () => openModule(m);
    c.append(icon(m.url_icono));
    c.innerHTML+=`<h6 class="fw-semibold mt-2">${m.nombre}</h6>
                  <p class="small text-muted mb-0">${m.descripcion||''}</p>`;
    col.appendChild(c);grid.appendChild(col);
  });
});


/* ---------- Logout ---------- */
// Función para cerrar sesión (puedes llamarla desde un botón)
async function logout() { const token = localStorage.getItem('cs_token');
if (!token) { window.location.href = 'login_front.php';
return;
} try { const response = await fetch('../api/logout.php', {
method: 'POST', headers: { 'Content-Type': 'application/json'
}, body: JSON.stringify({ token: token }) });
const data = await response.json();
if (data.ok) { console.log(data.mensaje);
} else { console.error('Error al cerrar sesión:', data.mensaje);
} } catch (error) { console.error('Error de conexión:', error);
} finally {localStorage.removeItem('cs_token');
localStorage.removeItem('cs_usuario');
window.location.href = 'login_front.php';
} }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>