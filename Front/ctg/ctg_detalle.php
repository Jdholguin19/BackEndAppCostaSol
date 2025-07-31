<?php /* Front/ctg/ctg_detalle.php */
$id = (int)($_GET['id'] ?? 0);
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><title>CTG detalle</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* —— layout global —— */
body{background:#f5f6f8}
.container{max-width:760px}

/* —— cabecera & badges —— */
.btn-back{padding:.25rem .5rem;font-size:1.25rem}
.badge-dot{position:relative;padding-left:.9rem;font-size:.8rem}
.badge-dot::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);
 width:.55rem;height:.55rem;border-radius:50%}
.badge-dot.abierto::before {background:#0d6efd}             /* ingresado */
.badge-dot.proceso::before {background:#d4ac1d}
.badge-dot.resuelto::before{background:#1f9d55}

.msg-head{font-weight:600;margin-bottom:.25rem}

/* —— lista de mensajes —— */
.chat{list-style:none;padding:0}
.chat li{display:flex;gap:.5rem;margin-bottom:1.25rem}

.chat .bubble{
   max-width:75%;padding:.7rem 1rem;border-radius:.75rem;position:relative;
   background:#f8f9fa;font-size:.95rem
}

.chat .time{font-size:.75rem;color:#6c757d;margin-top:.25rem}
.avatar-sm{width:40px;height:40px;border-radius:50%;object-fit:cover}

/* ➊ el <li> que tenga la clase .right empuja el contenido hacia la derecha */
.chat li.right{justify-content:flex-end}

/* ➋ dentro de .right el avatar debe quedar después de la burbuja            */
.chat li.right img{order:2;margin-left:.5rem}

/* ➌ burbuja del responsable con color diferente                              */
.chat li.right .bubble{background:#e9f2ff}

</style>
</head><body>

<div class="container py-4" id="wrap">
  <!-- cabecera -->
  <div class="d-flex align-items-center mb-4">
    <button class="btn btn-link text-dark btn-back" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <h1 class="h5 mb-0 flex-grow-1 text-truncate" id="title">CTG</h1>
  </div>

  <!-- detalle principal -->
  <div id="headBox" class="mb-4"></div>

  <!-- hilo -->
  <ul id="chat" class="chat mb-5"></ul>

  <!-- —— caja nueva respuesta —— -->
  <form id="frmRespuesta" enctype="multipart/form-data" class="card p-3">
    <textarea id="txtMensaje" name="mensaje" class="form-control mb-2" rows="3" placeholder="Escriba su respuesta…" required></textarea>
    <div class="d-flex justify-content-between">
      <input type="file" name="archivo" accept="image/*,application/pdf" class="form-control w-auto form-control-sm">
      <button id="btnSend" class="btn btn-primary btn-sm" type="submit">Enviar</button>
    </div>
  </form>

  <!-- Área para mostrar notificaciones de éxito -->
  <div id="notificationArea" class="mt-3"></div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ------- rutas ------- */
const END_RESP = '../../api/ctg/ctg_respuestas.php?ctg_id=<?=$id?>';
const END_SEND = '../../api/ctg/ctg_insert_form.php';
const END_UPDATE_ESTADO = '../../api/ctg/ctg_update_estado.php'; // Nuevo endpoint

/* ------- obtener usuario autenticado ------- */
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
if(!u.id) { 
    document.getElementById('wrap').innerHTML = '<div class="alert alert-warning">Debes iniciar sesión para ver los detalles del CTG.</div>';
} else { // Inicio del bloque else si hay usuario autenticado

    const END_CTG  = `../../api/ctg/ctg_list.php?ctg_id=<?=$id?>`;

    /* ------- refs DOM ------- */
    const titleEl = document.getElementById('title');
    const headBox = document.getElementById('headBox');
    const chat    = document.getElementById('chat');
    const frmRespuesta = document.getElementById('frmRespuesta');
    const btnSend = document.getElementById('btnSend');
    const txtMensaje = document.getElementById('txtMensaje');
    const notificationArea = document.getElementById('notificationArea');

    // Obtener el token de localStorage
    const token = localStorage.getItem('cs_token');

    // Variable para almacenar las respuestas actuales y comparar
    let currentResponses = [];

    // Verificar si el usuario es responsable
    const isResponsable = u.is_responsable || false;

    // Variable para almacenar el estado actual del CTG (para revertir si falla la actualización)
    let currentCtgEstadoId = null;


    // Verificar si hay token antes de hacer cualquier solicitud a APIs protegidas
    if (!token) { // Inicio del bloque if si no hay token
         headBox.innerHTML = '<div class="alert alert-warning">Tu sesión ha expirado o no estás autorizado para ver este CTG. Por favor, inicia sesión de nuevo.</div>';
         if (frmRespuesta) frmRespuesta.style.display = 'none';
    } else { // Inicio del bloque else si hay token

        /* ------- helpers ------- */
        function fechaHora(str){
          return new Date(str).toLocaleString([], {dateStyle:'short', timeStyle:'short'});
        }
        function badgeEstado(txt){
          const k = txt.toLowerCase();
          const cls = k.includes('resuel') ? 'resuelto' :
                      k.includes('pro')   ? 'proceso'  : 'abierto';
          return `<span class="badge-dot ${cls}">${txt}</span>`;
        }

        function msgHTML(r){
          const esResp = Number(r.responsable_id) > 0;
          const dirClass = esResp ? 'right' : '';
          const foto = r.url_foto || 'https://via.placeholder.com/40x40?text=%20';

          return `<li class="${dirClass}">
                    <img src="${foto}" class="avatar-sm" alt="">
                    <div>
                      <div class="bubble">${r.mensaje}</div>
                      <div class="time">${fechaHora(r.fecha_respuesta)}</div>
                    </div>
                  </li>`;
        }

        function showNotification(message, type = 'success') {
          const alertDiv = document.createElement('div');
          alertDiv.classList.add('alert', `alert-${type}`, 'alert-dismissible', 'fade', 'show');
          alertDiv.setAttribute('role', 'alert');
          alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          `;
          notificationArea.appendChild(alertDiv);

          setTimeout(() => {
            alertDiv.remove();
          }, 5000);
        }

        /* ------- cabecera CTG ------- */
        fetch(END_CTG, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(r => {
            if (r.status === 401) {
                headBox.innerHTML = '<div class="alert alert-warning">Tu sesión ha expirado o no estás autorizado para ver este CTG. Por favor, inicia sesión de nuevo.</div>';
                if (frmRespuesta) frmRespuesta.style.display = 'none';
                 return Promise.reject('No autorizado');
            }
            return r.json();
        })
        .then(d=>{
          if(!d.ok||!d.ctg[0]) {
              headBox.innerHTML = `<div class="alert alert-danger">Error al cargar el CTG o no encontrado: ${d.mensaje || 'Desconocido'}</div>`;
              if (frmRespuesta) frmRespuesta.style.display = 'none';
              return;
          }
          const p = d.ctg[0];

          // Actualizar el estado actual del CTG globalmente
          currentCtgEstadoId = p.estado_id;

          // Mostrar Manzana/Villa en el título si están disponibles
          const mzVillaTitle = (p.manzana || p.villa) ? ` · Mz ${p.manzana} – Villa ${p.villa}` : '';
          titleEl.textContent = `${p.subtipo}${mzVillaTitle}`; // Establecer el título con el subtipo y Mz/Villa


          headBox.innerHTML = `
            <p class="mb-1">
              <span class="badge bg-secondary me-1">${p.tipo}</span>
              ${badgeEstado(p.estado)}
            </p>
            <p class="small text-muted mb-2">${fechaHora(p.fecha_ingreso)}</p>
            <div class="p-3 rounded bg-white border">${p.descripcion}</div>`;

          // Si el CTG está cerrado, ocultar el formulario de respuesta
          if (p.estado.toLowerCase().includes('cerr')) {
          if (frmRespuesta) frmRespuesta.style.display = 'none';
          }

          // --- INICIO: Lógica para Responsables ---
          if (isResponsable) {
              addEstadoDropdown(p.estado_id); // p.estado_id debería venir en la respuesta de ctg_list.php
          }
          // --- FIN: Lógica para Responsables ---

        })
         .catch(err => {
           console.error(err);
            if (err !== 'No autorizado') {
                 headBox.innerHTML = '<div class="alert alert-danger">Error al conectar con el servidor para obtener el CTG.</div>';
                 if (frmRespuesta) frmRespuesta.style.display = 'none';
            }
        });

        /* ------- Función para agregar menú desplegable de estado (para responsables) ------- */
        function addEstadoDropdown(currentEstadoId) {
            if (document.getElementById('selEstadoCTG')) {
                return; // Ya existe, no añadir de nuevo
            }

            const selectHtml = `
              <select id="selEstadoCTG" class="form-select form-select-sm w-auto ms-3">
                <!-- Opciones se cargarán aquí -->
</select>`;

            const titleElement = document.getElementById('title');
            if (titleElement) {
                titleElement.insertAdjacentHTML('afterend', selectHtml);
            }

            const selEstadoCTG = document.getElementById('selEstadoCTG');

            fetch('../../api/ctg/ctg_estados.php')
                .then(r => r.json())
                .then(d => {
                    if (d.ok && d.estados) {
                        d.estados.forEach(estado => {
                            if (estado.id == 1) {
                              return; // Saltar la opción con ID 1 (Ingresado)
                            }
                            const option = document.createElement('option');
                            option.value = estado.id;
                            option.textContent = estado.nombre;
                            if (estado.id == currentEstadoId) {
                                option.selected = true;
                            }
                            selEstadoCTG.appendChild(option);
                        });

                        selEstadoCTG.addEventListener('change', handleEstadoChange);

                    } else {
                        console.error('Error al cargar estados de CTG:', d.msg);
                        if (selEstadoCTG) {
                             selEstadoCTG.innerHTML = '<option value="">Error al cargar</option>';
                             selEstadoCTG.disabled = true;
                        }
                    }
                })
                .catch(err => {
                    console.error('Error fetching CTG states:', err);
                     if (selEstadoCTG) {
                         selEstadoCTG.innerHTML = '<option value="">Error de red</option>';
                         selEstadoCTG.disabled = true;
                    }
                });
        }

        /* ------- Función para manejar el cambio de estado ------- */
        function handleEstadoChange(event) {
            const newEstadoId = event.target.value;
            const ctgId = <?=$id?>;

            if (!newEstadoId) {
                console.warn("Seleccione un estado válido.");
                return;
            }

            // Deshabilitar el select mientras se actualiza
            event.target.disabled = true;

            fetch(END_UPDATE_ESTADO, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    ctg_id: ctgId,
                    estado_id: newEstadoId
                })
            })
            .then(r => {
                if (r.status === 401 || r.status === 403) {
                     showNotification('No tienes permiso para cambiar el estado del CTG.', 'danger');
                     // Revertir la selección del dropdown si no está autorizado
                     event.target.value = currentCtgEstadoId; // Usar el estado global almacenado
                     return Promise.reject('Permiso denegado o no autorizado');
                }
                return r.json();
            })
            .then(d => {
                if (d.ok) {
                    showNotification('Estado del CTG actualizado correctamente.');
                    // Actualizar el estado actual global
                    currentCtgEstadoId = newEstadoId;
                     // Actualizar la visualización del badge de estado
                     updateEstadoBadge(newEstadoId, event.target.options[event.target.selectedIndex].text);

                     // Si el estado cambia a cerrado, ocultar el formulario de respuesta
                     if (event.target.options[event.target.selectedIndex].text.toLowerCase().includes('cerr')) {
                         if (frmRespuesta) frmRespuesta.style.display = 'none';
                     } else {
                          if (frmRespuesta) frmRespuesta.style.display = 'block';
                     }

                } else {
                    showNotification('Error al actualizar el estado del CTG: ' + (d.msg || 'Desconocido'), 'danger');
                     // Revertir la selección del dropdown si falla la actualización
                     event.target.value = currentCtgEstadoId;
                }
            })
            .catch(err => {
                console.error('Error updating CTG state:', err);
                 if (err !== 'Permiso denegado o no autorizado') {
                     showNotification('Error de red al actualizar el estado del CTG.', 'danger');
                 }
                 // Revertir la selección del dropdown en caso de error de red
                 event.target.value = currentCtgEstadoId;
            })
            .finally(()=>{ event.target.disabled = false; }); // Habilitar el select al finalizar
        } // FIN de la función handleEstadoChange

        // Función para actualizar el badge de estado en la cabecera (Opcional)
        function updateEstadoBadge(estadoId, estadoNombre) {
            const badgeContainer = headBox.querySelector('.badge-dot').parentNode; // Encuentra el contenedor del badge
            if (badgeContainer) {
                badgeContainer.innerHTML = `
                  <span class="badge bg-secondary me-1">${headBox.querySelector('.badge.bg-secondary').textContent}</span>
                  ${badgeEstado(estadoNombre)}`; // Reutiliza la función badgeEstado
            }
        }


        /* ------- respuestas (Función para cargar y mostrar) ------- */
        function loadAndDisplayResponses() {
          fetch(END_RESP, {
              headers: {
                  'Authorization': `Bearer ${token}`
              }
          }).then(r => {
              if (r.status === 401) {
                 chat.innerHTML='<li class="text-warning">Tu sesión ha expirado o no estás autorizado para ver las respuestas.</li>';
                  return Promise.reject('No autorizado');
              }
              return r.json();
          })
          .then(d=>{
            if(!d.ok){
                 chat.innerHTML='<li class="text-danger">Error al cargar respuestas</li>';
                 console.error('Error en API ctg_respuestas:', d.msg);
                 return;
             }

            const newResponsesString = JSON.stringify(d.respuestas);
            const currentResponsesString = JSON.stringify(currentResponses);

            if (newResponsesString !== currentResponsesString) {
                currentResponses = d.respuestas;
                chat.innerHTML = currentResponses.length
                  ? currentResponses.map(msgHTML).join('')
                  : '<li class="text-muted">— Sin respuestas —</li>';

                chat.scrollTop = chat.scrollHeight;
            }

          })
          .catch(err=>{
               console.error(err);
              if (err !== 'No autorizado') {
                 chat.innerHTML='<li class="text-danger">Error al conectar con el servidor de respuestas</li>';
             }
          });
        }

        // Cargar respuestas al cargar la página
        loadAndDisplayResponses();

        // Configurar polling para cargar respuestas cada 5 segundos
        const pollingInterval = setInterval(loadAndDisplayResponses, 5000);

        // Limpiar el intervalo cuando el usuario sale de la página
        window.addEventListener('beforeunload', () => {
            clearInterval(pollingInterval);
        });

        /* ------- envío de nueva respuesta ------- */
        frmRespuesta.addEventListener('submit',e=>{
          e.preventDefault();
          if(!frmRespuesta.checkValidity()){ frmRespuesta.classList.add('was-validated'); return; }

          const fd = new FormData(e.target);
          fd.append('ctg_id', <?=$id?>);

          const btn=document.getElementById('btnSend');
          btn.disabled=true; btn.textContent='Enviando…';

          fetch(END_SEND,{method:'POST',body:fd,
              headers: {
                  'Authorization': `Bearer ${token}`
              }
           })
            .then(r => {
                if (r.status === 401) {
                     showNotification('Tu sesión ha expirado o no estás autorizado para responder. Por favor, inicia sesión de nuevo.', 'danger');
                     return Promise.reject('No autorizado');
                }
                return r.json();
            })
            .then(d=>{
              if(d.ok){
                txtMensaje.value = '';
                const fileInput = frmRespuesta.querySelector('input[type="file"]');
                if(fileInput) fileInput.value = '';

                frmRespuesta.classList.remove('was-validated');
                loadAndDisplayResponses();
                showNotification('Respuesta enviada correctamente!');
              }else throw d.msg || ''; })
            .catch(errMsg => {
                 console.error(errMsg);
                  if (errMsg !== 'No autorizado') {
                       showNotification('Error al enviar respuesta: ' + (errMsg || 'Desconocido'), 'danger');
                  }
            })
            .finally(()=>{btn.disabled=false;btn.textContent='Enviar';});
        });

    } // FIN del bloque else si hay token
} // FIN del bloque else si hay usuario autenticado
</script>
</body></html>