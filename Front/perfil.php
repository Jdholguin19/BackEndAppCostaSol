<?php /* Front/perfil.php */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil de Usuario</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">
<link href="assets/css/style_perfil.css" rel="stylesheet">

<style>
  /* Estilos para el botón de resuscripción */
  .resubscribe-btn {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 0.9rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
  }

  .resubscribe-btn:hover {
      background-color: #218838;
  }

  /* Estilo para el botón en estado "Desuscrito" */
  .unsubscribe-btn.unsubscribed-state {
      background-color: transparent;
      border: 1px solid #28a745;
      color: #333;
  }

  /* Contenedor de botones de notificación para Flexbox */
  .notification-buttons-container {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
  }

  /* Eliminar margen izquierdo hardcodeado */
  #resubscribeBtn {
      margin-left: 0 !important;
  }

  /* Estilos responsivos para los botones de notificación */
  @media (max-width: 480px) {
      .notification-buttons-container {
          flex-direction: column;
          align-items: stretch; /* Estira los botones al ancho completo */
      }
  }

  /* --- ESTILOS PARA EL SWITCH DE NOTIFICACIONES --- */
  .switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 28px;
  }

  .switch input { 
    opacity: 0;
    width: 0;
    height: 0;
  }

  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
  }

  input:checked + .slider {
    background-color: #28a745;
  }

  input:focus + .slider {
    box-shadow: 0 0 1px #28a745;
  }

  input:checked + .slider:before {
    -webkit-transform: translateX(22px);
    -ms-transform: translateX(22px);
    transform: translateX(22px);
  }

  .slider.round {
    border-radius: 28px;
  }

  .slider.round:before {
    border-radius: 50%;
  }
  /* --- FIN DE ESTILOS PARA EL SWITCH --- */
</style>

</head>
<body>

<!-- Header Section -->
<div class="header-section">
  <div class="welcome-card">
    <div class="welcome-info">
      <button class="back-button" id="btnBack"><i class="bi bi-arrow-left"></i></button>
      <h4 class="page-title">Perfil</h4>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  
  <!-- Profile Picture Section -->
  <div class="profile-picture-section">
    <div class="profile-picture-container">
      <img src="" class="profile-picture" id="profilePicture" alt="Foto de perfil">
      <button class="edit-picture-btn" id="editPictureBtn">Editar</button>
      <!-- Input oculto para seleccionar archivo -->
      <input type="file" id="fileInput" accept="image/*" style="display: none;">
    </div>
  </div>

  <!-- Profile Information -->
  <div class="profile-info">
    
    <!-- Nombre -->
    <div class="info-item">
      <div class="info-icon">
        <i class="bi bi-person"></i>
      </div>
      <div class="info-content">
        <div class="info-label">Nombres </div>
        <div class="info-value" id="userName">Cargando...</div><div class="info-value" id="userLastName">Cargando...</div>
        
      </div>
    </div>

    <!-- Cédula -->
    <div class="info-item">
      <div class="info-icon">
        <i class="bi bi-card-text"></i>
      </div>
      <div class="info-content">
        <div class="info-label">Cédula</div>
        <div class="info-value" id="userCedula">Cargando...</div>
      </div>
    </div>

    <!-- Teléfono -->
    <div class="info-item">
      <div class="info-icon">
        <i class="bi bi-telephone"></i>
      </div>
      <div class="info-content">
        <div class="info-label">Teléfono</div>
        <div class="info-value" id="userPhone">Cargando...</div>
      </div>
    </div>

    <!-- Correo -->
    <div class="info-item">
      <div class="info-icon">
        <i class="bi bi-envelope"></i>
      </div>
      <div class="info-content">
        <div class="info-label">Correo</div>
        <div class="info-value" id="userEmail">Cargando...</div>
      </div>
    </div>

    <!-- Notificaciones -->
    <div class="info-item">
      <div class="info-icon">
        <i class="bi bi-bell"></i>
      </div>
      <div class="info-content">
        <div class="info-label">Notificaciones</div>
        <div class="info-value">
          <label class="switch">
            <input type="checkbox" id="notificationSwitch">
            <span class="slider round"></span>
          </label>
        </div>
      </div>
    </div>

  </div>

  <!-- Logout Button -->
  <div class="logout-section">
    <button class="logout-btn" id="logoutBtn">
      <i class="bi bi-box-arrow-right"></i>
      Cerrar Sesión
    </button>
  </div>

</div>

<!-- Ventana emergente de confirmación de desuscripción -->
<div id="unsubscribeModal" class="unsubscribe-modal">
  <div class="unsubscribe-content">
    <h3 class="unsubscribe-title">¿Seguro que quieres desuscribirte?</h3>
    <div class="unsubscribe-buttons">
      <button class="btn-no-thanks" onclick="hideUnsubscribeModal()">No, gracias</button>
      <button class="btn-unsubscribe" onclick="unsubscribeFromNotifications()">Desuscribirse</button>
    </div>
  </div>
</div>

<?php 
$active_page = 'perfil';
include '../api/bottom_nav.php'; 
?>

<script>
  /* ---------- USER ---------- */
  console.log('Valor en localStorage para cs_usuario:', localStorage.getItem('cs_usuario'));
  const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
  if (!u.id) location.href = 'login_front.php';

  /* ---------- Profile Data Loading ---------- */
  async function loadProfileData() {
    const token = localStorage.getItem('cs_token');
    if (!token) {
      console.error('No hay token disponible');
      return;
    }

    try {
      const response = await fetch('../api/perfil.php', {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      const data = await response.json();
      
      if (data.ok) {
        const profilePicture = document.getElementById('profilePicture');
        profilePicture.src = data.usuario.url_foto_perfil || 'https://via.placeholder.com/120x120?text=Usuario';
        document.getElementById('userName').textContent = data.usuario.nombres || data.usuario.nombre || 'No disponible';
        document.getElementById('userLastName').textContent = data.usuario.apellidos || 'No disponible';
        document.getElementById('userCedula').textContent = data.usuario.cedula || 'No disponible';
        document.getElementById('userPhone').textContent = data.usuario.telefono || 'No disponible';
        document.getElementById('userEmail').textContent = data.usuario.email || 'No disponible';
      } else {
        console.error('Error al cargar datos del perfil:', data.mensaje);
        document.getElementById('userName').textContent = u.nombres || u.nombre || 'No disponible';
        document.getElementById('userCedula').textContent = 'No disponible';
        document.getElementById('userPhone').textContent = 'No disponible';
        document.getElementById('userEmail').textContent = 'No disponible';
      }
    } catch (error) {
      console.error('Error al cargar perfil:', error);
      document.getElementById('userName').textContent = u.nombres || u.nombre || 'No disponible';
      document.getElementById('userCedula').textContent = 'No disponible';
      document.getElementById('userPhone').textContent = 'No disponible';
      document.getElementById('userEmail').textContent = 'No disponible';
    }
  }

  /* ---------- Upload Profile Picture ---------- */
  async function uploadProfilePicture(file) {
    const token = localStorage.getItem('cs_token');
    if (!token) throw new Error('No hay token disponible');

    const formData = new FormData();
    formData.append('profile_picture', file);

    try {
      const response = await fetch('../api/update_profile_picture.php', {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` },
        body: formData
      });

      const data = await response.json();
      
      if (data.ok) {
        document.getElementById('profilePicture').src = data.url_foto_perfil;
        alert('Foto de perfil actualizada correctamente');
        const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
        u.url_foto_perfil = data.url_foto_perfil;
        localStorage.setItem('cs_usuario', JSON.stringify(u));
      } else {
        throw new Error(data.mensaje || 'Error al actualizar la foto');
      }
    } catch (error) {
      console.error('Error en la subida:', error);
      throw error;
    }
  }

  /* ---------- OneSignal Switch Logic ---------- */
  const notificationSwitch = document.getElementById('notificationSwitch');

  // Mostrar ventana emergente de desuscripción
  function showUnsubscribeModal() {
    const modal = document.getElementById('unsubscribeModal');
    if (modal) {
      modal.classList.add('show');
    }
  }

  // Ocultar ventana emergente de desuscripción
  function hideUnsubscribeModal() {
    const modal = document.getElementById('unsubscribeModal');
    if (modal) {
      modal.classList.remove('show');
    }
  }

  // Función para desuscribirse de las notificaciones
  async function unsubscribeFromNotifications() {
    try {
      // Deshabilitar el switch durante el proceso
      notificationSwitch.disabled = true;

      // Ocultar la ventana emergente
      hideUnsubscribeModal();

      // Intentar desuscribirse de OneSignal
      if (window.OneSignal) {
        try {
          // En OneSignal v16, intentar desuscribirse
          if (window.OneSignal.setSubscription && typeof window.OneSignal.setSubscription === 'function') {
            await window.OneSignal.setSubscription(false);
            console.log('Desuscripción exitosa con OneSignal.setSubscription');
          } else if (window.OneSignal.User && window.OneSignal.User.PushSubscription) {
            // Intentar con la API de User
            await window.OneSignal.User.PushSubscription.optOut();
            console.log('Desuscripción exitosa con User.PushSubscription.optOut');
          } else {
            // Fallback: usar la API nativa del navegador
            console.log('Usando fallback para desuscripción');
          }
        } catch (e) {
          console.warn('Error con OneSignal, usando fallback:', e);
        }
      }

      // Obtener el token para actualizar el backend
      const token = localStorage.getItem('cs_token');
      if (token) {
        try {
          // Actualizar el Player ID a cadena vacía en el backend (temporalmente)
          // TODO: Cambiar a null cuando la API esté actualizada
          const response = await fetch('../api/update_player_id.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
              onesignal_player_id: "" // Cadena vacía temporalmente en lugar de null
            })
          });
          
          const data = await response.json();
          if (data.ok) {
            console.log('Player ID eliminado del servidor:', data.mensaje);
          } else {
            console.warn('Error al eliminar Player ID del servidor:', data.mensaje);
          }
        } catch (e) {
          console.warn('Error al comunicarse con el servidor:', e);
        }
      }

      // Marcar en localStorage que el usuario se desuscribió
      localStorage.setItem('onesignal_declined', 'true');
      localStorage.setItem('onesignal_unsubscribed', 'true');
      
      // Eliminar cualquier Player ID almacenado
      localStorage.removeItem('onesignal_player_id');

      // Actualizar el switch
      notificationSwitch.checked = false;
      
      // Mostrar mensaje de éxito
      alert('Te has desuscrito exitosamente de las notificaciones push.');

    } catch (error) {
      console.error('Error al desuscribirse:', error);
      alert('Error al desuscribirse. Por favor, inténtalo de nuevo.');
      
      // Revertir el switch en caso de error
      notificationSwitch.checked = true;
    } finally {
      notificationSwitch.disabled = false;
    }
  }

  // Función para volver a suscribirse a las notificaciones
  async function resubscribeToNotifications() {
    try {
      // Deshabilitar el switch durante el proceso
      notificationSwitch.disabled = true;

      // Limpiar el estado de desuscripción
      localStorage.removeItem('onesignal_unsubscribed');
      localStorage.removeItem('onesignal_declined');

      // Intentar suscribirse a OneSignal
      if (window.OneSignal) {
        try {
          if (window.OneSignal.showSlidedownPrompt) {
            await window.OneSignal.showSlidedownPrompt();
          } else if (window.OneSignal.showNativePrompt) {
            await window.OneSignal.showNativePrompt();
          } else {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
              console.log('Permisos de notificación concedidos');
            }
          }
        } catch (e) {
          console.warn('Error con OneSignal, usando fallback:', e);
          const permission = await Notification.requestPermission();
          if (permission === 'granted') {
            console.log('Permisos de notificación concedidos');
          }
        }
      }

      // Esperar un momento y verificar si se suscribió
      setTimeout(async () => {
        try {
          let isSubscribed = false;
          
          if (window.OneSignal && window.OneSignal.User && window.OneSignal.User.PushSubscription) {
            const playerId = window.OneSignal.User.PushSubscription.id;
            isSubscribed = !!playerId;
            console.log('Player ID después de resuscripción:', playerId);
          } else {
            const permission = await Notification.requestPermission();
            isSubscribed = permission === 'granted';
            console.log('Permisos nativos después de resuscripción:', permission);
          }
          
          if (isSubscribed) {
            console.log('Usuario resuscrito exitosamente');
            
            // Actualizar el switch
            notificationSwitch.checked = true;
            
            // Mostrar mensaje de éxito
            alert('Te has resuscrito exitosamente a las notificaciones push.');
          } else {
            console.log('Usuario aún no está suscrito después del intento');
            notificationSwitch.checked = false; // Revertir si no se suscribió
          }
          notificationSwitch.disabled = false;
        } catch (e) {
          console.warn('Error al verificar resuscripción:', e);
          notificationSwitch.checked = false; // Revertir en caso de error
          notificationSwitch.disabled = false;
        }
      }, 2000);

    } catch (error) {
      console.error('Error al resuscribirse:', error);
      alert('Error al resuscribirse. Por favor, inténtalo de nuevo.');
      
      // Revertir el switch en caso de error
      notificationSwitch.checked = false;
      notificationSwitch.disabled = false;
    }
  }

  /* ---------- Event Handlers ---------- */
  document.getElementById('btnBack').onclick = () => {
    if (document.referrer && document.referrer.includes(window.location.origin)) {
      history.back();
    } else {
      location.href = 'menu_front.php';
    }
  };

  document.getElementById('editPictureBtn').onclick = () => {
    document.getElementById('fileInput').click();
  };

  document.getElementById('fileInput').onchange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      alert('Por favor selecciona solo archivos de imagen');
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      alert('La imagen es demasiado grande. Máximo 5MB permitido');
      return;
    }

    const editBtn = document.getElementById('editPictureBtn');
    const originalText = editBtn.textContent;
    editBtn.textContent = 'Subiendo...';
    editBtn.disabled = true;

    try {
      await uploadProfilePicture(file);
      event.target.value = '';
    } catch (error) {
      alert('Error al subir la imagen. Intenta de nuevo.');
    } finally {
      editBtn.textContent = originalText;
      editBtn.disabled = false;
    }
  };

  document.getElementById('logoutBtn').onclick = async () => {
    if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
      await logout();
    }
  };

  // Event listener para el switch de notificaciones
  notificationSwitch.addEventListener('change', (event) => {
    if (event.target.checked) {
      resubscribeToNotifications();
    } else {
      showUnsubscribeModal();
    }
  });

  /* ---------- Logout Function ---------- */
  async function logout() { 
    const token = localStorage.getItem('cs_token');
    if (!token) { 
      window.location.href = 'login_front.php';
      return;
    } 
    
    try { 
      await fetch('../api/logout.php', {
        method: 'POST', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify({ token: token }) 
      });
    } catch (error) { 
      console.error('Error de conexión:', error);
    } finally {
      localStorage.removeItem('cs_token');
      localStorage.removeItem('cs_usuario');
      window.location.href = 'login_front.php';
    } 
  }

  /* ---------- Initialize ---------- */
  document.addEventListener('DOMContentLoaded', function() {
    loadProfileData();
    
    // Verificar si el usuario ya está desuscrito y configurar el switch
    const isUnsubscribed = localStorage.getItem('onesignal_unsubscribed') === 'true';
    notificationSwitch.checked = !isUnsubscribed;

    // Configurar los botones del modal
    document.querySelector('.btn-no-thanks').onclick = () => {
      hideUnsubscribeModal();
      // Revertir el switch si el usuario cancela
      notificationSwitch.checked = true;
    };
    
    document.querySelector('.btn-unsubscribe').onclick = unsubscribeFromNotifications;
  });

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
