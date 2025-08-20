<?php /* Front/menu_front.php */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menú principal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">

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
          allowLocalhostAsSecureOrigin: true,
      });

      // --- INICIO: Código para obtener y enviar el player_id ---
      const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
      if (u.id) {
          try {
              // Esperar un momento para que OneSignal esté completamente listo
              await new Promise(resolve => setTimeout(resolve, 3000));
              
              // Obtener el Player ID usando la API correcta de v17
              let playerId = null;
              
              // Intentar obtener el Player ID de diferentes maneras según la versión
              try {
                  // Método para v17+
                  if (OneSignal.User && OneSignal.User.PushSubscription) {
                      playerId = OneSignal.User.PushSubscription.id;
                  } else if (OneSignal.getUserId) {
                      // Método alternativo para versiones anteriores
                      playerId = await OneSignal.getUserId();
                  }
              } catch (e) {
                  console.warn("Error al obtener Player ID con método principal:", e);
                  // Intentar método alternativo
                  try {
                      if (OneSignal.getUserId) {
                          playerId = await OneSignal.getUserId();
                      }
                  } catch (e2) {
                      console.warn("Error con método alternativo:", e2);
                  }
              }
              
              console.log("OneSignal Player ID:", playerId);

              if (playerId) {
                  // Enviar el player_id al backend
                  const token = localStorage.getItem('cs_token');
                  if (token) {
                      const response = await fetch('../api/update_player_id.php', {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                              'Authorization': `Bearer ${token}`
                          },
                          body: JSON.stringify({
                              onesignal_player_id: playerId
                          })
                      });
                      const data = await response.json();
                      if (data.ok) {
                          console.log("Player ID actualizado en el servidor:", data.mensaje);
                      } else {
                          console.error("Error al actualizar Player ID en el servidor:", data.mensaje);
                      }
                  } else {
                      console.warn("No hay token de autenticación para enviar el Player ID.");
                  }
              } else {
                  console.warn("OneSignal Player ID es null - usuario puede no estar suscrito");
              }
          } catch (error) {
              console.error("Error al obtener o enviar el OneSignal Player ID:", error);
          }
      } else {
          console.warn("Usuario no logueado, no se puede enviar el Player ID.");
      }
      // --- FIN: Código para obtener y enviar el player_id ---
  });
</script>
</head>
<body>

<!-- Header Section -->
<hr style="color:rgba(69, 67, 67, 0.33); margin-top: 20px; ">
<div class="header-section">
  <div class="welcome-card">
    <div class="welcome-info">
      <p class="welcome-name" id="welcomeName">Hola, Usuario</p>
    </div>
    <!-- Contenedor para el avatar y el botón de cerrar sesión -->
    <div class="avatar-container">
        <img src="" class="avatar" id="welcomeAvatar" alt="Avatar">
        <button id="logoutButton" class="logout-button" style="display: none;">Cerrar sesión</button>
    </div>
  </div>

</div>

<!-- Main Content -->
<div class="main-content">
  <div class="Titulo">
    <p><button class="back-button" id="btnBack"><i class="bi bi-arrow-left"></i></button> Volver</p>
  </div>

  <!-- Module Grid -->
  <div class="module-grid" id="menuGrid">
    <div class="d-flex justify-content-center py-5" id="menuSpinner">
      <div class="spinner-border"></div>
    </div>
  </div>


  <hr style="color:rgba(69, 67, 67, 0.33); margin-top: 25px; "></hr>
  <!-- Location Card -->
  <div class="location-card">
    <h4 style="font-family: 'Playfair Display'; font-size: 1rem; font-weight: 600;">Ubicación</h4>
      <div class="menu-card">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1037.9195830251224!2d-80.04656221303496!3d-2.192825715281358!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x902d77a10263cfcb%3A0xcee97561a1b5df50!2sUrbanizaci%C3%B3n%20CostaSol!5e0!3m2!1ses!2sec!4v1754579757268!5m2!1ses!2sec" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          <p>Urbanización CostaSol, Km 9.5 Vía a la Costa, Guayaquil</p>
      </div>
  </div>
  <hr style="color:rgba(69, 67, 67, 0.33); margin-top: 25px; "></hr>

</div>

<?php 
$active_page = 'inicio';
include '../api/bottom_nav.php'; 
?>


<script>
  /* ---------- USER ---------- */
  console.log('Valor en localStorage para cs_usuario:', localStorage.getItem('cs_usuario')); // Registro
  const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
  if (!u.id) location.href = 'login_front.php';

  const is_admin_responsible = (u.is_responsable && u.id === 3);

  /* ---------- Endpoints ---------- */
  const API_NEWS  = '../api/noticias.php?limit=10';
  const API_MENU  = is_admin_responsible ? '../api/menu.php' : '../api/menu.php?role_id=' + u.rol_id;
  const API_PROP  = '../api/obtener_propiedades.php?id_usuario=' + u.id;
  const API_FASE  = '../api/propiedad_fase.php?id_propiedad=';

  /* ---------- Welcome ---------- */
  document.getElementById('welcomeName').textContent = `Hola, ${u.nombres || u.nombre}`;
  document.getElementById('welcomeAvatar').src = u.url_foto_perfil || 'https://via.placeholder.com/48';

  /* ---------- Helpers ---------- */
  function icon(url){
    if (url?.startsWith('bi-')) {
      const i = document.createElement('i');
      i.className = `menu-icon bi ${url}`; return i;
    }
    const img = document.createElement('img');
    img.src = url || '/assets/img/default.svg'; img.width = 40; return img;
  }

  function svgCircle(percent){
    const r = 30, c = 2 * Math.PI * r, off = c - (percent/100) * c;
    return `
    <div class="progress-ring">
      <svg width="80" height="80" viewBox="0 0 80 80">
        <g transform="rotate(-90 40 40)">
          <circle cx="40" cy="40" r="${r}" stroke="#e5e7eb" stroke-width="6" fill="none"/>
          <circle cx="40" cy="40" r="${r}" stroke="#2d5a3d" stroke-width="6" fill="none"
                  stroke-dasharray="${c}" stroke-dashoffset="${off}" stroke-linecap="round"/>
        </g>
        <text x="40" y="44">${percent}<tspan font-size="12">%</tspan></text>
      </svg>
    </div>`;
  }

  function openModule(menu){
    // Si el nombre es CTG lanzamos ctg.php
    if ((menu.nombre || '').trim().toUpperCase() === 'CTG'){
        location.href = 'ctg/ctg.php';               // ctg.php leerá el usuario desde localStorage
        return;
    }

/*
    if ((menu.nombre || '').trim().toUpperCase() === 'Garantias'){
        location.href = 'garantias.php';              
        return;
    }*/

    if ( menu.id === 1 ||
        (menu.nombre || '').toUpperCase().includes('VISITA') ){
        location.href = 'seleccion_acabados.php';          // <<<<<<
        return;
    } 


    if ( menu.id === 3 ||
        (menu.nombre || '').toUpperCase().includes('VISITA') ){
        location.href = 'citas.php';          // <<<<<<
        return;
    }


    if ( menu.id === 6 ||
        (menu.nombre || '').toUpperCase().includes('GARANTIAS') ){
        location.href = 'garantias.php';          // <<<<<<
        return;
    }    

      if ( menu.id === 7 ||
        (menu.nombre || '').toUpperCase().includes('CALENDARIO') ){
        location.href = 'panel_calendario.php';          // <<<<<<
        return;
    }

      if ( menu.id === 8 ||
        (menu.nombre || '').toUpperCase().includes('NOTIFICACIONES') ){
        location.href = 'notificaciones.php';          // <<<<<<
        return;
    }  


      if ( menu.id === 9 ||
        (menu.nombre || '').toUpperCase().includes('PQR') ){
        location.href = 'pqr/pqr.php';          // <<<<<<
        return;
    }  


    if ( menu.id === 10 ||
        (menu.nombre || '').toUpperCase().includes('USER') ){
        location.href = 'users.php';          // <<<<<<
        return;
    }

    if ( menu.id === 11 ||
        (menu.nombre || '').toUpperCase().includes('MCM') ){
        location.href = '../api/mcm.php';          // <<<<<<
        return;
    }


    if ( menu.id === 12 ||
        (menu.nombre || '').toUpperCase().includes('VEGETAL') ){
        location.href = '../api/paletavegetal.php';          // <<<<<<
        return;
    }


    if ( menu.id === 13 ||
        (menu.nombre || '').toUpperCase().includes('NOTICIA') ){
        location.href = 'noticia.php';          // <<<<<<
        return;
    }

    // …aquí podrías despachar otros módulos por nombre o id
    alert('Abrir módulo '+menu.id);
  }

  /* ---------- Menú ---------- */
  checkGarantiasStatus().then(hasActiveGarantias => {
    const token = localStorage.getItem('cs_token');
    fetch(API_MENU, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }).then(r=>r.json()).then(({ok,menus})=>{
      const grid=document.getElementById('menuGrid');
      document.getElementById('menuSpinner').remove();
      if(!ok){grid.textContent='Error menú';return;}
      
      grid.innerHTML = ''; // Clear spinner

      // Filtramos el menú con id 15 (Ver más) para no mostrarlo en esta página
      const menusFiltrados = menus.filter(menu => menu.id !== 15);
      
      menusFiltrados.forEach(m=>{
        // Condición para ocultar el módulo a los residentes
        const isAcabadosModule = m.id === 1;
        const isResidente = u.rol_id === 2;
        if (isAcabadosModule && isResidente) {
            return; // No renderizar este módulo
        }

        const isGarantiasModule = (m.id === 6);
        const isCtgModule = (m.nombre || '').trim().toUpperCase() === 'CTG';
        const shouldDisableGarantias = (isGarantiasModule || isCtgModule) && !hasActiveGarantias && !u.is_responsable;

        const card = document.createElement('div');
        card.className='menu-card';

        if (shouldDisableGarantias) {
            card.classList.add('disabled-card');
            card.onclick = () => {
                if (isGarantiasModule) {
                    alert('Todas tus garantías han expirado.');
                } else if (isCtgModule) {
                    alert('El módulo CTG está desactivado porque su garantía ha expirado.');
                }
            };
        } else {
            card.onclick = () => openModule(m);
        }
        
        const iconElement = icon(m.url_icono);
        card.appendChild(iconElement);
        
        const title = document.createElement('h6');
        title.textContent = m.nombre;
        card.appendChild(title);
        
        if(m.descripcion) {
          const desc = document.createElement('p');
          desc.textContent = m.descripcion;
          card.appendChild(desc);
        }
        
        grid.appendChild(card);
      });
    });
  });

  async function checkGarantiasStatus() {
    const token = localStorage.getItem('cs_token');
    if (!token) return false; // No token, assume no active warranties for safety

    try {
        const response = await fetch('../api/garantias.php', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();

        if (!data.ok || !data.garantias || data.garantias.length === 0) {
            return false; // No data or no warranties, so no active warranties
        }

        const currentDate = new Date();
        currentDate.setHours(0, 0, 0, 0); // Normalize to start of day for comparison

        for (const garantia of data.garantias) {
            // Parse vigencia string "dd/mm/yyyy" to Date object
            const parts = garantia.vigencia.split('/');
            const vigenciaDate = new Date(parts[2], parts[1] - 1, parts[0]); // Year, Month (0-indexed), Day
            vigenciaDate.setHours(0, 0, 0, 0); // Normalize

            if (vigenciaDate >= currentDate) {
                return true; // Found at least one active warranty
            }
        }
        return false; // All warranties have expired
    } catch (error) {
        console.error('Error checking garantias status:', error);
        return false; // On error, assume no active warranties
    }
  }

  /* ---------- Logout ---------- */

  // Función para cerrar sesión (puedes llamarla desde un botón)
  async function logout() { 
    const token = localStorage.getItem('cs_token');
    if (!token) { 
      window.location.href = 'login_front.php';
      return;
    } 
    try { 
      const response = await fetch('../api/logout.php', {
        method: 'POST', 
        headers: { 
          'Content-Type': 'application/json'
        }, 
        body: JSON.stringify({ token: token }) 
      });
      const data = await response.json();
      if (data.ok) { 
        console.log(data.mensaje);
      } else { 
        console.error('Error al cerrar sesión:', data.mensaje);
      } 
    } catch (error) { 
      console.error('Error de conexión:', error);
    } finally {
      localStorage.removeItem('cs_token');
      localStorage.removeItem('cs_usuario');
      window.location.href = 'login_front.php';
    } 
  }

/* ---------- navegación ---------- */
  document.getElementById('btnBack').onclick  = () => location.href='menu_front.php';


  // Función para cerrar sesión (puedes llamarla desde un botón)
  /* ---------- Logout Button ---------- */
  document.getElementById('welcomeAvatar').addEventListener('click', function() {
      const logoutButton = document.getElementById('logoutButton');
      // Alternar la visibilidad del botón
      if (logoutButton.style.display === 'none') {
          logoutButton.style.display = 'block';
      } else {
          logoutButton.style.display = 'none';
      }
  });

  // Ocultar el botón si se hace clic fuera de él o del avatar
  document.addEventListener('click', function(event) {
      const avatarContainer = document.querySelector('.avatar-container');
      const logoutButton = document.getElementById('logoutButton');
      // Si el clic no fue dentro del contenedor del avatar (que incluye el avatar y el botón)
      if (!avatarContainer.contains(event.target) && logoutButton.style.display === 'block') {
          logoutButton.style.display = 'none';
      }
  });

  // Evento de clic para el botón de cerrar sesión
  document.getElementById('logoutButton').addEventListener('click', function() {
      logout(); // Llamar a la función de cerrar sesión existente
  });



</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>