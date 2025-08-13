
<?php /* Front/register_front.php – pantalla de registro de usuario */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrarse | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">
<link href="assets/css/style_login.css" rel="stylesheet">

</head>
<body>

<!-- Main Content -->
<div class="login-container">
  <!-- CostaSol Logo -->
  <div class="text-center mb-4">
    <img src="https://app.costasol.com.ec/iconos/LogoCostaSolVerde.svg" alt="CostaSol logo" class="logo">
  </div>

  <div class="form-container">
    <form id="registerForm">
      <div class="form-group">
        <input type="text" class="form-control" id="nombres" placeholder="Nombres:" required>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" id="apellidos" placeholder="Apellidos:" required>
      </div>
      <div class="form-group">
        <input type="email" class="form-control" id="correo" placeholder="Correo:" required>
      </div>
      <div class="form-group">
        <input type="password" class="form-control" id="contrasena" placeholder="Contraseña:" required>
      </div>

      <button class="btn btn-login" type="submit" id="registerBtn">
        <span id="registerText">Registrarse</span>
        <span id="registerSpinner" style="display: none;">
          <div class="spinner-border spinner-border-sm" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </span>
      </button>
    </form>

    <div id="msg" class="error-message" style="display: none;"></div>
  </div>
</div>

<script>
const API_REGISTER = '../api/user_crud.php';
const form = document.getElementById('registerForm');
const msg = document.getElementById('msg');
const registerBtn = document.getElementById('registerBtn');
const registerText = document.getElementById('registerText');
const registerSpinner = document.getElementById('registerSpinner');

form.addEventListener('submit', async function(ev) {
  ev.preventDefault();

  // Hide previous messages
  msg.style.display = 'none';
  msg.className = 'error-message';

  // Show loading state
  registerBtn.disabled = true;
  registerText.style.display = 'none';
  registerSpinner.style.display = 'inline-block';

  const payload = {
    nombres: document.getElementById('nombres').value.trim(),
    apellidos: document.getElementById('apellidos').value.trim(),
    correo: document.getElementById('correo').value.trim(),
    contrasena: document.getElementById('contrasena').value,
    rol_id: 1 // Assuming 3 is the role ID for 'cliente'
  };

  try {
    const r = await fetch(API_REGISTER, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await r.json();

    if (!data.ok) {
      msg.textContent = data.mensaje || 'Error al registrar usuario';
      msg.style.display = 'block';
      msg.className = 'error-message';
      return;
    }

    // Success message
    msg.textContent = 'Registro exitoso. Redirigiendo al inicio de sesión...';
    msg.className = 'success-message';
    msg.style.display = 'block';

    // Redirect to login page after a short delay
    setTimeout(() => {
      window.location.href = 'login_front.php';
    }, 2000); // 2 second delay

  } catch (err) {
    console.error(err);
    msg.textContent = 'Error de conexión';
    msg.style.display = 'block';
    msg.className = 'error-message';
    console.error('Registration error: ' + err.message);
  } finally {
    // Reset button state
    registerBtn.disabled = false;
    registerText.style.display = 'inline';
    registerSpinner.style.display = 'none';
  }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>