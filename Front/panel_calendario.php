<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Calendario de Responsables</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<style>
/* ancho máximo */
#calendar{max-width:1200px;margin:0 auto;}

/* ---------- WEEK / DAY (timeGrid) ---------- */
.fc-timegrid-event-harness,
.fc-timegrid-event{
  overflow:visible!important;
  height:auto!important;          /* deja crecer alto si hace falta */
}

.fc-timegrid-event .fc-event-main-frame{
  display:block!important;        /* anula flex */
  white-space:normal!important;
  overflow-wrap:anywhere;
  word-break:break-all;
}
.fc-timegrid-event .fc-event-main{
  padding:2px 4px;
  line-height:1.15;
}
.fc-timegrid-event .fc-event-time{font-weight:600;display:block;}
.fc-timegrid-event .fc-event-title{font-size:.78rem;word-break:break-all;}

/* ---------- MONTH (dayGrid) ---------- */
.fc-daygrid-event{display:block;width:100%;}
.fc-daygrid-event .fc-event-main{
  white-space:normal;
  overflow-wrap:anywhere;
  font-size:.75rem;
  line-height:1.15;
}

/* ---------- texto oscuro sobre fondo amarillo ---------- */
.fc-event[style*="background-color:#ffc107"] *,
.fc-event[style*="background-color: #ffc107"] *{color:#212529!important;}
</style>


</head>
<body class="bg-light">

<div class="container py-4">
  <div class="d-flex align-items-center gap-3 mb-3">
    <h1 class="h4 mb-0">Calendario por responsable</h1>
    <select id="selResp" class="form-select w-auto"></select>
        <button class="btn btn-link text-dark btn-back" id="btnBack">
          <i class="bi bi-arrow-left"></i>
      </button> 
  </div>

  <div id="calendar"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
/* ---------- cargar responsables ---------- */
const selResp=document.getElementById('selResp');
fetch('../api/responsables_list.php')
  .then(r=>r.json())
  .then(d=>{
    d.items.forEach(r=>{
      selResp.insertAdjacentHTML('beforeend',
        `<option value="${r.id}">${r.nombre}</option>`);
    });
    initCalendar();
  });

/* ---------- calendario ---------- */
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
          responsable_id:selResp.value,
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
  selResp.onchange=()=>calendar.refetchEvents();
}


/* ---------- navegación ---------- */
document.getElementById('btnBack').onclick  = () => location.href='menu_front.php';


</script>
</body>
</html>
