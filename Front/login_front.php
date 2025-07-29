<?php /* Front/login_front.php – pantalla de inicio de sesión */ ?>
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
    try {
      await OneSignal.init({
        appId: "e77613c2-51f8-431d-9892-8b2463ecc817",
        safari_web_id: "web.onesignal.auto.5130fec1-dc87-4e71-b719-29a6a70279c4",
        notifyButton: {
          enable: true,
        },
        allowLocalhostAsSecureOrigin: true
      });

      console.log("OneSignal SDK inicializado.");
      
      // Escuchar cuando el usuario se suscriba
      OneSignal.User.PushSubscription.addEventListener('change', (event) => {
        console.log('Cambio en suscripción:', event);
        if (event.current.id) {
          console.log('Nuevo Player ID obtenido:', event.current.id);
          // Si hay datos de login guardados, enviar el Player ID
          const token = localStorage.getItem('cs_token');
          const userData = localStorage.getItem('cs_usuario');
          if (token && userData) {
            const user = JSON.parse(userData);
            sendPlayerIdToBackend(user.id, token, event.current.id);
          }
        }
      });

    } catch (e) {
        console.error("Error durante la inicialización de OneSignal:", e);
    }
  });

/* --- API para actualizar Player ID --- */
const API_UPDATE_PLAYER_ID = '../api/update_player_id.php';

/* --- Función para enviar el Player ID al backend --- */
async function sendPlayerIdToBackend(userId, token, playerId) {
    if (!userId || !token || !playerId) {
        console.warn("No se puede enviar Player ID al backend: falta userId, token o playerId");
        return;
    }
    try {
        console.log("Enviando Player ID al backend:", playerId);
        const response = await fetch(API_UPDATE_PLAYER_ID, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ user_id: userId, onesignal_player_id: playerId })
        });
        const data = await response.json();
        if (data.ok) {
            console.log("OneSignal Player ID guardado en backend para usuario", userId);
        } else {
            console.error("Error al guardar OneSignal Player ID en backend:", data.mensaje);
        }
    } catch (error) {
        console.error("Error de red al enviar Player ID al backend:", error);
    }
}

/* --- Función para obtener Player ID con reintentos --- */
async function getPlayerIdWithRetries(maxRetries = 5, delay = 2000) {
    for (let i = 0; i < maxRetries; i++) {
        try {
            // Verificar si OneSignal está listo
            if (window.OneSignal && window.OneSignal.User && window.OneSignal.User.PushSubscription) {
                const subscription = window.OneSignal.User.PushSubscription;
                
                // Verificar si ya está suscrito
                if (subscription.id) {
                    console.log(`Player ID obtenido en intento ${i + 1}:`, subscription.id);
                    return subscription.id;
                }
                
                // Si no está suscrito, intentar obtener el permiso
                if (subscription.optedIn === false) {
                    console.log(`Intento ${i + 1}: Solicitando permiso de notificaciones...`);
                    await window.OneSignal.Notifications.requestPermission();
                    
                    // Esperar un poco para que se procese
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    
                    // Verificar de nuevo
                    if (subscription.id) {
                        console.log(`Player ID obtenido después de solicitar permiso en intento ${i + 1}:`, subscription.id);
                        return subscription.id;
                    }
                }
            }
        } catch (error) {
            console.error(`Error en intento ${i + 1} de obtener Player ID:`, error);
        }
        
        // Esperar antes del siguiente intento
        if (i < maxRetries - 1) {
            console.log(`Esperando ${delay}ms antes del siguiente intento...`);
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }
    
    console.warn("No se pudo obtener el Player ID después de", maxRetries, "intentos");
    return null;
}

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
// const API_UPDATE_PLAYER_ID ya está definida arriba
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
  ev.preventDefault(); 
  msg.textContent='';
  
  const payload = {
    correo    : document.getElementById('correo').value.trim(),
    contrasena: document.getElementById('contrasena').value,
    perfil    : perfil
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
    console.log('Datos de usuario a guardar en localStorage:', data.user);
    localStorage.setItem('cs_token',  data.token);
    localStorage.setItem('cs_usuario',JSON.stringify(data.user));

    /* --- Intentar obtener y enviar Player ID después del login exitoso --- */
    console.log("--- Iniciando proceso para obtener Player ID después de login exitoso ---");
    
    // Función para intentar obtener y enviar el Player ID
    const handlePlayerIdAfterLogin = async () => {
      try {
        // Esperar un poco para que OneSignal se inicialice completamente
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        const playerId = await getPlayerIdWithRetries();
        
        if (playerId && data.user && data.token) {
          console.log("Player ID obtenido exitosamente, enviando al backend:", playerId);
          await sendPlayerIdToBackend(data.user.id, data.token, playerId);
        } else {
          console.warn("No se pudo obtener el Player ID o faltan datos del usuario");
          
          // Como backup, intentar obtener el Player ID más tarde
          setTimeout(async () => {
            console.log("Intento tardío de obtener Player ID...");
            const latePlayerId = await getPlayerIdWithRetries(3, 3000);
            if (latePlayerId) {
              const currentToken = localStorage.getItem('cs_token');
              const currentUser = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
              if (currentToken && currentUser.id) {
                await sendPlayerIdToBackend(currentUser.id, currentToken, latePlayerId);
              }
            }
          }, 5000);
        }
      } catch (error) {
        console.error("Error en handlePlayerIdAfterLogin:", error);
      }
    };
    
    // Ejecutar el manejo del Player ID en paralelo (no bloquear la redirección)
    handlePlayerIdAfterLogin();

    /* redirigir segun el tipo de usuario */
    // Pequeño delay para dar tiempo al proceso del Player ID
    setTimeout(() => {
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
    }, 500);

  }catch(err){
    console.error(err);
    msg.textContent = 'Error de conexión';
    console.error('Login error: ' + err.message);
  }
});
</script>

</body>
</html>
