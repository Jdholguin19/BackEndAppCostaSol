<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Nuevo PQR</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../assets/css/style_main.css" rel="stylesheet">
<link href="../assets/css/style_pqr_nuevo.css" rel="stylesheet">
</head>
<body class="pqr-nuevo-page">

<div class="pqr-container">
  <div class="pqr-header">
    <h1 class="pqr-header-title">Nuevo PQR</h1>
    <button class="pqr-back-btn" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
  </div>

  <form id="frmPqr" enctype="multipart/form-data" class="pqr-form needs-validation" novalidate>
    <!-- propiedad -->
    <label class="pqr-form-label">Propiedad</label>
    <select id="selProp" name="id_propiedad" class="pqr-form-select" required></select>

    <!-- tipo -->
    <label class="pqr-form-label">Tipo</label>
    <select id="selTipo" name="tipo_id" class="pqr-form-select" required></select>

    <!-- descripción -->
    <label class="pqr-form-label">Descripción</label>
    <textarea name="descripcion" rows="4" class="pqr-form-textarea"
              placeholder="Describa detalladamente su petición, queja o reclamo" required></textarea>

    <!-- adjunto -->
    <label class="pqr-form-label">Adjunto (opcional)</label>
    <div class="pqr-file-input-wrapper">
        <input type="file" name="archivo" accept="image/*,application/pdf" class="pqr-file-input">
    </div>

    <div class="pqr-button-group">
      <button type="button" class="pqr-btn-cancel" onclick="history.back()">Cancelar</button>
      <button class="pqr-btn-submit" type="submit" id="btnSend">Enviar</button>
    </div>
  </form>
</div>

<?php 
$active_page = 'pqr';
include '../../api/bottom_nav.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
// No redirigimos inmediatamente si no hay u.id, manejamos la falta de token al llamar a la API
// if(!u.id) location.href='login.php';

// Obtener el token de localStorage
const token = localStorage.getItem('cs_token');
const propSel  = document.getElementById('selProp');


// Si no hay token, deshabilitar formulario y mostrar mensaje
if (!token) {
    const container = document.querySelector('.pqr-container');
    if (container) {
        container.innerHTML = '<div class="alert alert-warning">Debes iniciar sesión para crear un nuevo PQR.</div>';
    }
} else {

      /* ---------- cargar propiedades ---------- */
      const token = localStorage.getItem('cs_token');
      fetch('../../api/obtener_propiedades.php?id_usuario='+u.id, {
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

    /* ---------- llenar tipos ---------- */
    // Asegúrate de que esta API (tipo_pqr.php) maneja token si es necesario
    fetch('../../api/pqr/tipo_pqr.php').then(r=>r.json()).then(d=>{
      if(d.ok){
        const sel=document.getElementById('selTipo');
        d.tipos.forEach(t=>sel.insertAdjacentHTML('beforeend',
            `<option value="${t.id}">${t.nombre}</option>`));
      }
    });



    /* ---------- envío ---------- */
    document.getElementById('frmPqr').addEventListener('submit',e=>{
      e.preventDefault();
      if(!e.target.checkValidity()){ e.target.classList.add('was-validated'); return; }

      const fd = new FormData(e.target);
      // fd.append('id_usuario',u.id); // <-- ELIMINADO: El backend obtiene el ID del token

      const btn=document.getElementById('btnSend');
      btn.disabled=true; btn.textContent='Enviando…';

      fetch('../../api/pqr/pqr_create.php',{method:'POST',body:fd,
          headers: {
              'Authorization': `Bearer ${token}`
              // No necesitas Content-Type: multipart/form-data aquí; fetch lo establece automáticamente con FormData
          }
       })
        .then(r => {
            if (r.status === 403) { // Manejar Prohibido (si responsables intentan crear PQR)
                 alert('No tienes permiso para crear PQRs.');
                 return Promise.reject('Permiso denegado');
            }
             if (r.status === 401) { // Manejar No autorizado
                 alert('Tu sesión ha expirado o no estás autorizado. Por favor, inicia sesión de nuevo.');
                 // Opcional: Redirigir a la página de login
                 // setTimeout(() => { location.href = 'login_front.php'; }, 2000);
                 return Promise.reject('No autorizado');
             }
            return r.json();
        })
        .then(d=>{
          if(d.ok){
            alert('Solicitud registrada Nº '+d.numero);
            location.href='pqr.php';
          }else throw d.msg || ''; }) // Lanzar mensaje de error del backend si existe
        .catch(errMsg => {
             console.error(errMsg);
              if (errMsg !== 'No autorizado' && errMsg !== 'Permiso denegado') {
                   alert('Error al registrar PQR: ' + (errMsg || 'Desconocido'));
              }
        })
        .finally(()=>{btn.disabled=false;btn.textContent='Enviar';});
    });

}
</script>
</body>
</html>