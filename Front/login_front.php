<?php /* Front/login.php – pantalla de inicio de sesión */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar sesión | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">
<link href="assets/css/style_login.css" rel="stylesheet">

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

<!-- Main Content -->
<div class="login-container">
  <!-- CostaSol Logo -->
  <div class="text-center mb-4">
    <img src="https://app.costasol.com.ec/iconos/LogoCostaSolVerde.svg" alt="CostaSol logo" class="logo">
  </div>


  <div class="tab-container">
    <button id="btnResidente" class="tab-btn active">Iniciar sesión</button>
  </div>


  <div class="form-container">
    <form id="loginForm">
      <div class="form-group">
        <input type="email" class="form-control" id="correo" placeholder="Usuario:" required>
      </div>
      <div class="form-group">
        <input type="password" class="form-control" id="contrasena" placeholder="Contraseña:" required>
      </div>

      <a href="#" class="forgot-link"><center>Recuperar contraseña</center></a>

      <button class="btn btn-login" type="submit" id="loginBtn">
        <span id="loginText">Ingresar</span>
        <span id="loginSpinner" style="display: none;">
          <div class="spinner-border spinner-border-sm" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </span>
      </button>
    </form>
 <button class="btn btn-register" id="registerBtn">Registrarse</button> <div id="msg" class="error-message" style="display: none;"></div> </div>
</div>

<script>
const API_LOGIN = '../api/login.php';
const form = document.getElementById('loginForm');
const msg = document.getElementById('msg');
const loginBtn = document.getElementById('loginBtn');
const loginText = document.getElementById('loginText');
const loginSpinner = document.getElementById('loginSpinner');

/* --- redirección a registro --- */
const registerBtn = document.getElementById('registerBtn');
registerBtn.addEventListener('click', function() {
  window.location.href = 'register_front.php';
}); // Corrected: Added closing parenthesis

/* --- envío --- */
form.addEventListener('submit', async function(ev) {
  ev.preventDefault();
  
  // Hide previous messages
  msg.style.display = 'none';
  msg.className = 'error-message';
  
  // Show loading state
  loginBtn.disabled = true;
  loginText.style.display = 'none';
  loginSpinner.style.display = 'inline-block';
  
  const payload = {
    correo: document.getElementById('correo').value.trim(),
    contrasena: document.getElementById('contrasena').value
  };

  try {
    const r = await fetch(API_LOGIN, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    
    const data = await r.json();
    
    if (!data.ok) {
      msg.textContent = data.mensaje || 'Credenciales incorrectas';
      msg.style.display = 'block';
      return;
    }
    
    // Success message
    msg.textContent = 'Iniciando sesión...';
    msg.className = 'success-message';
    msg.style.display = 'block';
    
    /* guardar token y usuario en LocalStorage */
    console.log('Datos de usuario a guardar en localStorage:', data.user);
    localStorage.setItem('cs_token', data.token);
    localStorage.setItem('cs_usuario', JSON.stringify(data.user));

    /* redirigir segun el tipo de usuario */
    if (data.user.is_responsable) {
      window.location.href = 'menu_front.php';
    } else {
      if (data.user.rol_id === 1) {
        window.location.href = 'menu_front.php';
      } else if (data.user.rol_id === 2) {
        window.location.href = 'menu_front.php';
      } else {
        window.location.href = 'menu_front.php';
      }
    }

  } catch (err) {
    console.error(err);
    msg.textContent = 'Error de conexión';
    msg.style.display = 'block';
    console.error('Login error: ' + err.message);
  } finally {
    // Reset button state
    loginBtn.disabled = false;
    loginText.style.display = 'inline';
    loginSpinner.style.display = 'none';
  }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
