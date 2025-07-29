<?php /* Front/login.php – pantalla de inicio de sesión */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Iniciar sesión | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
html,body{height:100%}
body{
  display:flex;align-items:center;justify-content:center;
  background:#f3f5f7;font-family:system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif
}
.logo{width:120px}
.tab-btn{
  border:0;padding:.25rem 1rem;border-radius:999px;font-size:.85rem;
  background:#0f3d2f;color:#fff
}
.tab-btn.inactive{
  background:#f9f3e7;color:#0f3d2f
}
.tab-btn+ .tab-btn{margin-left:.25rem}
.input-round{
  border-radius:2rem;padding-left:1rem
}
.btn-primary-cs{
  background:#0f3d2f;border:none;border-radius:999px;padding:.5rem 2rem
}
.btn-primary-cs:hover{background:#0d3327}
.small-link{font-size:.75rem}
.form-box{
  width:100%;max-width:320px
}
</style>
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "e77613c2-51f8-431d-9892-8b2463ecc817",
      safari_web_id: "web.onesignal.auto.5130fec1-dc87-4e71-b719-29a6a70279c4",
      notifyButton: {
        enable: true,
      },
    });
  });
</script>
</head>
<body>

<main class="form-box">

  <div class="text-center mb-4">
    <img src="https://app.costasol.com.ec/iconos/LogoCostaSolVerde.svg" alt="CostaSol logo" class="logo">
  </div>

  <!-- selector residente / trabajador -->
  <div class="d-flex justify-content-center mb-4">
    <button id="btnResidente" class="tab-btn">Residente</button>
    <button id="btnTrabajador" class="tab-btn inactive">Trabajador</button>
  </div>

  <form id="loginForm" class="mb-2">
    <div class="mb-3">
      <input type="email" class="form-control input-round" id="correo" placeholder="Usuario:" required>
    </div>
    <div class="mb-2">
      <input type="password" class="form-control input-round" id="contrasena" placeholder="Contraseña:" required>
    </div>

    <div class="text-end small-link mb-3">
      <a href="#" class="link-secondary link-underline-opacity-0">Recuperar contraseña</a>
    </div>

    <div class="d-grid">
      <button class="btn btn-primary-cs" type="submit">Ingresar</button>
    </div>
  </form>

  <div id="msg" class="text-center text-danger small"></div>
</main>

<script>
const API_LOGIN = '../api/login.php';
const form = document.getElementById('loginForm');
const msg  = document.getElementById('msg');

/* --- selector de perfil (por si más adelante envías role_id) --- */
let perfil = 'residente';
document.getElementById('btnResidente').onclick = e=>{
  perfil='residente'; e.target.classList.remove('inactive');
  document.getElementById('btnTrabajador').classList.add('inactive');
};
document.getElementById('btnTrabajador').onclick = e=>{
  perfil='trabajador'; e.target.classList.remove('inactive');
  document.getElementById('btnResidente').classList.add('inactive');
};

/* --- envío --- */
form.addEventListener('submit', async ev=>{
  ev.preventDefault(); msg.textContent='';
  const payload = {
    correo    : document.getElementById('correo').value.trim(),
    contrasena: document.getElementById('contrasena').value,
    perfil    : perfil            // opcional; el backend puede ignorarlo
  };

  try{
    const r = await fetch(API_LOGIN,{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify(payload)
    });
    const data = await r.json();
    if(!data.ok){
      msg.textContent = data.mensaje || 'Credenciales incorrectas';
      return;
    }
    /* guardar token y usuario en LocalStorage */
    console.log('Datos de usuario a guardar en localStorage:', data.user); // Registro
    localStorage.setItem('cs_token',  data.token);
    localStorage.setItem('cs_usuario',JSON.stringify(data.user));

    /* redirigir segun el tipo de usuario */
    if (data.user.is_responsable) {
      window.location.href = 'menu_front.php'; // Redirect responsible users to menu_front.php
    } else {
      // Redirect based on user.rol_id for clients/residents
      if (data.user.rol_id === 1) {
        window.location.href = 'menu_front.php'; // Example: Redirect role 1 to client_dashboard.php
      } else if (data.user.rol_id === 2) {
        window.location.href = 'menu_front.php'; // Example: Redirect role 2 to resident_portal.php
      } else {
        // Default redirect for other roles or if rol_id is not handled
        window.location.href = 'menu_front.php'; // Example: Redirect to a default page
      }
    }

  }catch(err){
    console.error(err);
    msg.textContent = 'Error de conexión';
    console.error('Login error: ' + err.message);
  }
});
</script>

</body>
</html>
