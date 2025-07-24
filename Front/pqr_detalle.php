<?php /* Front/pqr_detalle.php */
$id = (int)($_GET['id'] ?? 0);
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><title>PQR detalle</title>

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
    <h1 class="h5 mb-0 flex-grow-1 text-truncate" id="title">PQR</h1>
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

<script>
/* ------- rutas ------- */
const END_RESP = '../api/pqr_respuestas.php?pqr_id=<?=$id?>';
const END_SEND = '../api/pqr_insert_form.php';

/* ------- obtener usuario autenticado ------- */
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
// Aunque obtenemos u.id del localStorage, la API de PQR listado ya no lo usa de la URL
// La API valida con el token y obtiene el ID del usuario/responsable.
// Sin embargo, mantenemos esta verificación básica de u.id para deshabilitar la UI si no hay usuario logueado.
if(!u.id) { 
    // alert('Usuario no autenticado'); // Evitar alert en carga automática
    // location.href='login.php'; // Redirigir si no hay usuario logueado
    // Mejor, solo deshabilitar la UI y mostrar mensaje en headBox si no hay usuario
    document.getElementById('wrap').innerHTML = '<div class="alert alert-warning">Debes iniciar sesión para ver los detalles del PQR.</div>';
    // No ejecutar el resto del script si no hay usuario
} else {

    // Ya no se usa u.id en la URL para la API listado; el backend lo obtiene del token
    // const END_PQR  = `../api/pqr_list.php?id_usuario=${u.id}&estado_id=0&pqr_id=<?=$id?>`;
    // Ahora solo enviamos el pqr_id en la URL
    const END_PQR  = `../api/pqr_list.php?pqr_id=<?=$id?>`; // solo 1 registro

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

    // Verificar si hay token antes de hacer cualquier solicitud a APIs protegidas
    if (!token) {
        // Mostrar mensaje y deshabilitar UI si no hay token
         headBox.innerHTML = '<div class="alert alert-warning">Tu sesión ha expirado o no estás autorizado para ver este PQR. Por favor, inicia sesión de nuevo.</div>';
         if (frmRespuesta) frmRespuesta.style.display = 'none';

    } else {


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
          /* r.responsable_id es 0, null o un número › 0 */
          const esResp = Number(r.responsable_id) > 0;

          const dirClass = esResp ? 'right' : '';
          const foto = r.url_foto || 'https://via.placeholder.com/40x40?text=%20'; // Considera usar la foto real del usuario/responsable

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

          // Ocultar después de unos segundos
          setTimeout(() => {
            alertDiv.remove(); // Eliminar el elemento de la notificación
          }, 5000); // Ocultar después de 5 segundos
        }


        /* ------- cabecera PQR ------- */
        // Incluir el token en la solicitud a la API de lista de PQRs
        fetch(END_PQR, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(r => {
            if (r.status === 401) {
                // Manejar no autorizado - mostrar mensaje y posiblemente redirigir
                headBox.innerHTML = '<div class="alert alert-warning">Tu sesión ha expirado o no estás autorizado para ver este PQR. Por favor, inicia sesión de nuevo.</div>';
                if (frmRespuesta) frmRespuesta.style.display = 'none'; // Asegurarse de que frmRespuesta existe antes de intentar ocultarlo
                // Opcional: Redirigir a la página de login
                // setTimeout(() => { location.href = 'login_front.php'; }, 2000);
                 return Promise.reject('No autorizado');
            }
            return r.json();
        })
        .then(d=>{
          if(!d.ok||!d.pqr[0]) {
              // Manejar caso donde no se encuentra el PQR o hay error del backend
              headBox.innerHTML = `<div class="alert alert-danger">Error al cargar el PQR o no encontrado: ${d.mensaje || 'Desconocido'}</div>`;
              if (frmRespuesta) frmRespuesta.style.display = 'none';
              return;
          }
          const p = d.pqr[0];
          titleEl.textContent = p.subtipo;
          headBox.innerHTML = `
            <h2 class="h6 msg-head mb-1">${p.subtipo}</h2>
            <p class="mb-1">
              <span class="badge bg-secondary me-1">${p.tipo}</span>
              ${badgeEstado(p.estado)}
            </p>
            <p class="small text-muted mb-2">${p.manzana}/${p.villa} · ${fechaHora(p.fecha_ingreso)}</p>
            <div class="p-3 rounded bg-white border">${p.descripcion}</div>`;

          // Si el PQR está cerrado, ocultar el formulario de respuesta
          if (p.estado.toLowerCase().includes('cerr')) {
          if (frmRespuesta) frmRespuesta.style.display = 'none';
          }

        })
         .catch(err => {
           console.error(err);
            if (err !== 'No autorizado') {
                 headBox.innerHTML = '<div class="alert alert-danger">Error al conectar con el servidor para obtener el PQR.</div>';
                 if (frmRespuesta) frmRespuesta.style.display = 'none';
            }
        });

        /* ------- respuestas ------- */
        // Incluir el token en la solicitud a la API de respuestas
        function loadRespuestas() {
          fetch(END_RESP, {
              headers: {
                  'Authorization': `Bearer ${token}`
              }
          }).then(r => {
              if (r.status === 401) {
                 chat.innerHTML='<li class="text-warning">Tu sesión ha expirado o no estás autorizado para ver las respuestas.</li>';
                  // Opcional: Redirigir a la página de login
                  // setTimeout(() => { location.href = 'login_front.php'; }, 2000);
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
            chat.innerHTML = d.respuestas.length
              ? d.respuestas.map(msgHTML).join('')
              : '<li class="text-muted">— Sin respuestas —</li>';
          })
          .catch(err=>{
               console.error(err);
              if (err !== 'No autorizado') {
                 chat.innerHTML='<li class="text-danger">Error al conectar con el servidor de respuestas</li>';
             }
          });
        }
        loadRespuestas();

        /* ------- envío de nueva respuesta ------- */
        // Incluir el token en la solicitud a la API de envío de respuesta
        frmRespuesta.addEventListener('submit',e=>{
          e.preventDefault();
          if(!frmRespuesta.checkValidity()){ frmRespuesta.classList.add('was-validated'); return; }

          // No enviamos usuario_id en el FormData; el backend lo obtiene del token
          const fd = new FormData(frmRespuesta);
          fd.append('pqr_id', <?=$id?>);
          // fd.append('usuario_id', u.id); // <-- ELIMINADO

          btnSend.disabled=true; btnSend.textContent='Enviando…';

          fetch(END_SEND,{method:'POST',body:fd,
              headers: {
                  'Authorization': `Bearer ${token}`
                  // No necesitas Content-Type: multipart/form-data aquí; fetch lo establece automáticamente con FormData
              }
           })
            .then(r => {
                if (r.status === 401) {
                     showNotification('Tu sesión ha expirado o no estás autorizado para responder. Por favor, inicia sesión de nuevo.', 'danger');
                     // Opcional: Redirigir a la página de login
                     // setTimeout(() => { location.href = 'login_front.php'; }, 2000);
                     return Promise.reject('No autorizado');
                }
                return r.json();
            })
            .then(d=>{
              if(d.ok){
                txtMensaje.value = ''; // Clear the textarea
                const fileInput = frmRespuesta.querySelector('input[type="file"]');
                if(fileInput) fileInput.value = '';

                frmRespuesta.classList.remove('was-validated');
                loadRespuestas(); // Reload messages
                showNotification('Respuesta enviada correctamente!'); // Mostrar notificación de éxito
              }else throw d.msg || ''; }) // Lanzar mensaje de error del backend si existe
            .catch(errMsg=>{
                console.error(errMsg);
                 if (errMsg !== 'No autorizado') {
                    showNotification('Error al enviar respuesta: ' + (errMsg || 'Desconocido'), 'danger');
                 }
            })
            .finally(()=>{btnSend.disabled=false;btnSend.textContent='Enviar';});
        });

    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>