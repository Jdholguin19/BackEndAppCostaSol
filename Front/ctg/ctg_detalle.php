<?php /* Front/ctg/ctg_detalle.php */
$id = (int)($_GET['id'] ?? 0);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CTG Detalle | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../assets/css/style_main.css" rel="stylesheet">
<link href="../assets/css/style_ctg_detalle.css" rel="stylesheet">
</head>
<body>

<!-- Header Section -->
<div class="ctg-detalle-header">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <button id="btnToggleObservaciones" class="toggle-observaciones-button header-button" type="button">
      <i class="bi bi-journal-text"></i>
    </button>
    <div>
      <h1 class="ctg-detalle-title" id="title">CTG</h1>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="ctg-detalle-container">
  <!-- detalle principal -->
  <div id="headBox" class="ctg-detalle-box"></div>

  <!-- hilo -->
  <ul id="chat" class="chat-list"></ul>

  <!-- caja nueva respuesta -->
  <form id="frmRespuesta" enctype="multipart/form-data" class="response-form">
    <div class="input-area">
      <input type="file" name="archivo" accept="image/*,application/pdf" class="file-input" id="fileInputHidden">
      <button id="btnAttachFile" class="attach-file-button" type="button">
        <i class="bi bi-paperclip"></i>
      </button>
      <textarea id="txtMensaje" name="mensaje" class="response-textarea" rows="1" placeholder="Escriba su respuesta…" required></textarea>
      <button id="btnSend" class="send-button" type="submit">
        <i class="bi bi-send"></i>
      </button>
    </div>
  </form>

  <!-- Área para mostrar notificaciones de éxito -->
  <div id="notificationArea" class="notification-area"></div>

  <!-- Área de observaciones (solo visible para responsables) -->
  <div id="observacionesContainer" class="observaciones-container">
    <label for="txtObservaciones" class="observaciones-label">
      <i class="bi bi-journal-text"></i> Observaciones del cliente
    </label>
    <textarea 
      id="txtObservaciones" 
      class="observaciones-textarea" 
      placeholder="Escriba aquí las observaciones sobre este cliente..."
      maxlength="700"
    ></textarea>
    <div class="observaciones-actions">
      <button id="btnSaveObservaciones" class="observaciones-save-btn" type="button">
        <i class="bi bi-check-circle"></i>
        Guardar observaciones
      </button>
    </div>
  </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ------- rutas ------- */
const END_RESP = '../../api/ctg/ctg_respuestas.php?ctg_id=<?=$id?>';
const END_SEND = '../../api/ctg/ctg_insert_form.php';
const END_UPDATE_ESTADO = '../../api/ctg/ctg_update_estado.php'; // Nuevo endpoint
const END_OBSERVACIONES = '../../api/ctg/ctg_observaciones.php?ctg_id=<?=$id?>';
const END_UPDATE_OBSERVACIONES = '../../api/ctg/ctg_update_observaciones.php';

/* ------- obtener usuario autenticado ------- */
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
if(!u.id) { 
    document.getElementById('wrap').innerHTML = '<div class="alert alert-warning">Debes iniciar sesión para ver los detalles del CTG.</div>';
} else { // Inicio del bloque else si hay usuario autenticado

    /* ------- MARCAR NOTIFICACIONES COMO LEÍDAS ------- */
    function markNotificationsAsRead() {
        const ctgId = <?=$id?>;
        if (!ctgId) return;

        const token = localStorage.getItem('cs_token');
        if (!token) return; // No hacer nada si no hay token

        fetch('../../api/notificaciones_mark_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                type: 'ctg',
                id: ctgId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log('Notificaciones de CTG marcadas como leídas.');
            } else {
                console.error('Error al marcar notificaciones como leídas:', data.message);
            }
        })
        .catch(error => {
            console.error('Error de red al marcar notificaciones:', error);
        });
    }
    // Llamar a la función al cargar la página si hay un usuario logueado
    markNotificationsAsRead();

    const END_CTG  = `../../api/ctg/ctg_list.php?ctg_id=<?=$id?>`;

    /* ------- refs DOM ------- */
    const titleEl = document.getElementById('title');
    const headBox = document.getElementById('headBox');
    const chat    = document.getElementById('chat');
    const frmRespuesta = document.getElementById('frmRespuesta');
    const btnSend = document.getElementById('btnSend');
    const txtMensaje = document.getElementById('txtMensaje');
    const notificationArea = document.getElementById('notificationArea');
    const observacionesContainer = document.getElementById('observacionesContainer');
    const txtObservaciones = document.getElementById('txtObservaciones');
    const btnSaveObservaciones = document.getElementById('btnSaveObservaciones');
    const btnToggleObservaciones = document.getElementById('btnToggleObservaciones');
    const btnAttachFile = document.getElementById('btnAttachFile');
    const fileInputHidden = document.getElementById('fileInputHidden');

    // Obtener el token de localStorage
    const token = localStorage.getItem('cs_token');

    // Variable para almacenar las respuestas actuales y comparar
    let currentResponses = [];

    // Verificar si el usuario es responsable
    const isResponsable = u.is_responsable || false;

    // Variable para almacenar el estado actual del CTG (para revertir si falla la actualización)
    let currentCtgEstadoId = null;

    // Ocultar el botón de alternar observaciones si no es responsable
    if (!isResponsable && btnToggleObservaciones) {
        btnToggleObservaciones.style.display = 'none';
    }

    // Ocultar el contenedor de observaciones por defecto
    if (observacionesContainer) {
        observacionesContainer.classList.remove('show');
    }


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

        /* ------- Funciones para observaciones ------- */
        function loadObservaciones() {
          fetch(END_OBSERVACIONES, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          })
          .then(r => {
            if (r.status === 401) {
              showNotification('Tu sesión ha expirado o no estás autorizado para ver las observaciones.', 'danger');
              return Promise.reject('No autorizado');
            }
            if (r.status === 403) {
              showNotification('No tienes permisos para ver las observaciones de este CTG.', 'warning');
              return Promise.reject('Sin permisos');
            }
            return r.json();
          })
          .then(d => {
            if (d.ok) {
              txtObservaciones.value = d.observaciones || '';
            } else {
              console.error('Error al cargar observaciones:', d.mensaje);
            }
          })
          .catch(err => {
            console.error('Error al cargar observaciones:', err);
            if (err !== 'No autorizado' && err !== 'Sin permisos') {
              showNotification('Error al cargar las observaciones.', 'danger');
            }
          });
        }

        function saveObservaciones() {
          const observaciones = txtObservaciones.value.trim();
          
          btnSaveObservaciones.disabled = true;
          btnSaveObservaciones.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

          const formData = new FormData();
          formData.append('ctg_id', <?=$id?>);
          formData.append('observaciones', observaciones);

          fetch(END_UPDATE_OBSERVACIONES, {
            method: 'POST',
            body: formData,
            headers: {
              'Authorization': `Bearer ${token}`
            }
          })
          .then(r => {
            if (r.status === 401) {
              showNotification('Tu sesión ha expirado o no estás autorizado para guardar observaciones.', 'danger');
              return Promise.reject('No autorizado');
            }
            if (r.status === 403) {
              showNotification('No tienes permisos para guardar observaciones en este CTG.', 'warning');
              return Promise.reject('Sin permisos');
            }
            return r.json();
          })
          .then(d => {
            if (d.ok) {
              showNotification('Observaciones guardadas correctamente!');
            } else {
              throw new Error(d.mensaje || 'Error desconocido');
            }
          })
          .catch(err => {
            console.error('Error al guardar observaciones:', err);
            if (err !== 'No autorizado' && err !== 'Sin permisos') {
              showNotification('Error al guardar las observaciones: ' + err.message, 'danger');
            }
          })
          .finally(() => {
            btnSaveObservaciones.disabled = false;
            btnSaveObservaciones.innerHTML = '<i class="bi bi-check-circle"></i> Guardar observaciones';
          });
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
                        // Actualizar el estado actual del CTG globalmente ANTES de crear el dropdown
          currentCtgEstadoId = p.estado_id;
          
          // Si no hay estado_id, intentar encontrar por nombre
          if (!p.estado_id && p.estado) {
              addEstadoDropdownByName(p.estado);
          } else {
              addEstadoDropdown(p.estado_id); // p.estado_id debería venir en la respuesta de ctg_list.php
          }
          
          // Mostrar área de observaciones para responsables
          observacionesContainer.classList.add('show');
          loadObservaciones();
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
            


            // Crear select simple pero con estilos personalizados
            const selectHTML = `
                <div class="estado-selector-container">
                    <select id="selEstadoCTG" class="custom-select">
                        <option value="">Cargando...</option>
                    </select>
                </div>
            `;

            // Insertar después del título
            const titleElement = document.getElementById('title');
            if (titleElement) {
                titleElement.insertAdjacentHTML('afterend', selectHTML);
            }

            // Cargar estados
            fetch('../../api/ctg/ctg_estados.php')
                .then(r => r.json())
                .then(d => {
                    if (d.ok && d.estados) {
                        const select = document.getElementById('selEstadoCTG');
                        
                        // Limpiar opciones
                        select.innerHTML = '';
                        
                        // Agregar opciones (excluir ID 1)
                        d.estados.forEach(estado => {
                            if (estado.id != 1) {
                                const option = document.createElement('option');
                                option.value = estado.id;
                                option.textContent = estado.nombre;
                                
                                // Marcar como seleccionado si coincide
                                if (estado.id == currentEstadoId) {
                                    option.selected = true;
                                }
                                
                                select.appendChild(option);
                            }
                        });
                        

                        
                        // Evento change
                        select.addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            if (selectedOption.value) {
                                handleEstadoChangeCustom(selectedOption.value, selectedOption.textContent);
                            }
                        });
                        
                    } else {
                        document.getElementById('selEstadoCTG').innerHTML = '<option value="">Error al cargar</option>';
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    document.getElementById('selEstadoCTG').innerHTML = '<option value="">Error de red</option>';
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

        /* ------- Función personalizada para manejar el cambio de estado ------- */
        function handleEstadoChangeCustom(newEstadoId, estadoNombre) {
            const ctgId = <?=$id?>;

            if (!newEstadoId) {
                console.warn("Seleccione un estado válido.");
                return;
            }

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
                    updateEstadoBadge(newEstadoId, estadoNombre);
                    
                    // Actualizar la opción seleccionada en el dropdown
                    updateDropdownSelection(newEstadoId, estadoNombre);

                    // Si el estado cambia a cerrado, ocultar el formulario de respuesta
                    if (estadoNombre.toLowerCase().includes('cerr')) {
                        if (frmRespuesta) frmRespuesta.style.display = 'none';
                    } else {
                         if (frmRespuesta) frmRespuesta.style.display = 'block';
                    }

                } else {
                    showNotification('Error al actualizar el estado del CTG: ' + (d.msg || 'Desconocido'), 'danger');
                }
            })
            .catch(err => {
                console.error('Error updating CTG state:', err);
                 if (err !== 'Permiso denegado o no autorizado') {
                     showNotification('Error de red al actualizar el estado del CTG.', 'danger');
                 }
            });
        }

        /* ------- Función para crear dropdown por nombre de estado ------- */
        function addEstadoDropdownByName(estadoNombre) {
            if (document.getElementById('selEstadoCTG')) {
                return; // Ya existe, no añadir de nuevo
            }
            


            // Crear select simple pero con estilos personalizados
            const selectHTML = `
                <div class="estado-selector-container">
                    <select id="selEstadoCTG" class="custom-select">
                        <option value="">Cargando...</option>
                    </select>
                </div>
            `;

            // Insertar después del título
            const titleElement = document.getElementById('title');
            if (titleElement) {
                titleElement.insertAdjacentHTML('afterend', selectHTML);
            }

            // Cargar estados
            fetch('../../api/ctg/ctg_estados.php')
                .then(r => r.json())
                .then(d => {
                    if (d.ok && d.estados) {
                        const select = document.getElementById('selEstadoCTG');
                        
                        // Limpiar opciones
                        select.innerHTML = '';
                        
                        // Agregar opciones (excluir ID 1)
                        d.estados.forEach(estado => {
                            if (estado.id != 1) {
                                const option = document.createElement('option');
                                option.value = estado.id;
                                option.textContent = estado.nombre;
                                
                                // Marcar como seleccionado si coincide el nombre
                                if (estado.nombre.toLowerCase() === estadoNombre.toLowerCase()) {
                                    option.selected = true;
                                }
                                
                                select.appendChild(option);
                            }
                        });
                        
                        // Evento change
                        select.addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            if (selectedOption.value) {
                                handleEstadoChangeCustom(selectedOption.value, selectedOption.textContent);
                            }
                        });
                        
                    } else {
                        document.getElementById('selEstadoCTG').innerHTML = '<option value="">Error al cargar</option>';
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    document.getElementById('selEstadoCTG').innerHTML = '<option value="">Error de red</option>';
                });
        }

        /* ------- Función para actualizar la selección del dropdown ------- */
        function updateDropdownSelection(estadoId, estadoNombre) {
            const selectedText = document.getElementById('selectedText');
            const dropdownOptions = document.getElementById('dropdownOptions');
            
            if (!selectedText || !dropdownOptions) return;
            
            // Actualizar texto del header
            selectedText.textContent = estadoNombre;
            
            // Remover selección anterior y marcar nueva
            dropdownOptions.querySelectorAll('.dropdown-option').forEach(opt => {
                opt.classList.remove('selected');
                if (opt.getAttribute('data-value') == estadoId) {
                    opt.classList.add('selected');
                }
            });
        }

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
        const pollingInterval = setInterval(loadAndDisplayResponses, 3000);

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
            .finally(()=>{btn.disabled=false;btn.textContent='➤';});
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

        /* ------- Event listener para adjuntar archivo ------- */
        if (btnAttachFile && fileInputHidden) {
            btnAttachFile.addEventListener('click', function() {
                fileInputHidden.click(); // Trigger click on the hidden file input
            });
        }

        /* ------- Event listener para guardar observaciones ------- */
        btnSaveObservaciones.addEventListener('click', function(e) {
            e.preventDefault();
            saveObservaciones();
        });

        /* ------- Event listener para alternar observaciones ------- */
        if (btnToggleObservaciones) {
            btnToggleObservaciones.addEventListener('click', function() {
                if (isResponsable) {
                    toggleObservacionesModal();
                }
            });
        }

        function toggleObservacionesModal() {
            const isShowing = observacionesContainer.classList.contains('show');
            if (isShowing) {
                // Hide modal
                observacionesContainer.classList.remove('show');
                const backdrop = document.getElementById('modalBackdrop');
                if (backdrop) {
                    backdrop.classList.remove('show');
                    setTimeout(() => backdrop.remove(), 300); // Remove after transition
                }
                document.body.style.overflow = ''; // Restore scroll
            } else {
                // Show modal
                let backdrop = document.getElementById('modalBackdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.id = 'modalBackdrop';
                    backdrop.classList.add('modal-backdrop');
                    document.body.appendChild(backdrop);
                    backdrop.addEventListener('click', toggleObservacionesModal); // Close on backdrop click
                }
                setTimeout(() => backdrop.classList.add('show'), 10); // Add show class after slight delay
                observacionesContainer.classList.add('show');
                loadObservaciones(); // Load observations when opened
                document.body.style.overflow = 'hidden'; // Prevent scroll
            }
        }

        /* ------- Auto-guardar observaciones al perder el foco ------- */
        txtObservaciones.addEventListener('blur', function() {
            // Solo auto-guardar si el usuario es responsable y hay cambios
            if (isResponsable && txtObservaciones.value.trim() !== '') {
                // Pequeño delay para evitar guardar mientras el usuario está escribiendo
                setTimeout(() => {
                    if (document.activeElement !== txtObservaciones) {
                        saveObservaciones();
                    }
                }, 1000);
            }
        });

    } // FIN del bloque else si hay token
} // FIN del bloque else si hay usuario autenticado
</script>
</body></html>