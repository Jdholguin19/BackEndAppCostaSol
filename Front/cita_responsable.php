<?php /* Front/cita_responsable.php */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agendar Cita para Cliente | CostaSol</title>

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
      <h1 class="citas-title">Agendar para Cliente</h1>
    </div>
  </div>
  <hr style="color:rgba(69, 67, 67, 0); margin-top: 1px; "></hr>
</div>

<!-- Main Content -->
<div class="container">
  <!-- Usuario Section -->
  <div class="form-section">
    <label class="section-title">
      <i class="bi bi-person-check"></i>
      Cliente
    </label>
    <select id="selUsuario" class="form-select"><option value="">-- Seleccione un cliente --</option></select>
  </div>

  <!-- Propiedad Section -->
  <div class="form-section" id="propiedad-section" style="display: none;">
    <label class="section-title">
      <i class="bi bi-house"></i>
      Propiedad
    </label>
    <select id="selProp" class="form-select"></select>
  </div>

  <!-- Propósito Section -->
  <div class="form-section" id="proposito-section" style="display: none;">
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
  <div class="form-section" id="fechas-section" style="display: none;">
    <label class="section-title">
      <i class="bi bi-calendar-event"></i>
      Seleccione fecha
    </label>
    <div id="calendar-container"></div> <!-- El calendario se renderizará aquí -->
  </div>

  <!-- Horas Section -->
  <div class="form-section" id="horas-section" style="display: none;">
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
<script>
/* ---------- responsable ---------- */
const responsable = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
if(!responsable.is_responsable) location.href='login_front.php';

/* ---------- refs ---------- */
const userSel = document.getElementById('selUsuario');
const propSel  = document.getElementById('selProp');
const propGrid = document.getElementById('propGrid');
const calendarContainer = document.getElementById('calendar-container');
const horaBox  = document.getElementById('horaList');
const btnOk    = document.getElementById('btnOk');

// Secciones
const propiedadSection = document.getElementById('propiedad-section');
const propositoSection = document.getElementById('proposito-section');
const fechasSection = document.getElementById('fechas-section');
const horasSection = document.getElementById('horas-section');

/* ---------- estado local ---------- */
let selectedUserId = 0;
let propositoId = 0, fechaSel='', horaSel='', citaDuracion = 0;
let fp; // flatpickr instance
let hourObserver; // IntersectionObserver for hours

/* ---------- helpers ---------- */
function reset(lvl){
  if(lvl<=0) { // Reset propósito
      propositoId = 0;
      propGrid.querySelectorAll('.purpose-button').forEach(b => b.classList.remove('active'));
      fechasSection.style.display = 'none';
      horasSection.style.display = 'none';
  }
  if(lvl<=1){ // Reset calendario
    fechaSel=''; 
    if(fp) fp.clear();
    calendarContainer.style.display = 'none';
    horasSection.style.display = 'none';
  }
  if(lvl<=2){ // Reset horas
      horaSel=''; 
      horaBox.innerHTML=''; 
      if(hourObserver) hourObserver.disconnect();
  }
  btnOk.disabled = !(selectedUserId && propSel.value && propositoId && fechaSel && horaSel);
}

/* ---------- Cargar Usuarios ---------- */
const token = localStorage.getItem('cs_token');
fetch('../api/user_list.php', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
})
.then(r => r.json()).then(d => {
    if (!d.ok) {
        alert('No se pudo cargar la lista de clientes.');
        return;
    }
    d.data.forEach(u => {
        userSel.insertAdjacentHTML('beforeend',
            `<option value="${u.id_usuario}">${u.nombre} ${u.apellido}</option>`);
    });
});

/* ---------- Evento: seleccionar usuario ---------- */
userSel.addEventListener('change', () => {
    selectedUserId = userSel.value;
    propSel.innerHTML = ''; // Limpiar propiedades anteriores
    reset(0);

    if (!selectedUserId) {
        propiedadSection.style.display = 'none';
        propositoSection.style.display = 'none';
        return;
    }

    // Cargar propiedades del usuario seleccionado
    fetch(`../api/obtener_propiedades.php?id_usuario=${selectedUserId}`, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
    .then(r => r.json()).then(d => {
        if (!d.ok || d.propiedades.length === 0) {
            alert('Este cliente no tiene propiedades asignadas.');
            propiedadSection.style.display = 'none';
            propositoSection.style.display = 'none';
            return;
        }
        d.propiedades.forEach(p => {
            propSel.insertAdjacentHTML('beforeend',
                `<option value="${p.id}">${p.urbanizacion} — Mz ${p.manzana} / Villa ${p.solar}</option>`);
        });
        propiedadSection.style.display = 'block';
        propositoSection.style.display = 'block';
    });
});

/* ---------- cargar propósitos ---------- */
fetch('../api/propositos.php').then(r=>r.json()).then(d=>{
  if(!d.ok) return;
  d.items.forEach(p=>{
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
                horasSection.style.display = 'block';
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
                btnOk.disabled = !(selectedUserId && propSel.value && propositoId && fechaSel && horaSel);
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

    if (propositoId == 2) {
        citaDuracion = 120;
    } else {
        citaDuracion = 0;
    }
    
    reset(1);
    fechasSection.style.display = 'block';
    calendarContainer.style.display = 'block';
    initializeCalendar();
});

/* ---------- confirmar ---------- */
btnOk.onclick = ()=>{
  btnOk.disabled=true; 
  btnOk.innerHTML='<i class="bi bi-hourglass-split"></i> Guardando…';
  const fd = new FormData();
  fd.append('id_usuario'  , selectedUserId); // <-- ID del usuario seleccionado
  fd.append('id_propiedad', propSel.value);
  fd.append('proposito_id', propositoId);
  fd.append('fecha'       , fechaSel);
  fd.append('hora'        , horaSel);
  fd.append('observaciones', document.getElementById('txtObservaciones').value);
  fd.append('duracion', citaDuracion);

  fetch('../api/cita/cita_create.php',{method:'POST',body:fd, headers: {'Authorization': `Bearer ${token}`}})
   .then(r=>r.json()).then(d=>{
      if(d.ok){ alert('Cita agendada para el cliente ✔'); location.href='panel_calendario.php'; }
      else    { alert(d.msg||'No se pudo agendar'); btnOk.disabled=false; btnOk.innerHTML='<i class="bi bi-check-circle"></i> Confirmar'; }
   })
   .catch(()=>{ alert('Error servidor'); btnOk.disabled=false; btnOk.innerHTML='<i class="bi bi-check-circle"></i> Confirmar'; });
};
</script>
</body>
</html>