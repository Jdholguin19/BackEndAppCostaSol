<?php /* Front/cita_nueva.php */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agendar Visita | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">
<link href="assets/css/style_cita_nueva.css" rel="stylesheet">
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
      <h1 class="citas-title">Nueva Cita</h1>
    </div>
  </div>
  <hr style="color:rgba(69, 67, 67, 0); margin-top: 1px; "></hr>
</div>

<!-- Main Content -->
<div class="container">
  <!-- Propiedad Section -->
  <div class="form-section">
    <label class="section-title">
      <i class="bi bi-house"></i>
      Propiedad
    </label>
    <select id="selProp" class="form-select"></select>
  </div>

  <!-- Propósito Section -->
  <div class="form-section">
    <label class="section-title">
      <i class="bi bi-list-check"></i>
      Seleccione el propósito
    </label>
    <div id="propGrid" class="purpose-grid"></div>
  </div>

  <!-- Fechas Section -->
  <div class="form-section">
    <label class="section-title">
      <i class="bi bi-calendar-event"></i>
      Seleccione fecha
    </label>
    <div id="fechaList" class="date-time-list"></div>
  </div>

  <!-- Horas Section -->
  <div class="form-section">
    <label class="section-title">
      <i class="bi bi-clock"></i>
      Seleccione hora
    </label>
    <div id="horaList" class="date-time-list"></div>
  </div>

  <!-- Action Buttons -->
  <div class="action-buttons">
    <button class="cancel-button" onclick="history.back()">
      <i class="bi bi-x-circle"></i>
      Cancelar
    </button>
    <button id="btnOk" class="confirm-button" disabled>
      <i class="bi bi-check-circle"></i>
      Confirmar
    </button>
  </div>
</div>

<?php 
$active_page = 'citas';
include 'includes/bottom_nav.php'; 
?>

<script>
/* ---------- usuario ---------- */
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
if(!u.id) location.href='login.php';

/* ---------- refs ---------- */
const propSel  = document.getElementById('selProp');
const propGrid = document.getElementById('propGrid');
const fechaBox = document.getElementById('fechaList');
const horaBox  = document.getElementById('horaList');
const btnOk    = document.getElementById('btnOk');

/* ---------- estado local ---------- */
let propositoId = 0, fechaSel='', horaSel='';

/* ---------- helpers ---------- */
// ⇒ interpreta la fecha como LOCAL, sin tocar UTC
const iso2esp = s => {
  const [y, m, d] = s.split('-').map(Number);        // y=2025, m=6, d=30
  return new Date(y, m - 1, d)                       // Date(2025,5,30) local
         .toLocaleDateString('es-ES',{
           weekday:'long', year:'numeric',
           month:'long',  day:'numeric'
         });
};

function reset(lvl){
  if(lvl<=1){ fechaSel=''; fechaBox.innerHTML=''; }
  if(lvl<=2){ horaSel=''; horaBox.innerHTML=''; }
  btnOk.disabled = !(propositoId && fechaSel && horaSel);
}

/* ---------- cargar propiedades ---------- */
fetch('../api/obtener_propiedades.php?id_usuario='+u.id)
 .then(r=>r.json()).then(d=>{
   if(!d.ok) return;
   d.propiedades.forEach(p=>{
     propSel.insertAdjacentHTML('beforeend',
      `<option value="${p.id}">${p.urbanizacion} — Mz ${p.manzana} / Villa ${p.solar}</option>`);
   });
 });

/* ---------- cargar propósitos ---------- */
fetch('../api/propositos.php').then(r=>r.json()).then(d=>{
  if(!d.ok) return;
  d.items.forEach(p=>{
    const col=document.createElement('div'); col.className='col-4';
    col.innerHTML = `<button class="purpose-button" data-id="${p.id}">
        <img src="${p.url_icono||'https://via.placeholder.com/48'}" width="36">
        <span>${p.proposito}</span></button>`;
    propGrid.appendChild(col);
  });
});

/* ---------- click propósito ---------- */
propGrid.addEventListener('click',e=>{
  const btn=e.target.closest('.purpose-button'); if(!btn) return;
  propGrid.querySelectorAll('.purpose-button').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  propositoId = btn.dataset.id;
  reset(1);

  /* cargar días disponibles */
  fechaBox.innerHTML='<div class="loading-container"><div class="spinner-border"></div></div>';
  fetch(`../api/cita/dias_disponibles.php?proposito_id=${propositoId}`)
   .then(r=>r.json()).then(d=>{
     fechaBox.innerHTML='';
     if(!d.ok||!d.items.length){ fechaBox.innerHTML='<div class="empty-state">— Sin fechas —</div>'; return; }
     d.items.forEach(f=>{
       fechaBox.insertAdjacentHTML('beforeend',
         `<button class="date-slot" data-fecha="${f.fecha}">
             <div class="date-info">
               <span class="date-text"><i class="bi bi-calendar3"></i> ${iso2esp(f.fecha)}</span>
               <span class="date-availability">${f.libres} horarios</span>
             </div>
          </button>`);
     });
   });
});

/* ---------- click día ---------- */
fechaBox.addEventListener('click',e=>{
  const btn=e.target.closest('.date-slot'); if(!btn) return;
  fechaBox.querySelectorAll('.date-slot').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  fechaSel = btn.dataset.fecha;
  reset(2);

  /* cargar horas disponibles */
  horaBox.innerHTML='<div class="loading-container"><div class="spinner-border"></div></div>';
  fetch(`../api/cita/horas_disponibles.php?proposito_id=${propositoId}&fecha=${fechaSel}`)
   .then(r=>r.json()).then(d=>{
     horaBox.innerHTML='';
     if(!d.ok||!d.items.length){ horaBox.innerHTML='<div class="empty-state">— Sin horarios —</div>'; return; }
     d.items.forEach(h=>{
       horaBox.insertAdjacentHTML('beforeend',
         `<button class="time-slot" data-hora="${h.hora}" data-resp="${h.responsable_id}">
             ${h.hora}</button>`);
     });
   });
});

/* ---------- click hora ---------- */
horaBox.addEventListener('click',e=>{
  const btn=e.target.closest('.time-slot'); if(!btn) return;
  horaBox.querySelectorAll('.time-slot').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  horaSel = btn.dataset.hora;
  respSel = btn.dataset.resp;       // puede ser útil si quieres mostrar "responsable asignado"
  btnOk.disabled = !(propositoId && fechaSel && horaSel);
});

/* ---------- confirmar ---------- */
btnOk.onclick = ()=>{
  btnOk.disabled=true; 
  btnOk.innerHTML='<i class="bi bi-hourglass-split"></i> Guardando…';
  const fd = new FormData();
  fd.append('id_usuario'  , u.id);
  fd.append('id_propiedad', propSel.value);
  fd.append('proposito_id', propositoId);
  fd.append('fecha'       , fechaSel);
  fd.append('hora'        , horaSel);

  fetch('../api/cita/cita_create.php',{method:'POST',body:fd})
   .then(r=>r.json()).then(d=>{
      if(d.ok){ alert('Cita agendada ✔'); location.href='citas.php'; }
      else    { alert(d.msg||'No se pudo agendar'); btnOk.disabled=false; btnOk.innerHTML='<i class="bi bi-check-circle"></i> Confirmar'; }
   })
   .catch(()=>{ alert('Error servidor'); btnOk.disabled=false; btnOk.innerHTML='<i class="bi bi-check-circle"></i> Confirmar'; });
};
</script>
</body>
</html>
