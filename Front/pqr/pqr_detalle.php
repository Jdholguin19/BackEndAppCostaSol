<?php /* Front/pqr/pqr_detalle.php */
$id = (int)($_GET['id'] ?? 0);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PQR Detalle | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../assets/css/style_main.css" rel="stylesheet">
<link href="../assets/css/style_pqr_detalle.css" rel="stylesheet">
</head>
<body>

<!-- Header Section -->
<div class="pqr-detalle-header">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <div>
      <h1 class="pqr-detalle-title" id="title">PQR</h1>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="pqr-detalle-container">
  <!-- detalle principal -->
  <div id="headBox" class="pqr-detalle-box"></div>

  <!-- hilo -->
  <ul id="chat" class="chat-list"></ul>

  <!-- caja nueva respuesta -->
  <form id="frmRespuesta" enctype="multipart/form-data" class="response-form">
    <textarea id="txtMensaje" name="mensaje" class="response-textarea" rows="3" placeholder="Escriba su respuesta…" required></textarea>
    <div class="response-actions">
      <input type="file" name="archivo" accept="image/*,application/pdf" class="file-input">
      <button id="btnSend" class="send-button" type="submit">
        <i class="bi bi-send"></i>
        Enviar
      </button>
    </div>
  </form>

  <!-- Área para mostrar notificaciones de éxito -->
  <div id="notificationArea" class="notification-area"></div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ------- rutas ------- */
const END_RESP = '../../api/pqr/pqr_respuestas.php?pqr_id=<?=$id?>';
const END_SEND = '../../api/pqr/pqr_insert_form.php';
const END_UPDATE_ESTADO = '../../api/pqr/pqr_update_estado.php'; // Nuevo endpoint

/* ------- obtener usuario autenticado ------- */
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
if(!u.id) { 
    document.getElementById('wrap').innerHTML = '<div class="alert alert-warning">Debes iniciar sesión para ver los detalles del PQR.</div>';
} else { // Inicio del bloque else si hay usuario autenticado

    const END_PQR  = `../../api/pqr/pqr_list.php?pqr_id=<?=$id?>`;

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

    // Variable para almacenar el estado actual del PQR (para revertir si falla la actualización)
    let currentPqrEstadoId = null;


    // Verificar si hay token antes de hacer cualquier solicitud a APIs protegidas
    if (!token) { // Inicio del bloque if si no hay token
         headBox.innerHTML = '<div class="alert alert-warning">Tu sesión ha expirado o no estás autorizado para ver este PQR. Por favor, inicia sesión de nuevo.</div>';
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
          const mensaje = r.mensaje.replace(/\n/g, '<br>'); // Convertir saltos de línea en HTML

          return `<li class="${dirClass}">
                    <div>
                      <div class="bubble">${mensaje}</div>
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

        /* ------- cabecera PQR ------- */
        fetch(END_PQR, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(r => {
            if (r.status === 401) {
                headBox.innerHTML = '<div class="alert alert-warning">Tu sesión ha expirado o no estás autorizado para ver este PQR. Por favor, inicia sesión de nuevo.</div>';
                if (frmRespuesta) frmRespuesta.style.display = 'none';
                 return Promise.reject('No autorizado');
            }
            return r.json();
        })
        .then(d=>{
          if(!d.ok||!d.pqr[0]) {
              headBox.innerHTML = `<div class="alert alert-danger">Error al cargar el PQR o no encontrado: ${d.mensaje || 'Desconocido'}</div>`;
              if (frmRespuesta) frmRespuesta.style.display = 'none';
              return;
          }
          const p = d.pqr[0];

          // Actualizar el estado actual del PQR globalmente
          currentPqrEstadoId = p.estado_id;

          // Mostrar Manzana/Villa en el título si están disponibles
          const mzVillaTitle = (p.manzana || p.villa) ? ` · Mz ${p.manzana} – Villa ${p.villa}` : '';
          titleEl.textContent = `${mzVillaTitle}`; // Establecer el título con el y Mz/Villa


          headBox.innerHTML = `
            <p class="mb-1">
              <span class="badge bg-secondary me-1">${p.tipo}</span>
              ${badgeEstado(p.estado)}
            </p>
            <p class="small text-muted mb-2">${fechaHora(p.fecha_ingreso)}</p>
            <div class="p-3 rounded bg-white border">${p.descripcion}</div>`;

          // Si el PQR está cerrado, ocultar el formulario de respuesta
          if (p.estado.toLowerCase().includes('cerr')) {
          if (frmRespuesta) frmRespuesta.style.display = 'none';
          }

          // --- INICIO: Lógica para Responsables ---
          if (isResponsable) {
              addEstadoDropdown(p.estado_id); // p.estado_id debería venir en la respuesta de pqr_list.php
          }
          // --- FIN: Lógica para Responsables ---

        })
         .catch(err => {
           console.error(err);
            if (err !== 'No autorizado') {
                 headBox.innerHTML = '<div class="alert alert-danger">Error al conectar con el servidor para obtener el PQR.</div>';
                 if (frmRespuesta) frmRespuesta.style.display = 'none';
            }
        });

        /* ------- Función para agregar menú desplegable de estado (para responsables) ------- */
        function addEstadoDropdown(currentEstadoId) {
            if (document.getElementById('selEstadoPQR')) {
                return; // Ya existe, no añadir de nuevo
            }

            const selectHtml = `
              <div class="estado-selector-container">
                <select id="selEstadoPQR" class="form-select form-select-sm w-auto ms-3">
                  <!-- Opciones se cargarán aquí -->
                </select>
              </div>`;

            const titleElement = document.getElementById('title');
            if (titleElement) {
                titleElement.insertAdjacentHTML('afterend', selectHtml);
            }

            const selEstadoPQR = document.getElementById('selEstadoPQR');

            fetch('../../api/pqr/pqr_estados.php')
                .then(r => r.json())
                .then(d => {
                    if (d.ok && d.estados) {
                        d.estados.forEach(estado => {
                            
                            const option = document.createElement('option');
                            option.value = estado.id;
                            option.textContent = estado.nombre;
                            if (estado.id == currentEstadoId) {
                                option.selected = true;
                            }
                            selEstadoPQR.appendChild(option);
                        });

                        selEstadoPQR.addEventListener('change', handleEstadoChange);

                    } else {
                        console.error('Error al cargar estados de PQR:', d.msg);
                        if (selEstadoPQR) {
                             selEstadoPQR.innerHTML = '<option value="">Error al cargar</option>';
                             selEstadoPQR.disabled = true;
                        }
                    }
                })
                .catch(err => {
                    console.error('Error fetching PQR states:', err);
                     if (selEstadoPQR) {
                         selEstadoPQR.innerHTML = '<option value="">Error de red</option>';
                         selEstadoPQR.disabled = true;
                    }
                });
        }

        /* ------- Función para manejar el cambio de estado ------- */
        function handleEstadoChange(event) {
            const newEstadoId = event.target.value;
            const pqrId = <?=$id?>;

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
                    pqr_id: pqrId,
                    estado_id: newEstadoId
                })
            })
            .then(r => {
                if (r.status === 401 || r.status === 403) {
                     showNotification('No tienes permiso para cambiar el estado del PQR.', 'danger');
                     // Revertir la selección del dropdown si no está autorizado
                     event.target.value = currentPqrEstadoId; // Usar el estado global almacenado
                     return Promise.reject('Permiso denegado o no autorizado');
                }
                return r.json();
            })
            .then(d => {
                if (d.ok) {
                    showNotification('Estado del PQR actualizado correctamente.');
                    // Actualizar el estado actual global
                    currentPqrEstadoId = newEstadoId;
                     // Actualizar la visualización del badge de estado
                     updateEstadoBadge(newEstadoId, event.target.options[event.target.selectedIndex].text);

                     // Si el estado cambia a cerrado, ocultar el formulario de respuesta
                     if (event.target.options[event.target.selectedIndex].text.toLowerCase().includes('cerr')) {
                         if (frmRespuesta) frmRespuesta.style.display = 'none';
                     } else {
                          if (frmRespuesta) frmRespuesta.style.display = 'block';
                     }

                } else {
                    showNotification('Error al actualizar el estado del PQR: ' + (d.msg || 'Desconocido'), 'danger');
                     // Revertir la selección del dropdown si falla la actualización
                     event.target.value = currentPqrEstadoId;
                }
            })
            .catch(err => {
                console.error('Error updating PQR state:', err);
                 if (err !== 'Permiso denegado o no autorizado') {
                     showNotification('Error de red al actualizar el estado del PQR.', 'danger');
                 }
                 // Revertir la selección del dropdown en caso de error de red
                 event.target.value = currentPqrEstadoId;
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
                 console.error('Error en API pqr_respuestas:', d.msg);
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
          fd.append('pqr_id', <?=$id?>);

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

        /* ------- Envío con Enter ------- */
        txtMensaje.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // Prevenir salto de línea
                
                // Solo enviar si hay texto
                if (txtMensaje.value.trim()) {
                    frmRespuesta.dispatchEvent(new Event('submit'));
                }
            }
        });

    } // FIN del bloque else si hay token
} // FIN del bloque else si hay usuario autenticado
</script>
</body></html>