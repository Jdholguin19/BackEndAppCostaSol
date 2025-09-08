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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

  <!-- Observacion Section -->
  <div class="form-section">
    <label class="section-title">
      <i class="bi bi-pencil-square"></i>
      Objetivo de la visita (Opcional)
    </label>
    <textarea id="txtObservaciones" class="form-control" rows="3" placeholder="Escriba aquí una breve descripción del propósito de su visita..."></textarea>
  </div>

  <!-- Fechas Section -->
  <div class="form-section">
    <label class="section-title">
      <i class="bi bi-calendar-event"></i>
      Seleccione fecha
    </label>
    <div id="calendar-container"></div> <!-- El calendario se renderizará aquí -->
  </div>

  <!-- Horas Section -->
  <div class="form-section">
    <label class="section-title">
      <i class="bi bi-clock"></i>
      Seleccione hora
    </label>
    <div id="horaList-container">
        <div id="horaList"></div>
    </div>
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
include '../api/bottom_nav.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
<script>
/* ---------- usuario ---------- */
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
if(!u.id) location.href='login.php';

/* ---------- refs ---------- */
const propSel  = document.getElementById('selProp');
const propGrid = document.getElementById('propGrid');
const calendarContainer = document.getElementById('calendar-container');
const horaBox  = document.getElementById('horaList');
const btnOk    = document.getElementById('btnOk');

/* ---------- estado local ---------- */
let propositoId = 0, fechaSel='', horaSel='', citaDuracion = 0;
let fp; // flatpickr instance
let hourObserver; // IntersectionObserver for hours

/* ---------- helpers ---------- */
function reset(lvl){
  if(lvl<=1){ 
    fechaSel=''; 
    if(fp) fp.clear();
    calendarContainer.style.display = 'none';
  }
  if(lvl<=2){ 
      horaSel=''; 
      horaBox.innerHTML=''; 
      if(hourObserver) hourObserver.disconnect();
  }
  btnOk.disabled = !(propositoId && fechaSel && horaSel);
}

/* ---------- cargar propiedades ---------- */
const token = localStorage.getItem('cs_token');
fetch('../api/obtener_propiedades.php?id_usuario='+u.id, {
  headers: {
    'Authorization': `Bearer ${token}`
  }
})
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
    // Se inserta directamente el botón, sin el div contenedor `col-4`
    propGrid.insertAdjacentHTML('beforeend', 
      `<button class="purpose-button" data-id="${p.id}">
          <img src="${p.url_icono||'https://via.placeholder.com/48'}" alt="Icono de ${p.proposito}">
          <span>${p.proposito}</span>
      </button>`);
  });
});

// --- Lógica del Calendario y Horas ---

function fetchAvailableDays(year, month, instance) {
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'flatpickr-loading';
    instance.calendarContainer.appendChild(loadingDiv);

    fetch(`../api/cita/dias_disponibles.php?proposito_id=${propositoId}&year=${year}&month=${month}`)
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                instance.set('enable', data.items);
            }
        })
        .finally(() => {
            if(instance.calendarContainer.contains(loadingDiv)) {
                instance.calendarContainer.removeChild(loadingDiv);
            }
        });
}

function initializeCalendar() {
    if (fp) {
        fp.destroy();
    }
    fp = flatpickr(calendarContainer, {
        locale: "es",
        inline: true,
        dateFormat: "Y-m-d",
        minDate: "today",
        onReady: function(selectedDates, dateStr, instance) {
            fetchAvailableDays(instance.currentYear, instance.currentMonth + 1, instance);
        },
        onMonthChange: function(selectedDates, dateStr, instance) {
            fetchAvailableDays(instance.currentYear, instance.currentMonth + 1, instance);
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                fechaSel = dateStr;
                loadHoras(fechaSel);
            }
        },
    });
}

function setupHourObserver() {
    if (hourObserver) {
        hourObserver.disconnect();
    }

    const options = {
        root: horaBox,
        rootMargin: '-50% 0px -50% 0px',
        threshold: 0
    };

    hourObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                horaBox.querySelectorAll('.time-slot').forEach(b => b.classList.remove('active'));
                entry.target.classList.add('active');
                horaSel = entry.target.dataset.hora;
                btnOk.disabled = !(propositoId && fechaSel && horaSel);
            }
        });
    }, options);

    horaBox.querySelectorAll('.time-slot').forEach(slot => {
        hourObserver.observe(slot);
    });
}

function loadHoras(selectedDate) {
    reset(2);
    horaBox.innerHTML = '<div class="loading-container"><div class="spinner-border"></div></div>';
    
    let apiUrl = `../api/cita/horas_disponibles.php?proposito_id=${propositoId}&fecha=${selectedDate}`;
    if (citaDuracion > 0) {
        apiUrl += `&duracion=${citaDuracion}`;
    }

    fetch(apiUrl)
        .then(r => r.json()).then(d => {
            horaBox.innerHTML = '';
            if (!d.ok || !d.items.length) {
                horaBox.innerHTML = '<div class="empty-state">— Agendar con 24 horas de anticipación —</div>';
                return;
            }
            d.items.forEach(h => {
                horaBox.insertAdjacentHTML('beforeend',
                    `<button class="time-slot" data-hora="${h.hora}" data-resp="${h.responsable_id}">
                     ${h.hora}</button>`);
            });
            setupHourObserver();
        });
}

/* ---------- click propósito ---------- */
propGrid.addEventListener('click', e => {
    const btn = e.target.closest('.purpose-button');
    if (!btn) return;
    propGrid.querySelectorAll('.purpose-button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    propositoId = btn.dataset.id;

    // Si el propósito es "Elección de acabados" (ID 2), la duración es de 120 minutos.
    if (propositoId == 2) {
        citaDuracion = 120;
    } else {
        citaDuracion = 0; // Usará el intervalo por defecto de la base de datos.
    }
    
    reset(1);
    calendarContainer.style.display = 'block';
    initializeCalendar();
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
  fd.append('observaciones', document.getElementById('txtObservaciones').value);
  fd.append('duracion', citaDuracion); // Enviar la duración

  fetch('../api/cita/cita_create.php',{method:'POST', body:fd, headers: {'Authorization': `Bearer ${token}`}})
   .then(r=>r.json()).then(d=>{
      if(d.ok){ alert('Cita agendada ✔'); location.href='citas.php'; }
      else    { alert(d.msg||'No se pudo agendar'); btnOk.disabled=false; btnOk.innerHTML='<i class="bi bi-check-circle"></i> Confirmar'; }
   })
   .catch(()=>{ alert('Error servidor'); btnOk.disabled=false; btnOk.innerHTML='<i class="bi bi-check-circle"></i> Confirmar'; });
};
</script>
</body>
</html>
</script>
</body>
</html>
