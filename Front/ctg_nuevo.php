<!doctype html><html lang="es"><head>
<meta charset="utf-8"><title>Nuevo CTG</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f5f6f8}
.container{max-width:600px}
</style></head><body>
<div class="container py-4">
  <button class="btn btn-link" onclick="history.back()"><i class="bi bi-arrow-left"></i></button>
  <h1 class="h4 mb-4">Nuevo CTG</h1>

  <form id="frmCtg" enctype="multipart/form-data" class="card p-4 shadow-sm needs-validation" novalidate>
    <!-- propiedad -->
    <label class="form-label">Propiedad</label>
    <select id="selProp" name="id_propiedad" class="form-select mb-3" required></select>

  

    <!-- tipo -->
    <label class="form-label">Tipo</label>
    <select id="selTipo"   name="tipo_id"    class="form-select mb-3" required></select>

    <!-- subtipo -->
    <label class="form-label">Sub-tipo</label>
   <select id="selSub"    name="subtipo_id" class="form-select mb-3" required disabled>
      <option value="">— Seleccione tipo —</option>
    </select>

    <!-- descripción -->
    <label class="form-label">Descripción</label>
    <textarea name="descripcion" rows="4" class="form-control mb-3"
              placeholder="Describa detalladamente su petición, queja o reclamo" required></textarea>

    <!-- adjunto -->
    <label class="form-label">Adjunto (opcional)</label>
    <input type="file" name="archivo" accept="image/*,application/pdf" class="form-control mb-4">

    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-secondary w-50" onclick="history.back()">Cancelar</button>
      <button class="btn btn-primary w-50" type="submit" id="btnSend">Enviar</button>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const u = JSON.parse(localStorage.getItem('cs_usuario')||'{}');
// No redirigimos inmediatamente si no hay u.id, manejamos la falta de token al llamar a la API
// if(!u.id) location.href='login.php';

// Obtener el token de localStorage
const token = localStorage.getItem('cs_token');

// Si no hay token, deshabilitar formulario y mostrar mensaje
if (!token) {
    const container = document.querySelector('.container');
    if (container) {
        container.innerHTML = '<div class="alert alert-warning">Debes iniciar sesión para crear un nuevo CTG.</div>';
    }
} else {

    /* ---------- llenar propiedades ---------- */
    // Asegúrate de que esta API (obtener_propiedades.php) también maneja token si es necesario
    // Actualmente usa id_usuario en URL - si la cambiaste, ajusta aquí
    fetch('../api/obtener_propiedades.php?id_usuario='+u.id) // <-- Si obtener_propiedades.php usa token, ajusta esta llamada
      .then(r=>r.json()).then(d=>{
        if(!d.ok) return;
        const sel = document.getElementById('selProp');
        d.propiedades.forEach(p=>{
          sel.insertAdjacentHTML('beforeend',
            `<option value="${p.id}">${p.urbanizacion} — Mz ${p.manzana} / Villa ${p.solar}</option>`);
        });
      });

    /* ---------- llenar tipos ---------- */
    // Asegúrate de que esta API (tipo_ctg.php) maneja token si es necesario
    fetch('../api/tipo_ctg.php').then(r=>r.json()).then(d=>{
      if(d.ok){
        const sel=document.getElementById('selTipo');
        d.tipos.forEach(t=>sel.insertAdjacentHTML('beforeend',
            `<option value="${t.id}">${t.nombre}</option>`));
      }
    });




    /* ---------- cuando cambia tipo → cargar sub-tipos ---------- */
    document.getElementById('selTipo').addEventListener('change',e=>{
      const tid=e.target.value, selSub=document.getElementById('selSub');
      selSub.innerHTML='<option>— Cargando… —</option>'; selSub.disabled=true;
      // Asegúrate de que esta API (subtipo_ctg.php) maneja token si es necesario
      fetch('../api/subtipo_ctg.php?tipo_id='+tid).then(r=>r.json()).then(d=>{ // <-- Si subtipo_ctg.php usa token, ajusta esta llamada
          selSub.innerHTML='<option value="">— Seleccione —</option>';
          if(d.ok){
            d.subtipos.forEach(s=>selSub.insertAdjacentHTML('beforeend',
               `<option value="${s.id}">${s.nombre}</option>`));
          }
          selSub.disabled=false;
      });
    });

    /* ---------- envío ---------- */
    document.getElementById('frmCtg').addEventListener('submit',e=>{
      e.preventDefault();
      if(!e.target.checkValidity()){ e.target.classList.add('was-validated'); return; }

      const fd = new FormData(e.target);
      // fd.append('id_usuario',u.id); // <-- ELIMINADO: El backend obtiene el ID del token

      const btn=document.getElementById('btnSend');
      btn.disabled=true; btn.textContent='Enviando…';

      fetch('../api/ctg_create.php',{method:'POST',body:fd,
          headers: {
              'Authorization': `Bearer ${token}`
              // No necesitas Content-Type: multipart/form-data aquí; fetch lo establece automáticamente con FormData
          }
       })
        .then(r => {
            if (r.status === 403) { // Manejar Prohibido (si responsables intentan crear CTG)
                 alert('No tienes permiso para crear CTGs.');
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
            location.href='ctg.php';
          }else throw d.msg || ''; }) // Lanzar mensaje de error del backend si existe
        .catch(errMsg => {
             console.error(errMsg);
              if (errMsg !== 'No autorizado' && errMsg !== 'Permiso denegado') {
                   alert('Error al registrar CTG: ' + (errMsg || 'Desconocido'));
              }
        })
        .finally(()=>{btn.disabled=false;btn.textContent='Enviar';});
    });

}
</script>
</body></html>