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
<link href="assets/css/style_filtro.css" rel="stylesheet">
<link href="assets/css/chat.css" rel="stylesheet">

<?php $onesignal = require __DIR__ . '/../config/config_onesignal.php'; ?>
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
      await OneSignal.init({
          appId: "<?= htmlspecialchars($onesignal['ONESIGNAL_APP_ID'] ?? '') ?>",
          safari_web_id: "<?= htmlspecialchars($onesignal['ONESIGNAL_SAFARI_WEB_ID'] ?? '') ?>",
          notifyButton: {
              enable: false, // Deshabilitamos el botón de notificación por defecto
          },
          allowLocalhostAsSecureOrigin: true,
      });

      // Verificar el estado de suscripción después de un delay para asegurar que OneSignal esté completamente inicializado
      setTimeout(async () => {
          await checkOneSignalSubscription(OneSignal);
      }, 2000);

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
                  // Verificar si el usuario ya se desuscribió antes de enviar el Player ID
                  const isUnsubscribed = localStorage.getItem('onesignal_unsubscribed') === 'true';
                  const isDeclined = localStorage.getItem('onesignal_declined') === 'true';
                  
                  if (isUnsubscribed || isDeclined) {
                      console.log("Usuario desuscrito, no se enviará Player ID al servidor");
                      return;
                  }
                  
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
                          
                          // Si tenemos un Player ID, significa que el usuario está suscrito
                          // Verificar nuevamente el estado de suscripción
                          setTimeout(async () => {
                              await checkOneSignalSubscription(OneSignal);
                          }, 1000);
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

  // Función para verificar el estado de suscripción de OneSignal
  async function checkOneSignalSubscription(OneSignal) {
      try {
          // Verificar si el usuario ya se desuscribió activamente
          const isUnsubscribed = localStorage.getItem('onesignal_unsubscribed') === 'true';
          const isDeclined = localStorage.getItem('onesignal_declined') === 'true';
          
          // Si el usuario se desuscribió activamente, solo ocultar el icono pero permitir resuscripción
          if (isUnsubscribed) {
              console.log("Usuario se desuscribió activamente, ocultando icono pero permitiendo resuscripción");
              hideOneSignalBell();
              return;
          }
          
          // Si solo rechazó inicialmente, mostrar la ventana emergente
          if (isDeclined) {
              console.log("Usuario rechazó inicialmente, mostrando ventana emergente para resuscripción");
              showSubscriptionModal();
              return;
          }
          
          // Verificar si el usuario está suscrito usando la API correcta de v16
          let isSubscribed = false;
          
          try {
              // Método para v16 - verificar permisos de notificación
              if (OneSignal.getNotificationPermission) {
                  const permission = await OneSignal.getNotificationPermission();
                  isSubscribed = permission === 'granted';
                  console.log("Permisos de notificación:", permission);
              } else if (OneSignal.isPushNotificationsEnabled) {
                  isSubscribed = await OneSignal.isPushNotificationsEnabled();
                  console.log("Push notifications enabled:", isSubscribed);
              } else {
                  // Fallback: verificar si hay player ID usando la API correcta
                  try {
                      // En v16, usar OneSignal.User.PushSubscription.id
                      if (OneSignal.User && OneSignal.User.PushSubscription) {
                          const playerId = OneSignal.User.PushSubscription.id;
                          isSubscribed = !!playerId;
                          console.log("Player ID desde User.PushSubscription:", playerId);
                      } else {
                          // Intentar con la API de permisos nativa del navegador
                          const permission = await Notification.requestPermission();
                          isSubscribed = permission === 'granted';
                          console.log("Permisos nativos del navegador:", permission);
                      }
                  } catch (e) {
                      console.warn("Error al obtener Player ID, usando permisos nativos:", e);
                      const permission = await Notification.requestPermission();
                      isSubscribed = permission === 'granted';
                      console.log("Permisos nativos del navegador (fallback):", permission);
                  }
              }
          } catch (e) {
              console.warn("Error con método principal, usando fallback:", e);
              // Fallback: verificar permisos nativos del navegador
              try {
                  const permission = await Notification.requestPermission();
                  isSubscribed = permission === 'granted';
                  console.log("Permisos nativos del navegador (fallback final):", permission);
              } catch (e2) {
                  console.warn("Error con fallback final:", e2);
                  isSubscribed = false;
              }
          }
          
          console.log("Estado final de suscripción:", isSubscribed);
          
          if (isSubscribed) {
              console.log("Usuario ya está suscrito a OneSignal");
              // Ocultar completamente el icono de notificación de OneSignal
              hideOneSignalBell();
          } else {
              console.log("Usuario no está suscrito a OneSignal");
              // Mostrar la ventana emergente de suscripción
              showSubscriptionModal();
          }
      } catch (error) {
          console.error("Error al verificar suscripción de OneSignal:", error);
          // En caso de error, mostrar la ventana emergente
          showSubscriptionModal();
      }
  }

  // Función para ocultar SOLO el icono de notificación de OneSignal
  function hideOneSignalBell() {
      // Solo ocultar elementos específicos del icono de notificación de OneSignal
      const specificSelectors = [
          '.onesignal-bell-launcher',
          '.onesignal-bell-launcher-button',
          '.onesignal-bell-launcher-icon',
          '.onesignal-bell-launcher-container'
      ];
      
      specificSelectors.forEach(selector => {
          const elements = document.querySelectorAll(selector);
          elements.forEach(element => {
              // Solo ocultar si realmente es un elemento del icono de notificación
              if (element && element.classList.contains('onesignal-bell-launcher')) {
                  element.style.display = 'none';
                  element.style.visibility = 'hidden';
                  element.style.opacity = '0';
                  element.style.pointerEvents = 'none';
                  console.log("Elemento del icono de OneSignal ocultado:", element);
              }
          });
      });
      
      // Agregar clase CSS específica solo para el icono
      document.body.classList.add('onesignal-bell-hidden');
      
      // CSS inline más específico y seguro
      const style = document.createElement('style');
      style.id = 'onesignal-bell-hide-style';
      style.textContent = `
          .onesignal-bell-launcher,
          .onesignal-bell-launcher *,
          .onesignal-bell-launcher-button,
          .onesignal-bell-launcher-icon,
          .onesignal-bell-launcher-container {
              display: none !important;
              visibility: hidden !important;
              opacity: 0 !important;
              pointer-events: none !important;
          }
      `;
      
      // Evitar duplicar estilos
      const existingStyle = document.getElementById('onesignal-bell-hide-style');
      if (!existingStyle) {
          document.head.appendChild(style);
      }
      
      console.log("Icono de OneSignal ocultado de forma segura");
  }

  // Función para limpiar completamente el estado de OneSignal
  function clearOneSignalState() {
      // Limpiar localStorage
      localStorage.removeItem('onesignal_player_id');
      localStorage.setItem('onesignal_declined', 'true');
      localStorage.setItem('onesignal_unsubscribed', 'true');
      
      // Ocultar el icono
      hideOneSignalBell();
      
      console.log("Estado de OneSignal limpiado completamente");
  }

  // Función para mostrar la ventana emergente de suscripción
  function showSubscriptionModal() {
      const modal = document.getElementById('subscriptionModal');
      if (modal) {
          modal.classList.add('show');
      }
  }

  // Función para ocultar la ventana emergente
  function hideSubscriptionModal() {
      const modal = document.getElementById('subscriptionModal');
      if (modal) {
          modal.classList.remove('show');
      }
  }

    // Función para suscribir al usuario
  async function subscribeToNotifications() {
      try {
          // Ocultar la ventana emergente
          hideSubscriptionModal();
          
          // Limpiar el estado de desuscripción para permitir resuscripción
          localStorage.removeItem('onesignal_unsubscribed');
          localStorage.removeItem('onesignal_declined');
          
          // Suscribir al usuario usando la API correcta de v16
          try {
              if (OneSignal.showSlidedownPrompt) {
                  await OneSignal.showSlidedownPrompt();
              } else if (OneSignal.showNativePrompt) {
                  await OneSignal.showNativePrompt();
              } else {
                  // Fallback: solicitar permisos directamente
                  const permission = await Notification.requestPermission();
                  if (permission === 'granted') {
                      console.log("Permisos de notificación concedidos");
                  }
              }
          } catch (e) {
              console.warn("Error con prompt de OneSignal, usando fallback:", e);
              // Fallback: solicitar permisos directamente
              const permission = await Notification.requestPermission();
              if (permission === 'granted') {
                  console.log("Permisos de notificación concedidos");
              }
          }
          
          // Esperar un momento y verificar si se suscribió
          setTimeout(async () => {
              try {
                  let isSubscribed = false;
                  
                  if (OneSignal.getNotificationPermission) {
                      const permission = await OneSignal.getNotificationPermission();
                      isSubscribed = permission === 'granted';
                      console.log("Permisos después de suscribir:", permission);
                  } else if (OneSignal.isPushNotificationsEnabled) {
                      isSubscribed = await OneSignal.isPushNotificationsEnabled();
                      console.log("Push notifications enabled después de suscribir:", isSubscribed);
                  } else {
                      // Verificar con la API correcta de v16
                      if (OneSignal.User && OneSignal.User.PushSubscription) {
                          const playerId = OneSignal.User.PushSubscription.id;
                          isSubscribed = !!playerId;
                          console.log("Player ID después de suscribir:", playerId);
                      } else {
                          // Usar permisos nativos del navegador
                          const permission = await Notification.requestPermission();
                          isSubscribed = permission === 'granted';
                          console.log("Permisos nativos después de suscribir:", permission);
                      }
                  }
                  
                  if (isSubscribed) {
                      console.log("Usuario suscrito exitosamente");
                      hideOneSignalBell();
                  } else {
                      console.log("Usuario aún no está suscrito después del intento");
                  }
              } catch (e) {
                  console.warn("Error al verificar suscripción después de suscribir:", e);
              }
          }, 2000);
      } catch (error) {
          console.error("Error al suscribir:", error);
          alert("Error al suscribirse a las notificaciones. Por favor, inténtelo de nuevo.");
      }
  }

  // Función para rechazar la suscripción
  function declineNotifications() {
      hideSubscriptionModal();
      // Guardar en localStorage que el usuario rechazó
      localStorage.setItem('onesignal_declined', 'true');
      console.log("Usuario rechazó las notificaciones");
  }
</script>
<script>
  if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("/OneSignalSDKWorker.js")
      .then(reg => console.log("Service Worker registrado:", reg))
      .catch(err => console.error("Error al registrar el SW:", err));
  }
</script>
</head>

<body>
<!-- Header Section -->
<div class="header-section">
  <div class="welcome-card">
    <div class="welcome-info">
      <p class="welcome-name" id="welcomeName">Hola, Usuario</p>
      <p class="welcome-prop"><i>Elija su propiedad:</i></p>
    </div>
    <!-- Contenedor para el avatar y el botón de cerrar sesión -->
    <div class="avatar-container">
        <a href="/Front/notificaciones.php" class="notification-icon-link">
            <i class="bi bi-bell-fill"></i>
            <span class="notification-badge" id="notification-badge"></span>
        </a>
        <img src="" class="avatar" id="welcomeAvatar" alt="Avatar" title="Ver perfil" style="cursor: pointer;">
        <button id="logoutButton" class="logout-button" style="display: none;">Cerrar sesión</button>
    </div>
  </div>

  <!-- Property Tabs -->
  <div class="property-tabs" id="propertyTabs" style="display: none;">
    <!-- Property tabs will be dynamically added here -->
  </div>

  <!-- Filter Button for Responsables -->
  <div class="filter-section" id="filterSection" style="display: none;">
    <button class="filter-btn" id="filterBtn">
      <i class="bi bi-funnel"></i>
      Filtrar Propiedades
    </button>
    <button class="filter-btn" id="showAllBtn" style="display: none; background: #6b7280;">
      <i class="bi bi-list"></i>
      Ver Todas
    </button>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <!-- Comunicados -->
  <div id="newsRow">
    <div class="d-flex justify-content-center py-5" id="newsSpinner">
      <div class="spinner-border"></div>
    </div>
  </div>

  <!-- Construction Progress -->
  <div id="avanceCard" style="display:none;"></div>

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
      <div class="map-wrapper">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1037.9195830251224!2d-80.04656221303496!3d-2.192825715281358!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x902d77a10263cfcb%3A0xcee97561a1b5df50!2sUrbanizaci%C3%B3n%20CostaSol!5e0!3m2!1ses!2sec!4v1754579757268!5m2!1ses!2sec" 
          style="border:0;" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
      <p>Urbanización CostaSol, Km 9.5 Vía a la Costa, Guayaquil</p>
    </div>
  </div>
  <hr style="color:rgba(69, 67, 67, 0.33); margin-top: 25px; "></hr>

</div>

<!-- Ventana emergente de suscripción a notificaciones -->
<div id="subscriptionModal" class="subscription-modal">
  <div class="subscription-content">
    <h3 class="subscription-title">Estate al tanto de todas las novedades</h3>
    <div class="subscription-buttons">
      <button class="btn-decline" onclick="declineNotifications()">No, gracias</button>
      <button class="btn-subscribe" onclick="subscribeToNotifications()">Subscribirse</button>
    </div>
  </div>
</div>

<!-- Ventana emergente de filtro de propiedades -->
<div id="filterModal" class="filter-modal">
  <div class="filter-content">
    <div class="filter-header">
      <h3 class="filter-title">Filtrar Propiedades</h3>
      <button class="filter-close" id="filterCloseBtn">
        <i class="bi bi-x"></i>
      </button>
    </div>
    
    <div class="filter-body">
      <div class="filter-group">
        <label for="filterCliente">Cliente:</label>
        <div class="autocomplete-container">
          <input type="text" id="filterCliente" placeholder="Ej: Daniel, Ana" class="filter-input autocomplete-input">
          <div id="clienteSuggestions" class="autocomplete-suggestions"></div>
        </div>
      </div>
      
      <div class="filter-group">
        <label for="filterUbicacion">Ubicación:</label>
        <div class="autocomplete-container">
          <input type="text" id="filterUbicacion" placeholder="Ej: 7100, 7100-01" class="filter-input autocomplete-input">
          <div id="ubicacionSuggestions" class="autocomplete-suggestions"></div>
        </div>
      </div>
      
      <div class="filter-group" id="propiedadesClienteGroup" style="display: none;">
        <label for="propiedadesCliente">Propiedades del Cliente:</label>
        <select id="propiedadesCliente" class="filter-input">
          <option value="">Seleccionar propiedad...</option>
        </select>
      </div>
    </div>
    
    <div class="filter-footer">
      <button class="btn-filter-clear" id="clearFiltersBtn">
        <i class="bi bi-arrow-clockwise"></i>
        Limpiar
      </button>
      <button class="btn-filter-apply" id="applyFiltersBtn">
        <i class="bi bi-search"></i>
        Filtrar
      </button>
    </div>
  </div>
</div>

<?php 
$active_page = 'inicio';
include '../api/bottom_nav.php'; 
?>

<script>
  /* ---------- MARK NOTIFICATION AS READ ---------- */
  async function markNotificationAsRead(notificationId, notificationType = 'notificacion') {
    try {
      const token = localStorage.getItem('cs_token');
      if (!token) return false;

      const response = await fetch('../api/notificaciones_mark_read.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          type: notificationType,
          id: parseInt(notificationId)
        })
      });

      if (response.ok) {
        console.log(`Notificación ${notificationId} marcada como leída`);
        // Actualizar el badge de notificaciones
        fetchNotifications();
        return true;
      }
      return false;
    } catch (error) {
      console.error('Error marcando notificación como leída:', error);
      return false;
    }
  }

  /* ---------- NOTIFICATIONS ---------- */
  function fetchNotifications() {
    const token = localStorage.getItem('cs_token');
    if (!token) return; // No hacer nada si no hay token

    fetch('../api/notificaciones_count.php', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
      .then(r => {
        if (!r.ok) {
          throw new Error(`HTTP error! status: ${r.status}`);
        }
        return r.json();
      })
      .then(data => {
        if (data.status === 'success') {
          const badge = document.getElementById('notification-badge');
          if (data.count > 0) {
            badge.textContent = data.count > 9 ? '+9' : data.count;
            badge.style.display = 'block';
          } else {
            badge.style.display = 'none';
          }
        }
      })
      .catch(error => console.error('Error fetching notifications:', error));
  }

  /* ---------- USER ---------- */
  console.log('Valor en localStorage para cs_usuario:', localStorage.getItem('cs_usuario')); // Registro
  const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
  const token = localStorage.getItem('cs_token');
  // console.log('Usuario parseado:', u);
  // console.log('is_responsable:', u.is_responsable);
  
  if (!u.id) {
    location.href = 'login_front.php';
  } else {
    fetchNotifications();
  }

  const is_admin_responsable = (u.is_responsable && u.id === 3);
  const is_responsable = u.is_responsable;

  /* ---------- Endpoints ---------- */
  const API_NEWS  = '../api/noticias.php?limit=10&filter_by_user=1';
  const API_MENU  = is_admin_responsable ? '../api/menu.php' : '../api/menu.php?role_id=' + u.rol_id;
  const API_PROP  = u.is_responsable ? '../api/obtener_propiedades.php' : '../api/obtener_propiedades.php?id_usuario=' + u.id;
  const API_FASE  = '../api/propiedad_fase.php?id_propiedad=';

  /* ---------- Welcome ---------- */
  document.getElementById('welcomeName').textContent = `Hola, ${u.nombres || u.nombre} ${u.apellidos || ''}`;
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

  async function logModuleAccess(menuId, menuName, token) {
    try {
      await fetch('../api/log_module_access.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          menu_id: menuId,
          menu_name: menuName
        })
      });
    } catch (error) {
      console.error('Error al registrar acceso al módulo:', error);
    }
  }

  async function openModule(menu){
    // Log access before redirecting
    await logModuleAccess(menu.id, menu.nombre, token);

    // Si el nombre es CTG lanzamos ctg.php
    if ((menu.nombre || '').trim().toUpperCase() === 'CTG'){
        location.href = 'ctg/ctg.php';               // ctg.php leerá el usuario desde localStorage
        return;
    }

  

    if ( menu.id === 1 ||
        (menu.nombre || '').toUpperCase().includes('ACABADOS') ){
        if (!currentProp) {
            alert('Por favor, seleccione una propiedad primero.');
            return;
        }
        location.href = `seleccion_acabados.php?propiedad_id=${currentProp}`;
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

    // Centro de Mensajes para responsables
    if ( (menu.id + '') === 'chat_responsable' ||
        (menu.nombre || '').toUpperCase().includes('MENSAJE') ){
        location.href = 'chat_responsable.php';          // <<<<<<
        return;
    }

    if ( menu.id === 15 ||
        (menu.nombre || '').toUpperCase().includes('VER') ){
        location.href = 'menu2.php';          // <<<<<<
        return;
    }


    // …aquí podrías despachar otros módulos por nombre o id
    alert('Abrir módulo '+menu.id);
  }

  /* ---------- Noticias ---------- */
  fetch(API_NEWS, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  }).then(r=>r.json()).then(({ok,noticias})=>{
    if(!ok){document.getElementById('newsSpinner').textContent='Error noticias';return;}
    const row=document.getElementById('newsRow');
    row.innerHTML = `
    <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
      <div class="carousel-inner">
        ${noticias.map((n,i)=>`
          <div class="carousel-item${i?'':' active'}">
            <div class="news-content">
              <img src="${n.url_imagen}" class="news-thumb" alt="">
              <div class="news-text">
                <h5>${n.titulo}</h5>
                <p>${n.resumen}</p>
                <a href="${n.link_noticia}" target="_blank">
                  Saber más <i class="bi bi-arrow-right-short"></i>
                </a>
              </div>
            </div>
          </div>`).join('')}
      </div>
      <br>
      <div class="carousel-indicators">
        ${noticias.map((_,i)=>`<button type="button" data-bs-target="#newsCarousel" data-bs-slide-to="${i}"
                  ${i?'':'class="active"'}></button>`).join('')}
      </div>
    </div>`;
  });

  /* ---------- Propiedades ---------- */
  let currentProp=null;
  let currentManzana = '', currentSolar = '';
  let isResponsable = u.is_responsable;
  let allProperties = [];
  let filteredProperties = [];

  function pintarAvance(etapa, porcentaje){
    const card = document.getElementById('avanceCard');
    card.innerHTML = `
      <div class="construction-card">
        <div class="construction-content">
          ${svgCircle(porcentaje)}
          <div class="construction-info">
            <h4>Fase de Construcción</h4>
            <p class="construction-stage">${etapa}</p>
            <a href="fase_detalle.php?manzana=${encodeURIComponent(currentManzana)}&villa=${encodeURIComponent(currentSolar)}"
              class="construction-link">
              Ver detalles <i class="bi bi-chevron-down"></i>
            </a>
          </div>
        </div>
      </div>`;
    card.style.display = 'block';
  }

  function loadFase(id){
    const card=document.getElementById('avanceCard');
    card.style.display='none'; card.innerHTML='';
    fetch(API_FASE+id)
      .then(r=>r.json())
      .then(d=>{
        if(!d.ok){
          pintarAvance('Sin etapa asignada',0);
        }else{
          const f=d.fase;
          const etapaTexto = f.etapa_id ? `• ${f.etapa}` : 'Sin etapa asignada';
          pintarAvance(etapaTexto,f.porcentaje);
        }
      })
      .catch(()=>pintarAvance('Error al cargar',0));
  }

  /* ---------- Funciones del Filtro ---------- */
  function showFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.classList.add('show');
    modal.style.display = 'flex';
  }

  function hideFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.classList.remove('show');
    setTimeout(() => {
      modal.style.display = 'none';
    }, 300);
  }

  function clearFilters() {
    document.getElementById('filterCliente').value = '';
    document.getElementById('filterUbicacion').value = '';
    document.getElementById('propiedadesCliente').value = '';
    document.getElementById('propiedadesClienteGroup').style.display = 'none';
    
    // Limpiar sugerencias
    hideSuggestions('clienteSuggestions');
    hideSuggestions('ubicacionSuggestions');
  }

  async function applyFilters() {
    const cliente = document.getElementById('filterCliente').value.trim();
    const ubicacion = document.getElementById('filterUbicacion').value.trim();
    const propiedadCliente = document.getElementById('propiedadesCliente').value;

    const filtros = {};
    
    // Si hay una propiedad específica seleccionada, usar esa
    if (propiedadCliente) {
      const propiedad = JSON.parse(propiedadCliente);
      filtros.manzana = propiedad.manzana;
      filtros.villa = propiedad.villa;
    } else {
      // Si hay ubicación, parsearla
      if (ubicacion) {
        if (ubicacion.includes('-')) {
          const [manzana, villa] = ubicacion.split('-');
          filtros.manzana = manzana.trim();
          filtros.villa = villa.trim();
        } else {
          filtros.manzana = ubicacion;
        }
      }
    }
    
    if (cliente) filtros.nombre_cliente = cliente;

    try {
      const response = await fetch('../api/filtro_propiedad/filtrar_propiedades.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ filtros })
      });

      const data = await response.json();
      
      if (data.ok) {
        filteredProperties = data.propiedades;
        renderFilteredProperties();
        hideFilterModal();
        
        // Mostrar mensaje de resultados
        const total = data.total;
        const mensaje = total === 0 ? 'No se encontraron propiedades' : 
                       total === 1 ? 'Se encontró 1 propiedad' : 
                       `Se encontraron ${total} propiedades`;
        alert(mensaje);
      } else {
        alert('Error al filtrar propiedades: ' + data.mensaje);
      }
    } catch (error) {
      console.error('Error al aplicar filtros:', error);
      alert('Error al aplicar filtros');
    }
  }

  function renderFilteredProperties() {
    const tabsContainer = document.getElementById('propertyTabs');
    const filterSection = document.getElementById('filterSection');
    const showAllBtn = document.getElementById('showAllBtn');
    
    // Limpiar tabs existentes
    tabsContainer.innerHTML = '';
    
    if (filteredProperties.length === 0) {
      tabsContainer.style.display = 'none';
      showAllBtn.style.display = 'none';
      return;
    }
    
    tabsContainer.style.display = 'flex';
    showAllBtn.style.display = 'inline-flex';
    
    filteredProperties.forEach((p, i) => {
      const tab = document.createElement('button');
      tab.className = `property-tab${i === 0 ? ' active' : ''}`;
      tab.textContent = p.display_name;
      tab.title = `${p.cliente_completo} - MZ ${p.manzana} Villa ${p.villa}`;
      tab.onclick = () => {
        // Remove active class from all tabs
        document.querySelectorAll('.property-tab').forEach(t => t.classList.remove('active'));
        // Add active class to clicked tab
        tab.classList.add('active');
        
        // Update current property and load fase
        currentProp = p.id;
        localStorage.setItem('cs_propiedad_id', currentProp);
        currentManzana = p.manzana;
        currentSolar = p.villa;
        loadFase(p.id);
      };
      tabsContainer.appendChild(tab);
      
      if (i === 0) {
        currentProp = p.id;
        localStorage.setItem('cs_propiedad_id', currentProp);
        currentManzana = p.manzana;
        currentSolar = p.villa;
        loadFase(p.id);
      }
    });
  }

  function showAllProperties() {
    const tabsContainer = document.getElementById('propertyTabs');
    const filterSection = document.getElementById('filterSection');
    
    // Limpiar tabs existentes
    tabsContainer.innerHTML = '';
    
    if (allProperties.length === 0) return;
    
    tabsContainer.style.display = 'flex';
    
    allProperties.forEach((p, i) => {
      const tab = document.createElement('button');
      tab.className = `property-tab${i === 0 ? ' active' : ''}`;
      tab.textContent = p.display_name;
      tab.onclick = () => {
        // Remove active class from all tabs
        document.querySelectorAll('.property-tab').forEach(t => t.classList.remove('active'));
        // Add active class to clicked tab
        tab.classList.add('active');
        
        // Update current property and load fase
        currentProp = p.id;
        localStorage.setItem('cs_propiedad_id', currentProp);
        currentManzana = p.manzana;
        currentSolar = p.villa;
        loadFase(p.id);
      };
      tabsContainer.appendChild(tab);
      
      if (i === 0) {
        currentProp = p.id;
        localStorage.setItem('cs_propiedad_id', currentProp);
        currentManzana = p.manzana;
        currentSolar = p.villa;
        loadFase(p.id);
      }
    });
  }

  // Cargar propiedades según el tipo de usuario
  if (is_responsable) {
    // Para responsables, usar el nuevo API
    fetch('../api/filtro_propiedad/obtener_todas_propiedades.php', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }).then(r => r.json()).then(({ok, propiedades, mostrar_filtro, is_responsable: es_responsable}) => {
      if (!ok || !propiedades?.length) return;
      
      allProperties = propiedades;
      const filterSection = document.getElementById('filterSection');
      
      if (mostrar_filtro) {
        // Mostrar solo el botón de filtro
        filterSection.style.display = 'block';
        document.getElementById('propertyTabs').style.display = 'none';
        
        // Configurar eventos del filtro
        document.getElementById('filterBtn').onclick = showFilterModal;
        document.getElementById('showAllBtn').onclick = () => {
          filteredProperties = [];
          showAllProperties();
          document.getElementById('showAllBtn').style.display = 'none';
          clearFilters();
        };
        document.getElementById('filterCloseBtn').onclick = hideFilterModal;
        document.getElementById('clearFiltersBtn').onclick = clearFilters;
        document.getElementById('applyFiltersBtn').onclick = applyFilters;
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('filterModal').onclick = (e) => {
          if (e.target.id === 'filterModal') {
            hideFilterModal();
          }
        };
        
        // Configurar autocomplete para cliente
        setupClienteAutocomplete();
        
        // Configurar autocomplete para ubicación
        setupUbicacionAutocomplete();
        
        // Configurar eventos del select de propiedades
        document.getElementById('propiedadesCliente').addEventListener('change', handlePropiedadClienteChange);
      } else {
        // Si no necesita filtro, mostrar todas las propiedades
        showAllProperties();
      }
    }).catch(error => {
      console.error('Error al cargar propiedades para responsable:', error);
    });
  } else {
    // Para usuarios normales, usar el API original
    fetch(API_PROP, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }).then(r=>r.json()).then(({ok,propiedades})=>{
      if(!ok||!propiedades.length) return;
      
      const tabsContainer = document.getElementById('propertyTabs');
      tabsContainer.style.display = 'flex';
      
      propiedades.forEach((p,i)=>{
        const tab = document.createElement('button');
        tab.className = `property-tab${i?'':' active'}`;
        tab.textContent = `${p.tipo} ${p.urbanizacion}`;
        tab.onclick = () => {
          // Remove active class from all tabs
          document.querySelectorAll('.property-tab').forEach(t => t.classList.remove('active'));
          // Add active class to clicked tab
          tab.classList.add('active');
          
          // Update current property and load fase
          currentProp = p.id;
          localStorage.setItem('cs_propiedad_id', currentProp); // Save to localStorage
          currentManzana = p.manzana;
          currentSolar = p.solar;
          loadFase(p.id);
        };
        tabsContainer.appendChild(tab);
        
        if(i===0){
          currentProp=p.id;  
          localStorage.setItem('cs_propiedad_id', currentProp); // Save initial property to localStorage
          currentManzana=p.manzana;
          currentSolar=p.solar; 
          loadFase(p.id);
        }
      });
    });
  }

  /* ---------- Menú ---------- */
  // Call checkGarantiasStatus before fetching API_MENU
  checkGarantiasStatus().then(hasActiveGarantias => {
    fetch(API_MENU, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }).then(r=>r.json()).then(({ok,menus})=>{
      const grid=document.getElementById('menuGrid');
      document.getElementById('menuSpinner').remove();
      if(!ok){grid.textContent='Error menú';return;}
      
      const renderMenu = (menuItems) => {
          grid.innerHTML = ''; // Clear grid before rendering
          menuItems.forEach(m=> {
              // Placeholder invisible to preserve layout position
              if (m && m.is_placeholder) {
                  const placeholder = document.createElement('div');
                  placeholder.className = 'menu-card';
                  placeholder.style.visibility = 'hidden';
                  placeholder.style.pointerEvents = 'none';
                  grid.appendChild(placeholder);
                  return;
              }
              // Check if this is the Garantias module (menu.id === 6)
              // and if there are no active warranties
              const isGarantiasModule = (Number(m.id) === 6);
              const isCtgModule = (m.nombre || '').trim().toUpperCase() === 'CTG';
              const shouldDisable = (isGarantiasModule || isCtgModule) && !hasActiveGarantias && !u.is_responsable;

              const card = document.createElement('div');
              card.className='menu-card';
              if (shouldDisable) {
                  card.classList.add('disabled-card'); // Add a class for styling
                  card.onclick = () => {
                      if (isGarantiasModule) {
                        alert('Todas tus garantías han expirado.');
                      } else if (isCtgModule) {
                        alert('El módulo CTG está desactivado porque su garantía ha expirado.');
                      }
                  }; // Override click to show alert
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
      };

      // Filter menus for Residente role before slicing
      const isResidente = u.rol_id === 2;
      const availableMenusBase = isResidente 
        ? menus.filter(m => m.id !== 1) 
        : menus;
      const availableMenus = u.is_responsable
        ? [{ id: 'chat_responsable', nombre: 'Centro de Mensajes', descripcion: 'Atiende conversaciones', url_icono: 'bi-chat-dots' }, ...availableMenusBase]
        : availableMenusBase;

      // Fijar el módulo id 15 ("Ver más") en la posición 4 siempre
      const verMas = availableMenus.find(m => Number(m.id) === 15);
      const others = availableMenus.filter(m => Number(m.id) !== 15);

      // Mantener el orden provisto por el API; tomar los tres primeros distintos de 15
      const firstThree = others.slice(0, 3);
      const itemsForFirstRow = [...firstThree];

      // Si faltan módulos antes del 4, agregar placeholders invisibles
      if (verMas) {
        while (itemsForFirstRow.length < 3) {
          itemsForFirstRow.push({ is_placeholder: true });
        }
        // Ubicar "Ver más" como la cuarta tarjeta
        itemsForFirstRow.push(verMas);
      } else {
        // Si no existe el módulo 15, usar los primeros cuatro disponibles
        itemsForFirstRow.push(...others.slice(itemsForFirstRow.length, 4 - itemsForFirstRow.length));
      }

      renderMenu(itemsForFirstRow);

    });
  });

  // ... rest of the code ...

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


  // Función para cerrar sesión (puedes llamarla desde un botón)
  /* ---------- Avatar Click - Redirigir a Perfil ---------- */
  document.getElementById('welcomeAvatar').addEventListener('click', function() {
      // Redirigir a la página de perfil
      window.location.href = 'perfil.php';
  });

  // Evento de clic para el botón de cerrar sesión (mantener por si se necesita en el futuro)
  document.getElementById('logoutButton').addEventListener('click', function() {
      logout(); // Llamar a la función de cerrar sesión existente
  });

  // Verificar si el usuario ya rechazó las notificaciones al cargar la página
  document.addEventListener('DOMContentLoaded', function() {
      // Verificar si el usuario se desuscribió activamente
      const isUnsubscribed = localStorage.getItem('onesignal_unsubscribed') === 'true';
      const declined = localStorage.getItem('onesignal_declined');
      
      if (isUnsubscribed || declined === 'true') {
          // Si el usuario ya se desuscribió o rechazó, ocultar la ventana emergente
          const modal = document.getElementById('subscriptionModal');
          if (modal) {
              modal.style.display = 'none';
          }
          
          // Limpiar completamente el estado de OneSignal
          if (isUnsubscribed) {
              clearOneSignalState();
          }
      }
      
      // Verificar periódicamente si aparece el icono de OneSignal y ocultarlo
      setInterval(() => {
          // Solo ocultar si el usuario está desuscrito activamente
          const isUnsubscribed = localStorage.getItem('onesignal_unsubscribed') === 'true';
          
          if (isUnsubscribed) {
              const oneSignalBellElements = document.querySelectorAll('.onesignal-bell-launcher, .onesignal-bell-launcher-button, .onesignal-bell-launcher-icon, .onesignal-bell-launcher-container');
              if (oneSignalBellElements.length > 0) {
                  oneSignalBellElements.forEach(element => {
                      // Solo ocultar elementos específicos del icono de notificación
                      if (element && (element.classList.contains('onesignal-bell-launcher') || 
                          element.classList.contains('onesignal-bell-launcher-button') ||
                          element.classList.contains('onesignal-bell-launcher-icon') ||
                          element.classList.contains('onesignal-bell-launcher-container'))) {
                          element.style.display = 'none';
                          element.style.visibility = 'hidden';
                          element.style.opacity = '0';
                          element.style.pointerEvents = 'none';
                      }
                  });
              }
          }
      }, 1000); // Verificar cada segundo
      
      // Observer para detectar cuando se agregan elementos del icono de OneSignal al DOM
      const observer = new MutationObserver((mutations) => {
          // Solo ocultar si el usuario está desuscrito activamente
          const isUnsubscribed = localStorage.getItem('onesignal_unsubscribed') === 'true';
          
          if (isUnsubscribed) {
              mutations.forEach((mutation) => {
                  mutation.addedNodes.forEach((node) => {
                      if (node.nodeType === Node.ELEMENT_NODE) {
                          // Verificar si el nodo agregado es específicamente del icono de notificación de OneSignal
                          if (node.classList && (
                              node.classList.contains('onesignal-bell-launcher') ||
                              node.classList.contains('onesignal-bell-launcher-button') ||
                              node.classList.contains('onesignal-bell-launcher-icon') ||
                              node.classList.contains('onesignal-bell-launcher-container')
                          )) {
                              // Ocultar inmediatamente solo si es el icono de notificación
                              node.style.display = 'none';
                              node.style.visibility = 'hidden';
                              node.style.opacity = '0';
                              node.style.pointerEvents = 'none';
                              console.log("Elemento del icono de OneSignal detectado y ocultado:", node);
                          }
                          
                          // Verificar también en los hijos del nodo, pero solo elementos específicos del icono
                          const oneSignalBellChildren = node.querySelectorAll('.onesignal-bell-launcher, .onesignal-bell-launcher-button, .onesignal-bell-launcher-icon, .onesignal-bell-launcher-container');
                          oneSignalBellChildren.forEach(element => {
                              // Solo ocultar si realmente es un elemento del icono de notificación
                              if (element && (element.classList.contains('onesignal-bell-launcher') || 
                                  element.classList.contains('onesignal-bell-launcher-button') ||
                                  element.classList.contains('onesignal-bell-launcher-icon') ||
                                  element.classList.contains('onesignal-bell-launcher-container'))) {
                                  element.style.display = 'none';
                                  element.style.visibility = 'hidden';
                                  element.style.opacity = '0';
                                  element.style.pointerEvents = 'none';
                              }
                          });
                      }
                  });
              });
          }
      });
      
      // Iniciar la observación
      observer.observe(document.body, {
          childList: true,
          subtree: true
      });
      
      // Verificación adicional después de que la página esté completamente cargada
      setTimeout(() => {
          // Si OneSignal ya está disponible, verificar el estado
          if (window.OneSignal) {
              checkOneSignalSubscription(window.OneSignal);
          }
      }, 5000); // Esperar 5 segundos para asegurar que OneSignal esté completamente inicializado
  });

  // Variables globales para autocomplete
  let clienteTimeout = null;
  let ubicacionTimeout = null;
  let selectedClienteId = null;

  // Función para configurar autocomplete de clientes
  function setupClienteAutocomplete() {
    const input = document.getElementById('filterCliente');
    const suggestions = document.getElementById('clienteSuggestions');
    
    input.addEventListener('input', (e) => {
      const query = e.target.value.trim();
      
      clearTimeout(clienteTimeout);
      
      if (query.length < 1) {
        hideSuggestions('clienteSuggestions');
        hidePropiedadesCliente();
        return;
      }
      
      clienteTimeout = setTimeout(() => {
        searchClientes(query);
      }, 300);
    });
    
    // Ocultar sugerencias al hacer clic fuera
    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !suggestions.contains(e.target)) {
        hideSuggestions('clienteSuggestions');
      }
    });
  }

  // Función para configurar autocomplete de ubicaciones
  function setupUbicacionAutocomplete() {
    const input = document.getElementById('filterUbicacion');
    const suggestions = document.getElementById('ubicacionSuggestions');
    
    input.addEventListener('input', (e) => {
      const query = e.target.value.trim();
      
      clearTimeout(ubicacionTimeout);
      
      if (query.length < 1) {
        hideSuggestions('ubicacionSuggestions');
        return;
      }
      
      ubicacionTimeout = setTimeout(() => {
        searchUbicaciones(query);
      }, 300);
    });
    
    // Ocultar sugerencias al hacer clic fuera
    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !suggestions.contains(e.target)) {
        hideSuggestions('ubicacionSuggestions');
      }
    });
  }

  // Función para buscar clientes
  async function searchClientes(query) {
    try {
      const response = await fetch(`../api/filtro_propiedad/buscar_clientes.php?q=${encodeURIComponent(query)}&limit=10`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      
      const data = await response.json();
      
      if (data.ok) {
        showClienteSuggestions(data.clientes);
      }
    } catch (error) {
      console.error('Error buscando clientes:', error);
    }
  }

  // Función para buscar ubicaciones
  async function searchUbicaciones(query) {
    try {
      const response = await fetch(`../api/filtro_propiedad/buscar_ubicaciones.php?q=${encodeURIComponent(query)}&limit=15`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      
      const data = await response.json();
      
      if (data.ok) {
        showUbicacionSuggestions(data.ubicaciones);
      }
    } catch (error) {
      console.error('Error buscando ubicaciones:', error);
    }
  }

  // Función para mostrar sugerencias de clientes
  function showClienteSuggestions(clientes) {
    const suggestions = document.getElementById('clienteSuggestions');
    
    if (clientes.length === 0) {
      hideSuggestions('clienteSuggestions');
      return;
    }
    
    suggestions.innerHTML = clientes.map(cliente => `
      <div class="autocomplete-suggestion" data-cliente-id="${cliente.id}" data-cliente-nombre="${cliente.nombre_completo}">
        <div class="suggestion-title">${cliente.nombre_completo}</div>
        <div class="suggestion-subtitle">${cliente.total_propiedades} propiedad${cliente.total_propiedades !== 1 ? 'es' : ''}</div>
      </div>
    `).join('');
    
    // Agregar eventos de clic
    suggestions.querySelectorAll('.autocomplete-suggestion').forEach(suggestion => {
      suggestion.addEventListener('click', () => {
        const clienteId = suggestion.dataset.clienteId;
        const clienteNombre = suggestion.dataset.clienteNombre;
        
        document.getElementById('filterCliente').value = clienteNombre;
        selectedClienteId = clienteId;
        
        hideSuggestions('clienteSuggestions');
        
        // Si el cliente tiene múltiples propiedades, mostrar el select
        if (clientes.find(c => c.id == clienteId).total_propiedades > 1) {
          loadPropiedadesCliente(clienteId);
        } else {
          hidePropiedadesCliente();
        }
      });
    });
    
    suggestions.classList.add('show');
  }

  // Función para mostrar sugerencias de ubicaciones
  function showUbicacionSuggestions(ubicaciones) {
    const suggestions = document.getElementById('ubicacionSuggestions');
    
    if (ubicaciones.length === 0) {
      hideSuggestions('ubicacionSuggestions');
      return;
    }
    
    suggestions.innerHTML = ubicaciones.map(ubicacion => `
      <div class="autocomplete-suggestion" data-ubicacion="${ubicacion.ubicacion_completa}" data-cliente-id="${ubicacion.cliente_id || ''}" data-cliente-nombre="${ubicacion.cliente_completo || ''}">
        <div class="suggestion-title">${ubicacion.ubicacion_completa}</div>
        <div class="suggestion-subtitle">${ubicacion.urbanizacion}${ubicacion.cliente_completo ? ' - ' + ubicacion.cliente_completo : ''}</div>
      </div>
    `).join('');
    
    // Agregar eventos de clic
    suggestions.querySelectorAll('.autocomplete-suggestion').forEach(suggestion => {
      suggestion.addEventListener('click', () => {
        const ubicacion = suggestion.dataset.ubicacion;
        const clienteId = suggestion.dataset.clienteId;
        const clienteNombre = suggestion.dataset.clienteNombre;
        
        document.getElementById('filterUbicacion').value = ubicacion;
        
        // Si hay cliente asociado, completar el campo cliente
        if (clienteNombre) {
          document.getElementById('filterCliente').value = clienteNombre;
          selectedClienteId = clienteId;
        }
        
        hideSuggestions('ubicacionSuggestions');
      });
    });
    
    suggestions.classList.add('show');
  }

  // Función para ocultar sugerencias
  function hideSuggestions(suggestionId) {
    const suggestions = document.getElementById(suggestionId);
    suggestions.classList.remove('show');
    suggestions.innerHTML = '';
  }

  // Función para cargar propiedades de un cliente
  async function loadPropiedadesCliente(clienteId) {
    try {
      const response = await fetch(`../api/filtro_propiedad/obtener_propiedades_cliente.php?cliente_id=${clienteId}`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      
      const data = await response.json();
      
      if (data.ok) {
        const select = document.getElementById('propiedadesCliente');
        select.innerHTML = '<option value="">Seleccionar propiedad...</option>';
        
        data.propiedades.forEach(propiedad => {
          const option = document.createElement('option');
          option.value = JSON.stringify(propiedad);
          option.textContent = propiedad.display_name;
          select.appendChild(option);
        });
        
        document.getElementById('propiedadesClienteGroup').style.display = 'block';
      }
    } catch (error) {
      console.error('Error cargando propiedades del cliente:', error);
    }
  }

  // Función para ocultar el grupo de propiedades del cliente
  function hidePropiedadesCliente() {
    document.getElementById('propiedadesClienteGroup').style.display = 'none';
    document.getElementById('propiedadesCliente').value = '';
  }

  // Función para manejar el cambio en el select de propiedades
  function handlePropiedadClienteChange() {
    const select = document.getElementById('propiedadesCliente');
    if (select.value) {
      const propiedad = JSON.parse(select.value);
      document.getElementById('filterUbicacion').value = propiedad.ubicacion_completa;
    }
  }

  /* ---------- MARK NOTIFICATION FROM URL PARAM ---------- */
  // Si se abre menu_front.php con parámetro notif_id, marcar esa notificación como leída
  const urlParams = new URLSearchParams(window.location.search);
  const notifId = urlParams.get('notif_id');
  const notifType = urlParams.get('notif_type') || 'notificacion';
  
  if (notifId) {
    // Esperar un poco para asegurar que la página esté lista
    setTimeout(() => {
      markNotificationAsRead(notifId, notifType);
    }, 500);
  }

  /* ---------- Chat Widget ---------- */
  (function(){
    const token = localStorage.getItem('cs_token');
    const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
    if (!token || !u.id || u.is_responsable) { return; }

    const API_THREAD = '../api/chat/thread.php';
    const API_MSG    = '../api/chat/messages.php';
    const API_TRANS  = '../api/chat/transcript.php';

    // Botón flotante
    const launcher = document.createElement('button');
    launcher.className = 'chat-launcher';
    launcher.innerHTML = '<i class="bi bi-chat-dots"></i>';
    document.body.appendChild(launcher);

    // Modal
    const modal = document.createElement('div');
    modal.className = 'chat-modal';
    modal.innerHTML = `
      <div class="chat-header">
        <div class="topbar">
          <div>
            <div class="title">Hola ${[u.nombres, u.apellidos].filter(Boolean).join(' ') || u.nombre || 'Usuario'} 👋</div>
            <div class="subtitle">¿Cómo podemos ayudarte?</div>
          </div>
          <div class="chat-options">
            <button id="chatMenuBtn" title="Opciones"><i class="bi bi-three-dots"></i></button>
            <div id="chatMenu" class="chat-options-menu">
              <div class="item" id="optExpand">Ampliar ventana</div>
              <div class="item" id="optTranscript">Descargar transcripción</div>
              <div class="item" id="optClose">Cerrar</div>
            </div>
          </div>
        </div>
      </div>
      <div class="chat-body" id="chatBody"></div>
      <div class="chat-footer">
        <div id="replyContainer" class="reply-container">
          <div class="replying-to">
            <div class="reply-info">
              <div class="reply-author"></div>
              <div class="reply-text"></div>
            </div>
            <button class="cancel-reply" onclick="cancelReply()">×</button>
          </div>
        </div>
        <div class="chat-input-row">
          <button id="btnEmoji" class="btn btn-link" title="Emoji"><i class="bi bi-emoji-smile"></i></button>
          <input id="chatText" class="chat-input" type="text" placeholder="Escribe un mensaje..." />
          <button id="btnAttach" class="btn btn-link" title="Adjuntar archivo"><i class="bi bi-paperclip"></i></button>
          <button id="btnCamera" class="btn btn-link" title="Cámara"><i class="bi bi-camera"></i></button>
          <button id="btnAudio" class="btn btn-link" title="Audio" style="display: block;"><i class="bi bi-mic"></i></button>
          <button id="chatSend" class="chat-send" title="Enviar" style="display: none;"><i class="bi bi-send-fill"></i></button>
          <input id="fileInput" type="file" style="display:none" />
        </div>
      </div>
      <div id="emojiPanel" class="emoji-panel" style="display:none">
        <span class="emoji">😀</span>
        <span class="emoji">😁</span>
        <span class="emoji">😂</span>
        <span class="emoji">😊</span>
        <span class="emoji">😍</span>
        <span class="emoji">👍</span>
        <span class="emoji">🙏</span>
        <span class="emoji">🎉</span>
      </div>
    `;
    document.body.appendChild(modal);

    // Overlay para cerrar al tocar fuera
    const overlay = document.createElement('div');
    overlay.className = 'chat-overlay';
    document.body.appendChild(overlay);

    const chatBody = modal.querySelector('#chatBody');
    const chatText = modal.querySelector('#chatText');
    const chatSend = modal.querySelector('#chatSend');
    const btnAudio = modal.querySelector('#btnAudio');
    const menuBtn  = modal.querySelector('#chatMenuBtn');
    const menu     = modal.querySelector('#chatMenu');
    const optExpand= modal.querySelector('#optExpand');
    const optTrans = modal.querySelector('#optTranscript');
    const optClose = modal.querySelector('#optClose');
    const btnAttach = modal.querySelector('#btnAttach');
    const fileInput = modal.querySelector('#fileInput');
    const btnEmoji  = modal.querySelector('#btnEmoji');
    const emojiPanel= modal.querySelector('#emojiPanel');
    const replyContainer = modal.querySelector('#replyContainer');

    let threadId = 0;
    let lastId = 0;
    let pollTimer = null;

    // Deduplicación y claves de mensajes
    const renderedIds = new Set();
    const pendingKeys = new Set();
    const makeKey = (m)=> (m.sender_type||'-') + '|' + String(m.content||'').trim();

    const isUploadUrl = (text)=> /^https?:\/\//.test(text) || /^\/?uploads\//i.test(text);
    function renderAttachment(bubble, text){
      const lower = String(text).toLowerCase();
      const isImg   = /(\.png|\.jpg|\.jpeg|\.gif|\.webp|\.bmp|\.svg)(\?.*)?$/i.test(lower);
      const isAudio = /(\.mp3|\.wav|\.ogg|\.webm|\.m4a)(\?.*)?$/i.test(lower) || text.startsWith('blob:') || /^data:audio\//i.test(text);
      const isVideo = /(\.mp4|\.webm|\.mov)(\?.*)?$/i.test(lower);
      if (isImg){
        const img = document.createElement('img');
        img.src = text; img.alt = 'imagen';
        img.style.maxWidth = '100%'; img.style.borderRadius = '12px';
        bubble.appendChild(img);
        return;
      }
      if (isAudio){
        const audio = document.createElement('audio');
        audio.controls = true;
        audio.src = text;
        audio.style.width = '100%';
        audio.style.height = '40px';
        audio.style.display = 'block';
        audio.setAttribute('preload','metadata');
        bubble.appendChild(audio);
        return;
      }
      if (isVideo){
        const video = document.createElement('video');
        video.controls = true; video.src = text; video.style.maxWidth='100%'; video.style.borderRadius='12px';
        bubble.appendChild(video);
        return;
      }
      const a = document.createElement('a'); a.href = text; a.target = '_blank'; a.textContent = 'Abrir archivo';
      bubble.appendChild(a);
    }

    let replyingTo = null;
    
    function appendMessage(m, opts={}){
      if (m.id && renderedIds.has(Number(m.id))) return; // evitar duplicados por id
      const row = document.createElement('div');
      row.className = 'message-row';
      const bubble = document.createElement('div');
      bubble.className = 'message ' + (m.sender_type === 'user' ? 'user' : 'responsable');
      if (opts.pending) bubble.classList.add('pending');
      
      // Add reply preview if this message is a reply
      if (m.reply_to) {
        const replyPreview = document.createElement('div');
        replyPreview.className = 'reply-preview';
        replyPreview.innerHTML = `
          <div class="reply-line"></div>
          <div class="reply-content">
            <div class="reply-author">${m.reply_to.sender_type === 'user' ? 'Tú' : 'Responsable'}</div>
            <div class="reply-text">${m.reply_to.content}</div>
          </div>
        `;
        bubble.appendChild(replyPreview);
      }
      
      const messageContent = document.createElement('div');
      messageContent.className = 'message-content';
      const text = String(m.content||'');
      if (isUploadUrl(text)) {
        renderAttachment(messageContent, text);
      } else {
        messageContent.textContent = text;
      }
      bubble.appendChild(messageContent);
      
      // Add reply button (only for non-pending messages)
      if (!opts.pending) {
        const replyBtn = document.createElement('button');
        replyBtn.className = 'reply-btn';
        replyBtn.innerHTML = '<i class="bi bi-reply"></i>';
        replyBtn.title = 'Responder';
        replyBtn.onclick = () => setReplyTo(m);
        bubble.appendChild(replyBtn);
      }
      
      row.appendChild(bubble);
      chatBody.appendChild(row);
      scrollToBottom();
      if (m.id) renderedIds.add(Number(m.id));
      return { row, bubble };
    }
    
    function setReplyTo(message) {
      replyingTo = message;
      showReplyPreview();
      chatText.focus();
    }
    
    function showReplyPreview() {
      const replyAuthor = replyContainer.querySelector('.reply-author');
      const replyText = replyContainer.querySelector('.reply-text');
      
      replyAuthor.textContent = `Respondiendo a ${replyingTo.sender_type === 'user' ? 'ti mismo' : 'Responsable'}`;
      replyText.textContent = replyingTo.content;
      replyContainer.style.display = 'block';
    }
    
    function cancelReply() {
      replyingTo = null;
      if (replyContainer) {
        replyContainer.style.display = 'none';
      }
    }
    
    // Make cancelReply globally accessible
    window.cancelReply = cancelReply;

    function scrollToBottom(){
      setTimeout(() => {
        chatBody.scrollTop = chatBody.scrollHeight;
      }, 100);
    }

    async function ensureThread(){
      const r = await fetch(API_THREAD, { headers: { 'Authorization': `Bearer ${token}` } });
      const data = await r.json();
      if (!data.ok) throw new Error(data.error||'No se pudo iniciar chat');
      threadId = data.thread.id || data.thread_id || data.id;
    }

    async function loadMessages(){
      if (!threadId) return;
      const url = `${API_MSG}?thread_id=${threadId}&limit=100&since_id=${lastId}`;
      const r = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
      const data = await r.json();
      if (data.ok && Array.isArray(data.messages)){
        let hasNewMessages = false;
        data.messages.forEach(m=>{
          const key = makeKey(m);
          if (m.sender_type==='user' && pendingKeys.has(key)){
            pendingKeys.delete(key);
            lastId = Math.max(lastId, Number(m.id||0));
            renderedIds.add(Number(m.id||0));
            return; // saltar eco
          }
          appendMessage(m);
          lastId = Math.max(lastId, Number(m.id||0));
          hasNewMessages = true;
        });
        if (hasNewMessages) {
          scrollToBottom();
        }
      }
    }

    async function sendMessage(){
      const txt = chatText.value.trim();
      if (!txt || !threadId) return;
      chatText.value='';
      
      // Reset to audio button after sending
      btnAudio.style.display = 'block';
      chatSend.style.display = 'none';
      
      // Prepare message object
      const messageObj = { sender_type:'user', content: txt };
      if (replyingTo) {
        messageObj.reply_to = replyingTo;
      }
      
      const key = makeKey(messageObj);
      pendingKeys.add(key);
      appendMessage(messageObj, { pending: true });
      
      // Clear reply state
      if (replyingTo) {
        cancelReply();
      }
      
      try{
        const payload = { thread_id: threadId, content: txt };
        if (replyingTo) {
          payload.reply_to_id = replyingTo.id;
        }
        
        await fetch(API_MSG, {
          method:'POST',
          headers:{ 'Content-Type':'application/json', 'Authorization': `Bearer ${token}` },
          body: JSON.stringify(payload)
        });
      }catch(e){ console.error('Error enviando mensaje', e); }
    }

    function startPolling(){ stopPolling(); pollTimer = setInterval(loadMessages, 3000); }
    function stopPolling(){ if (pollTimer) { clearInterval(pollTimer); pollTimer=null; } }

    // Adjuntar archivo con preview y estado
    btnAttach.addEventListener('click', ()=> fileInput.click());
    fileInput.addEventListener('change', async ()=>{
      if (!fileInput.files || !fileInput.files[0] || !threadId) return;
      const f = fileInput.files[0];
      const objectURL = URL.createObjectURL(f);
      const ext = (f.name||'').toLowerCase();
      const isImg   = /(\.png|\.jpg|\.jpeg|\.gif|\.webp|\.bmp|\.svg)$/i.test(ext);
      const isAudio = /(\.mp3|\.wav|\.ogg|\.webm|\.m4a)$/i.test(ext);
      const isVideo = /(\.mp4|\.webm|\.mov)$/i.test(ext);

      const row = document.createElement('div'); row.className='message-row';
      const bubble = document.createElement('div'); bubble.className='message user pending';
      if (isImg){ const img=document.createElement('img'); img.src=objectURL; img.style.maxWidth='100%'; img.style.borderRadius='12px'; bubble.appendChild(img); }
      else if (isAudio){ const audio=document.createElement('audio'); audio.controls=true; audio.src=objectURL; audio.style.width='100%'; bubble.appendChild(audio); }
      else if (isVideo){ const video=document.createElement('video'); video.controls=true; video.src=objectURL; video.style.maxWidth='100%'; video.style.borderRadius='12px'; bubble.appendChild(video); }
      else { const icon=document.createElement('span'); icon.textContent='📎 '; bubble.appendChild(icon); const name=document.createElement('span'); name.textContent=f.name||'archivo'; bubble.appendChild(name); }
      const status=document.createElement('div'); status.className='message-meta'; status.textContent='Subiendo...'; bubble.appendChild(status);
      row.appendChild(bubble); chatBody.appendChild(row); chatBody.scrollTop = chatBody.scrollHeight;

      const form = new FormData(); form.append('file', f);
      try{
        const r = await fetch('../api/chat/upload.php', { method:'POST', headers:{ 'Authorization': `Bearer ${token}` }, body: form });
        const data = await r.json();
        if (data.ok && data.url){
          const url = data.url;
          if (isImg){ bubble.querySelector('img').src = url; }
          else if (isAudio){ bubble.querySelector('audio').src = url; }
          else if (isVideo){ bubble.querySelector('video').src = url; }
          else { bubble.innerHTML=''; renderAttachment(bubble, url); }
          bubble.classList.remove('pending');
          status.textContent = 'Enviado';
          const key = makeKey({ sender_type:'user', content: url });
          pendingKeys.add(key);
          await fetch(API_MSG, { method:'POST', headers:{ 'Content-Type':'application/json', 'Authorization': `Bearer ${token}` }, body: JSON.stringify({ thread_id: threadId, content: url }) });
        } else {
          status.textContent = 'Error al subir';
        }
      }catch(e){ console.error('upload error', e); status.textContent='Error al subir'; }
      fileInput.value='';
    });

    // Selector de emojis mejorado
    const emojiList = '😀 😁 😂 🤣 😊 😇 🙂 🙃 😉 😍 😘 😗 😙 😚 🥰 😋 😛 😜 🤪 😝 🤗 🤭 🤫 🤔 🤐 🤨 😐 😑 😶 🙄 😏 😣 😥 😮 😯 😪 😫 🥱 😴 😌 🥳 🤓 😎 🤩 🥺 😤 😢 😭 😱 😳 🤯 😬 😰 🤥 🤧 🤒 🤕 🤑 🤠 🤡 👋 👍 👎 🙏 💪 ✌️ 👀 ❤️ 🧡 💛 💚 💙 💜 🤎 🖤 🤍 💔 ⭐ 🌟 🔥 🎉 🎂 🎁 🍻 ☕ 🍔 🍕 🍟 🍣 🍓 🍎 🥑 ⚽ 🏀 🎮 🎧'.split(' ');
    
    function initializeEmojiPanel() {
      emojiPanel.innerHTML = '<div class="emoji-grid">'+emojiList.map(e=>`<button class="emoji-btn" type="button">${e}</button>`).join('')+'</div>';
      emojiPanel.querySelectorAll('.emoji-btn').forEach(el=>{ 
        el.addEventListener('click', ()=>{ 
          chatText.value += el.textContent; 
          chatText.focus();
          // Trigger input event to update send/audio button
          chatText.dispatchEvent(new Event('input'));
        }); 
      });
    }
    
    btnEmoji.addEventListener('click', ()=>{
      const isVisible = emojiPanel.style.display === 'block';
      if (isVisible) {
        emojiPanel.style.display = 'none';
      } else {
        initializeEmojiPanel(); // Reinitialize every time
        emojiPanel.style.display = 'block';
      }
    });
    
    // Initialize emoji panel on load
    initializeEmojiPanel();

    // Grabación de audio con preview
    let mediaRecorder = null; let audioChunks = [];
    btnAudio.addEventListener('click', async ()=>{
      try{
        if (!mediaRecorder){
          const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
          mediaRecorder = new MediaRecorder(stream);
          audioChunks = [];
          mediaRecorder.ondataavailable = (e)=>{ if(e.data.size>0) audioChunks.push(e.data); };
          mediaRecorder.onstop = async ()=>{
            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            const objectURL = URL.createObjectURL(blob);
            const row = document.createElement('div'); row.className='message-row';
            const bubble = document.createElement('div'); bubble.className='message user pending';
            const audioEl=document.createElement('audio'); audioEl.controls=true; audioEl.src=objectURL; audioEl.style.width='100%';
            bubble.appendChild(audioEl);
            const status=document.createElement('div'); status.className='message-meta'; status.textContent='Subiendo audio...'; bubble.appendChild(status);
            row.appendChild(bubble); chatBody.appendChild(row); chatBody.scrollTop = chatBody.scrollHeight;

            const form = new FormData(); form.append('file', blob, 'audio.webm');
            try{
              const r = await fetch('../api/chat/upload.php', { method:'POST', headers:{ 'Authorization': `Bearer ${token}` }, body: form });
              const data = await r.json();
              if (data.ok && data.url){
                const url = data.url;
                audioEl.src = url;
                bubble.classList.remove('pending');
                status.textContent = 'Enviado';
                const key = makeKey({ sender_type:'user', content: url });
                pendingKeys.add(key);
                await fetch(API_MSG, { method:'POST', headers:{ 'Content-Type':'application/json', 'Authorization': `Bearer ${token}` }, body: JSON.stringify({ thread_id: threadId, content: url }) });
              } else {
                status.textContent = 'Error subiendo audio';
              }
            }catch(err){ console.error('audio upload error', err); status.textContent='Error subiendo audio'; }
          };
          mediaRecorder.start();
          btnAudio.innerHTML = '<i class="bi bi-stop-circle"></i>';
          btnAudio.title = 'Detener';
        } else {
          mediaRecorder.stop();
          mediaRecorder = null;
          btnAudio.innerHTML = '<i class="bi bi-mic"></i>';
          btnAudio.title = 'Audio';
        }
      }catch(e){ console.error('audio error', e); alert('No se pudo acceder al micrófono'); }
    });

    // Lanzador: toggle abrir/cerrar
    async function openChat(){
      modal.classList.add('open');
      overlay.classList.add('open');
      document.body.style.overflow = 'hidden';
      if (!threadId) await ensureThread();
      await loadMessages();
      // Asegurar scroll al último mensaje al abrir el chat
      setTimeout(() => scrollToBottom(), 200);
      startPolling();
    }
    function closeChat(){
      modal.classList.remove('open');
      modal.classList.remove('fullscreen');
      overlay.classList.remove('open');
      document.body.style.overflow = '';
      stopPolling();
    }
    launcher.onclick = async ()=>{ 
      if (modal.classList.contains('open')) { 
        closeChat(); 
      } else { 
        await openChat(); 
      } 
    };

    // Cerrar al tocar fuera
    overlay.addEventListener('click', closeChat);

    // Opciones
    menuBtn.addEventListener('click', ()=>{ menu.classList.toggle('open'); });
    document.addEventListener('click', (e)=>{ if(!menu.contains(e.target) && e.target!==menuBtn){ menu.classList.remove('open'); } });
    optExpand.addEventListener('click', ()=>{ modal.classList.toggle('fullscreen'); });
    optTrans.addEventListener('click', ()=>{
      if (!threadId) return;
      const url = new URL(API_TRANS, location.origin);
      url.searchParams.set('thread_id', threadId);
      fetch(url, { headers:{ 'Authorization': `Bearer ${token}` }})
        .then(r=>r.blob())
        .then(blob=>{ const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=`chat_transcript_${threadId}.txt`; document.body.appendChild(a); a.click(); a.remove(); });
    });
    optClose.addEventListener('click', closeChat);

    // Cerrar con Escape
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeChat(); });

    chatSend.onclick = sendMessage;
    chatText.addEventListener('keydown', (e)=>{ if(e.key==='Enter') sendMessage(); });
    
    // Dynamic button switching (WhatsApp style)
    chatText.addEventListener('input', () => {
      const hasText = chatText.value.trim().length > 0;
      if (hasText) {
        btnAudio.style.display = 'none';
        chatSend.style.display = 'block';
      } else {
        btnAudio.style.display = 'block';
        chatSend.style.display = 'none';
      }
    });
  })();
  /* ---------- End Chat Widget ---------- */

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>