<?php /* Front/menu_front.php */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menú Administrativo | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../assets/css/style_main2.css" rel="stylesheet">

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
    <p><button class="back-button" id="btnBack"><i class="bi bi-arrow-left"></i></button> Menú Administrativo</p>
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
include '../../api/bottom_nav.php'; 
?>


<script>
  /* ---------- USER ---------- */
  console.log('Valor en localStorage para cs_usuario:', localStorage.getItem('cs_usuario')); // Registro
  const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
  if (!u.id) location.href = 'login_front.php';
  if (!u.is_responsable) location.href = '../menu2.php'; // Redirigir si no es responsable

  const is_admin_responsible = u.is_responsable; // Modificado: es verdadero para cualquier responsable

  /* ---------- Endpoints ---------- */
  const API_NEWS  = '../../api/noticias.php?limit=10';
  const API_MENU  = '../../api/menu.php';
  const API_PROP  = '../../api/obtener_propiedades.php?id_usuario=' + u.id;
  const API_FASE  = '../../api/propiedad_fase.php?id_propiedad=';

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

  async function logModuleAccess(menuId, menuName, token) {
    try {
      await fetch('../../api/log_module_access.php', {
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
    const token = localStorage.getItem('cs_token'); // Get token here as it's not global in this scope
    await logModuleAccess(menu.id, menu.nombre, token);

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
 

      if ( menu.id === 7 ||
        (menu.nombre || '').toUpperCase().includes('CALENDARIO') ){
        location.href = 'panel_calendario.php';          // <<<<<<
        return;
    }


    if ( menu.id === 10 ||
        (menu.nombre || '').toUpperCase().includes('USER') ){
        location.href = '../users.php';          // <<<<<<
        return;
    }



    if ( menu.id === 13 ||
        (menu.nombre || '').toUpperCase().includes('NOTICIA') ){
        location.href = '../noticia.php';          // <<<<<<
        return;
    }


    if ( menu.id === 14 ||
        (menu.nombre || '').toUpperCase().includes('AUDITORIA') ){
        location.href = '../../auditoria/dashboard.php';          // <<<<<<
        return;
    }

    if ( menu.id === 16 ||
        (menu.nombre || '').toUpperCase().includes('ACABADOS') ){
        location.href = '../admin_acabados.php';          // <<<<<<
        return;
    }



    // …aquí podrías despachar otros módulos por nombre o id
    alert('Abrir módulo '+menu.id);
  }

  /* ---------- Menú ---------- */
  // No necesitamos check de garantías para admin
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

      // Filtrar solo menús de responsables: IDs 10,13,14,16,17
      const adminMenuIds = [10, 13, 14, 16];
      const menusFiltrados = menus.filter(menu => adminMenuIds.includes(Number(menu.id)));
      
      menusFiltrados.forEach(m=>{
        const card = document.createElement('div');
        card.className='menu-card';
        card.onclick = () => openModule(m);
        
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

  /* ---------- Logout ---------- */

  // Función para cerrar sesión (puedes llamarla desde un botón)
  async function logout() { 
    const token = localStorage.getItem('cs_token');
    if (!token) { 
      window.location.href = 'login_front.php';
      return;
    } 
    try { 
      const response = await fetch('../../api/logout.php', {
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
  document.getElementById('btnBack').onclick  = () => location.href='../menu2.php';


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