<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Calendario de Responsables</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">
<link href="assets/css/style_panel_calendario.css" rel="stylesheet">



</head>
<body class="bg-light">

<div class="container py-4">
  <div class="calendario-header">
    <h1 class="calendario-title">Calendario por responsable</h1>
    <button class="back-button" id="btnBack">
      <i class="bi bi-arrow-left"></i>
    </button>
  </div>

  <div class="d-flex align-items-center gap-3 mb-3">
    <select id="selResp" class="form-select w-auto"></select>
    <a href="cita_responsable.php" id="btnAgendarCliente" class="btn btn-primary">Agendar para Cliente</a>
    <button id="btnRefreshCalendar" class="btn btn-outline-primary"><i class="bi bi-arrow-clockwise"></i> Refrescar</button>
    <p style="align-items: center; margin-bottom: 0;">Citas de outlook de color</p>
    <div class="caja"></div>
    <p style="align-items: center; margin-bottom: 0;">Citas aplicación</p>
    <div class="caja1"></div>
  </div>

  <div id="calendar"></div>
</div>

<?php 
$active_page = 'inicio';
include '../api/bottom_nav.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
if(!u.id) location.href='login.php';

/* ---------- cargar responsables ---------- */
const selResp=document.getElementById('selResp');
const h1Title = document.querySelector('.calendario-title');

let is_admin_responsible = (u.is_responsable && u.id === 3);

if (!is_admin_responsible) {
  selResp.style.display = 'none';
  h1Title.textContent = 'Mi Calendario';
  initCalendar();
} else {
  fetch('../api/responsables_list.php')
    .then(r=>r.json())
    .then(d=>{
      d.items.forEach(r=>{
        selResp.insertAdjacentHTML('beforeend',
          `<option value="${r.id}">${r.nombre}</option>`);
      });
      initCalendar();
    });
}

/* ---------- calendario ---------- */
let calendar;

function initCalendar(){
  const calendar=new FullCalendar.Calendar(
    document.getElementById('calendar'),
    {
      locale:'es',
      initialView:'timeGridWeek',

      slotDuration:'00:15:00',          // ← ❶ cuadrícula en 15 min
      slotLabelInterval:'01:00:00',     // ← ❷ etiqueta cada 1 h
      slotMinTime:'07:00:00',
      slotMaxTime:'19:00:00',

      nowIndicator:true,
      height:'auto',
      eventDisplay:'block',
      slotEventOverlap:false,
      dayMaxEventRows:true,

      headerToolbar:{
        left:'prev,next today',
        center:'title',
        right:'dayGridMonth,timeGridWeek,timeGridDay'
      },

      eventContent: info=>({
        html:`<div><span class="fw-semibold">${info.timeText}</span><br>${info.event.title}</div>`
      }),

      events:(info,success,failure)=>{
        const p=new URLSearchParams({
          responsable_id: is_admin_responsible ? selResp.value : u.id,
          start:info.startStr,
          end  :info.endStr
        });
        fetch('../api/calendario_responsable.php?'+p)
          .then(r=>r.json())
          .then(d=>d.ok?success(d.items):failure())
          .catch(failure);
      }
    }
  );
  calendar.render();
  if (is_admin_responsible) {
    selResp.onchange=()=>calendar.refetchEvents();
  }
}

/* ---------- navegación ---------- */
document.getElementById('btnBack').onclick  = () => location.href='menu_front.php';

/* ---------- Refrescar Calendario ---------- */
document.getElementById('btnRefreshCalendar').onclick = () => {
  if (calendar) {
    calendar.refetchEvents();
  }
};


</script>
</body>
</html>
