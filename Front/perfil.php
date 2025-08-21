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
        <div class="info-label">Nombre</div>
        <div class="info-value" id="userName">Cargando...</div>
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

  </div>

  <!-- Logout Button -->
  <div class="logout-section">
    <button class="logout-btn" id="logoutBtn">
      <i class="bi bi-box-arrow-right"></i>
      Cerrar Sesión
    </button>
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
        // Actualizar foto de perfil
        const profilePicture = document.getElementById('profilePicture');
        profilePicture.src = data.usuario.url_foto_perfil || 'https://via.placeholder.com/120x120?text=Usuario';
        
        // Actualizar información del usuario
        document.getElementById('userName').textContent = data.usuario.nombres || data.usuario.nombre || 'No disponible';
        document.getElementById('userCedula').textContent = data.usuario.cedula || 'No disponible';
        document.getElementById('userPhone').textContent = data.usuario.telefono || 'No disponible';
        document.getElementById('userEmail').textContent = data.usuario.email || 'No disponible';
      } else {
        console.error('Error al cargar datos del perfil:', data.mensaje);
        // Mostrar valores por defecto
        document.getElementById('userName').textContent = u.nombres || u.nombre || 'No disponible';
        document.getElementById('userCedula').textContent = 'No disponible';
        document.getElementById('userPhone').textContent = 'No disponible';
        document.getElementById('userEmail').textContent = 'No disponible';
      }
    } catch (error) {
      console.error('Error al cargar perfil:', error);
      // Mostrar valores por defecto en caso de error
      document.getElementById('userName').textContent = u.nombres || u.nombre || 'No disponible';
      document.getElementById('userCedula').textContent = 'No disponible';
      document.getElementById('userPhone').textContent = 'No disponible';
      document.getElementById('userEmail').textContent = 'No disponible';
    }
  }

  /* ---------- Upload Profile Picture ---------- */
  async function uploadProfilePicture(file) {
    const token = localStorage.getItem('cs_token');
    if (!token) {
      throw new Error('No hay token disponible');
    }

    // Crear FormData para enviar el archivo
    const formData = new FormData();
    formData.append('profile_picture', file);

    try {
      const response = await fetch('../api/update_profile_picture.php', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formData
      });

      const data = await response.json();
      
      if (data.ok) {
        // Actualizar la imagen en la interfaz
        const profilePicture = document.getElementById('profilePicture');
        profilePicture.src = data.url_foto_perfil;
        
        // Mostrar mensaje de éxito
        alert('Foto de perfil actualizada correctamente');
        
        // Actualizar el localStorage si es necesario
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

  /* ---------- Event Handlers ---------- */
  
  // Botón de volver
  document.getElementById('btnBack').onclick = () => {
    // Intentar volver a la página anterior, si no hay historial, ir al menú
    if (document.referrer && document.referrer.includes(window.location.origin)) {
      history.back();
    } else {
      location.href = 'menu_front.php';
    }
  };

  // Botón de editar foto
  document.getElementById('editPictureBtn').onclick = () => {
    // Abrir el selector de archivos
    document.getElementById('fileInput').click();
  };

  // Input de archivo - cuando se selecciona una imagen
  document.getElementById('fileInput').onchange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validar que sea una imagen
    if (!file.type.startsWith('image/')) {
      alert('Por favor selecciona solo archivos de imagen');
      return;
    }

    // Validar tamaño (máximo 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('La imagen es demasiado grande. Máximo 5MB permitido');
      return;
    }

    // Mostrar indicador de carga
    const editBtn = document.getElementById('editPictureBtn');
    const originalText = editBtn.textContent;
    editBtn.textContent = 'Subiendo...';
    editBtn.disabled = true;

    try {
      await uploadProfilePicture(file);
      // Limpiar el input
      event.target.value = '';
    } catch (error) {
      console.error('Error al subir imagen:', error);
      alert('Error al subir la imagen. Intenta de nuevo.');
    } finally {
      // Restaurar botón
      editBtn.textContent = originalText;
      editBtn.disabled = false;
    }
  };

  // Botón de cerrar sesión
  document.getElementById('logoutBtn').onclick = async () => {
    if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
      await logout();
    }
  };

  /* ---------- Logout Function ---------- */
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

  /* ---------- Initialize ---------- */
  // Cargar datos del perfil cuando la página se carga
  document.addEventListener('DOMContentLoaded', loadProfileData);

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
