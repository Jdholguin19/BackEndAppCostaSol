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
              await new Promise(resolve => setTimeout(resolve, 2000));
              
              // Obtener el Player ID usando acceso directo a la propiedad
              const playerId = OneSignal.User.PushSubscription.id;
              
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
                              user_id: u.id,
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
<hr style="color:rgba(69, 67, 67, 0.33); margin-top: 25px; ">
<div class="header-section">
  <div class="welcome-card">
    <div class="welcome-info">
      <p class="welcome-name" id="welcomeName">Hola, Usuario</p>
      <p class="welcome-prop"><i>Elija su propiedad:</i></p>
    </div>
    <!-- Contenedor para el avatar y el botón de cerrar sesión -->
    <div class="avatar-container">
        <img src="" class="avatar" id="welcomeAvatar" alt="Avatar">
        <button id="logoutButton" class="logout-button" style="display: none;">Cerrar sesión</button>
    </div>
  </div>
  
  <!-- Property Tabs -->
  <div class="property-tabs" id="propertyTabs" style="display: none;">
    <!-- Property tabs will be dynamically added here -->
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
</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
  <div class="bottom-nav-content">
    <a href="#" class="nav-item active">
      <i class="bi bi-house"></i>
      <span>Inicio</span>
    </a>
    <a href="notificaciones.php" class="nav-item">
      <i class="bi bi-bell"></i>
      <span>Notificaciones</span>
    </a>
    <a href="citas.php" class="nav-item">
      <i class="bi bi-calendar"></i>
      <span>Cita</span>
    </a>
    <a href="ctg/ctg.php" class="nav-item">
      <i class="bi bi-file-text"></i>
      <span>CTG</span>
    </a>
    <a href="pqr/pqr.php" class="nav-item">
      <i class="bi bi-chat-dots"></i>
      <span>PQR</span>
    </a>
  </div>
</div>

<script>
  /* ---------- USER ---------- */
  console.log('Valor en localStorage para cs_usuario:', localStorage.getItem('cs_usuario')); // Registro
  const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
  if (!u.id) location.href = 'login_front.php';

  /* ---------- Endpoints ---------- */
  const API_NEWS  = '../api/noticias.php?limit=10';
  const API_MENU  = '../api/menu.php?role_id=' + u.rol_id;
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


    if ( menu.id === 6 ||
        (menu.nombre || '').toUpperCase().includes('VISITA') ){
        location.href = 'garantias.php';          // <<<<<<
        return;
    }

    if ( menu.id === 3 ||
        (menu.nombre || '').toUpperCase().includes('VISITA') ){
        location.href = 'citas.php';          // <<<<<<
        return;
    }

      if ( menu.id === 7 ||
        (menu.nombre || '').toUpperCase().includes('VISITA') ){
        location.href = 'panel_calendario.php';          // <<<<<<
        return;
    }

      if ( menu.id === 8 ||
        (menu.nombre || '').toUpperCase().includes('VISITA') ){
        location.href = 'notificaciones.php';          // <<<<<<
        return;
    }  


      if ( menu.id === 9 ||
        (menu.nombre || '').toUpperCase().includes('VISITA') ){
        location.href = 'pqr/pqr.php';          // <<<<<<
        return;
    }  


    if ( menu.id === 10 ||
        (menu.nombre || '').toUpperCase().includes('VISITA') ){
        location.href = 'users.php';          // <<<<<<
        return;
    } 



    // …aquí podrías despachar otros módulos por nombre o id
    alert('Abrir módulo '+menu.id);
  }

  /* ---------- Noticias ---------- */
  fetch(API_NEWS).then(r=>r.json()).then(({ok,noticias})=>{
    if(!ok){document.getElementById('newsSpinner').textContent='Error noticias';return;}
    const row=document.getElementById('newsRow');row.innerHTML='';
    
    const newsCard = document.createElement('div');
    newsCard.className = 'news-card';
    newsCard.innerHTML = `
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
        <div class="carousel-indicators outside">
          ${noticias.map((_,i)=>`<button type="button" data-bs-target="#newsCarousel" data-bs-slide-to="${i}"
                    ${i?'':'class="active"'}></button>`).join('')}
        </div>
      </div>`;
    row.appendChild(newsCard);
  });

  /* ---------- Propiedades ---------- */
  let currentProp=null;
  let currentManzana = '', currentSolar = '';

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

  fetch(API_PROP).then(r=>r.json()).then(({ok,propiedades})=>{
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
        currentManzana = p.manzana;
        currentSolar = p.solar;
        loadFase(p.id);
      };
      tabsContainer.appendChild(tab);
      
      if(i===0){
        currentProp=p.id;  
        currentManzana=p.manzana;
        currentSolar=p.solar; 
        loadFase(p.id);
      }
    });
  });

  /* ---------- Menú ---------- */
  fetch(API_MENU).then(r=>r.json()).then(({ok,menus})=>{
    const grid=document.getElementById('menuGrid');
    document.getElementById('menuSpinner').remove();
    if(!ok){grid.textContent='Error menú';return;}
    
    grid.innerHTML = ''; // Clear spinner
    
    menus.forEach(m=>{
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