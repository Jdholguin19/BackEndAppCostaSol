<?php /* Front/cita_nueva.php */ ?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><title>Agendar visita</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{background:#f5f6f8}
.container{max-width:520px}
.btn-prop{width:100%;padding:1rem;border:2px solid #ced4da;border-radius:.75rem;
          background:#fff;display:flex;flex-direction:column;align-items:center;gap:.25rem;
          transition:.2s}
.btn-prop.active{border-color:#0d6efd;background:#e9f3ff}
.btn-slot{width:100%;justify-content:space-between}
.btn-slot.active,.btn-slot:hover{background:#0d6efd;color:#fff}
</style>
</head><body>
<div class="container py-4">

 <button class="btn btn-link mb-2" onclick="history.back()"><i class="bi bi-arrow-left"></i></button>
 <h1 class="h4 mb-4">Agendar Visita</h1>

 <!-- —— Propiedad —— -->
 <label class="form-label">Propiedad</label>
 <select id="selProp" class="form-select mb-4"></select>

 <!-- —— Propósito —— -->
 <p class="mb-2 fw-semibold">Seleccione el propósito</p>
 <div id="propGrid" class="row g-2 mb-4"></div>

 <!-- —— Fechas —— -->
 <p class="mb-2 fw-semibold">Seleccione fecha</p>
 <div id="fechaList" class="vstack gap-2 mb-4"></div>

 <!-- —— Horas —— -->
 <p class="mb-2 fw-semibold">Seleccione hora</p>
 <div id="horaList" class="vstack gap-2 mb-4"></div>

 <div class="d-flex gap-2">
   <button class="btn btn-outline-secondary w-50" onclick="history.back()">Cancelar</button>
   <button id="btnOk" class="btn btn-primary w-50" disabled>Confirmar</button>
 </div>
</div>

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
    col.innerHTML = `<button class="btn-prop" data-id="${p.id}">
        <img src="${p.url_icono||'https://via.placeholder.com/48'}" width="36">
        <span>${p.proposito}</span></button>`;
    propGrid.appendChild(col);
  });
});

/* ---------- click propósito ---------- */
propGrid.addEventListener('click',e=>{
  const btn=e.target.closest('.btn-prop'); if(!btn) return;
  propGrid.querySelectorAll('.btn-prop').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  propositoId = btn.dataset.id;
  reset(1);

  /* cargar días disponibles */
  fechaBox.innerHTML='<div class="text-center py-3"><div class="spinner-border"></div></div>';
  fetch(`../api/dias_disponibles.php?proposito_id=${propositoId}`)
   .then(r=>r.json()).then(d=>{
     fechaBox.innerHTML='';
     if(!d.ok||!d.items.length){ fechaBox.innerHTML='<p class="text-muted">— Sin fechas —</p>'; return; }
     d.items.forEach(f=>{
       fechaBox.insertAdjacentHTML('beforeend',
         `<button class="btn btn-light btn-slot" data-fecha="${f.fecha}">
             <span><i class="bi bi-calendar3"></i> ${iso2esp(f.fecha)}</span>
             <span class="small text-muted">${f.libres} horarios</span>
          </button>`);
     });
   });
});

/* ---------- click día ---------- */
fechaBox.addEventListener('click',e=>{
  const btn=e.target.closest('.btn-slot'); if(!btn) return;
  fechaBox.querySelectorAll('.btn-slot').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  fechaSel = btn.dataset.fecha;
  reset(2);

  /* cargar horas disponibles */
  horaBox.innerHTML='<div class="text-center py-3"><div class="spinner-border"></div></div>';
  fetch(`../api/horas_disponibles.php?proposito_id=${propositoId}&fecha=${fechaSel}`)
   .then(r=>r.json()).then(d=>{
     horaBox.innerHTML='';
     if(!d.ok||!d.items.length){ horaBox.innerHTML='<p class="text-muted">— Sin horarios —</p>'; return; }
     d.items.forEach(h=>{
       horaBox.insertAdjacentHTML('beforeend',
         `<button class="btn btn-outline-primary btn-slot" data-hora="${h.hora}" data-resp="${h.responsable_id}">
             ${h.hora}</button>`);
     });
   });
});

/* ---------- click hora ---------- */
horaBox.addEventListener('click',e=>{
  const btn=e.target.closest('.btn-slot'); if(!btn) return;
  horaBox.querySelectorAll('.btn-slot').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  horaSel = btn.dataset.hora;
  respSel = btn.dataset.resp;       // puede ser útil si quieres mostrar “responsable asignado”
  btnOk.disabled = !(propositoId && fechaSel && horaSel);
});

/* ---------- confirmar ---------- */
btnOk.onclick = ()=>{
  btnOk.disabled=true; btnOk.textContent='Guardando…';
  const fd = new FormData();
  fd.append('id_usuario'  , u.id);
  fd.append('id_propiedad', propSel.value);
  fd.append('proposito_id', propositoId);
  fd.append('fecha'       , fechaSel);
  fd.append('hora'        , horaSel);

  fetch('../api/cita_create.php',{method:'POST',body:fd})
   .then(r=>r.json()).then(d=>{
      if(d.ok){ alert('Cita agendada ✔'); location.href='citas.php'; }
      else    { alert(d.msg||'No se pudo agendar'); btnOk.disabled=false; btnOk.textContent='Confirmar'; }
   })
   .catch(()=>{ alert('Error servidor'); btnOk.disabled=false; btnOk.textContent='Confirmar'; });
};
</script>
</body></html>
