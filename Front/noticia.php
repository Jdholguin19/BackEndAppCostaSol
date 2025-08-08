<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Noticias | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">
<link href="assets/css/style_noticia.css" rel="stylesheet">
</head>
<body>

<!-- Header Section -->
<div class="noticia-header">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <div>
      <h1 class="noticia-title">Gestión de Noticias</h1>
      <p class="noticia-subtitle">Administración de comunicados</p>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="noticia-container">
  <button id="btnNuevaNoticia" class="new-noticia-btn">
    <i class="bi bi-plus"></i>
    Nueva Noticia
  </button>

  <div id="formularioNuevaNoticia" class="formulario-noticia">
      <h4>Crear Nueva Noticia</h4>
      <form id="formNoticia">
          <div class="mb-3">
              <label for="tituloNoticia" class="form-label">Título</label>
              <input type="text" class="form-control" id="tituloNoticia" required>
          </div>
          <div class="mb-3">
              <label for="resumenNoticia" class="form-label">Resumen</label>
              <textarea class="form-control" id="resumenNoticia" rows="3" required></textarea>
          </div>
          <div class="mb-3">
              <label for="urlImagenNoticia" class="form-label">URL Imagen</label>
              <input type="url" class="form-control" id="urlImagenNoticia">
          </div>
          <div class="mb-3">
              <label for="linkNoticia" class="form-label">Link Noticia</label>
              <input type="url" class="form-control" id="linkNoticia">
          </div>
          <div class="form-actions">
              <button type="submit" class="btn btn-primary">Guardar Noticia</button>
              <button type="button" class="btn btn-secondary" onclick="cancelarFormulario()">Cancelar</button>
          </div>
      </form>
  </div>

  <div id="noticias-list">
      <!-- Las noticias se cargarán aquí -->
      <div class="loading-state">
          <div class="spinner-border"></div>
          <p class="mt-2">Cargando noticias...</p>
      </div>
  </div>
</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
  <div class="bottom-nav-content">
    <a href="menu_front.php" class="nav-item">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // JavaScript logic will go here
  const noticiasListDiv = document.getElementById('noticias-list');
  const formularioNuevaNoticiaDiv = document.getElementById('formularioNuevaNoticia');
  const btnNuevaNoticia = document.getElementById('btnNuevaNoticia');
  const formNoticia = document.getElementById('formNoticia');

  // Show the form when "Nueva Noticia" button is clicked
  btnNuevaNoticia.addEventListener('click', () => {
      formularioNuevaNoticiaDiv.style.display = 'block';
      btnNuevaNoticia.style.display = 'none'; // Optionally hide the button when form is open
  });

  // Function to load news
  async function loadNoticias() {
      noticiasListDiv.innerHTML = `
          <div class="loading-state">
              <div class="spinner-border"></div>
              <p class="mt-2">Cargando noticias...</p>
          </div>
      `;
      try {
          // Assuming an API endpoint for listing news
          const response = await fetch('../api/noticias.php?limit=100'); // Increased limit for admin view
          const data = await response.json();

          noticiasListDiv.innerHTML = ''; // Clear loading spinner

          if (data.ok && data.noticias && data.noticias.length > 0) {
              data.noticias.forEach(noticia => {
                  const noticiaElement = document.createElement('div');
                  noticiaElement.classList.add('noticia-item');
                   // Add a data attribute for easy access to news ID
                  noticiaElement.setAttribute('data-id', noticia.id);

                  noticiaElement.innerHTML = `
                      <img src="${noticia.url_imagen || 'https://via.placeholder.com/80?text=Sin+Imagen'}" alt="Imagen de Noticia">
                      <div class="noticia-content">
                          <h5>${noticia.titulo}</h5>
                          <p>${noticia.resumen}</p>
                          ${noticia.link_noticia ? `<a href="${noticia.link_noticia}" target="_blank" class="btn btn-sm btn-outline-primary">Ver Noticia</a>` : ''}
                      </div>
                      <div class="noticia-actions">
                          <button class="btn btn-danger btn-sm btn-delete-noticia">Eliminar</button>
                      </div>
                  `;
                  noticiasListDiv.appendChild(noticiaElement);
              });
 
               // Add event listeners for delete buttons
              document.querySelectorAll('.btn-delete-noticia').forEach(button => {
                  button.addEventListener('click', async (event) => {
                      const noticiaElement = event.target.closest('.noticia-item');
                      const noticiaId = noticiaElement.getAttribute('data-id');
                      if (confirm('¿Estás seguro de que quieres eliminar esta noticia?')) {
                          await deleteNoticia(noticiaId);
                      }
                  });
              });


          } else if (data.ok && data.noticias && data.noticias.length === 0) {
               noticiasListDiv.innerHTML = `
                  <div class="text-center py-5">
                      <i class="bi bi-info-circle text-muted" style="font-size: 3rem;"></i>
                      <p class="text-muted mt-3">No hay noticias disponibles.</p>
                  </div>
              `;
          }
           else {
              throw new Error(data.error || 'Error al obtener noticias.');
          }

      } catch (error) {
          console.error('Error loading news:', error);
           noticiasListDiv.innerHTML = `
              <div class="error-state">
                  <div class="alert">
                      <i class="bi bi-exclamation-triangle"></i>
                      <p class="text-danger mt-3">Error al cargar las noticias</p>
                      <p class="text-muted small">${error.message}</p>
                  </div>
              </div>
          `;
      }
  }


    // Function to handle form submission for new news
    formNoticia.addEventListener('submit', async (event) => {
        event.preventDefault(); // Prevent default form submission

        const titulo = document.getElementById('tituloNoticia').value;
        const resumen = document.getElementById('resumenNoticia').value;
        const urlImagen = document.getElementById('urlImagenNoticia').value;
        const linkNoticia = document.getElementById('linkNoticia').value;

        const newNoticiaData = {
            titulo: titulo,
            resumen: resumen,
            url_imagen: urlImagen,
            link_noticia: linkNoticia
            // autor_id and fecha_publicacion will be handled by the API
            // estado and orden will be handled by the API
        };

        try {
            // Assuming an API endpoint for creating news
            const response = await fetch('../api/noticias.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // Add authorization header if needed for the API
                    // 'Authorization': `Bearer ${your_token}`
                },
                body: JSON.stringify(newNoticiaData)
            });

            const data = await response.json();

            if (data.ok) {
                alert('Noticia guardada con éxito!');
                formNoticia.reset(); // Clear the form
                formularioNuevaNoticiaDiv.style.display = 'none'; // Hide the form
                btnNuevaNoticia.style.display = 'block'; // Show the "Nueva Noticia" button
                loadNoticias(); // Reload the news list
            } else {
                alert('Error al guardar la noticia: ' + (data.error || 'Error desconocido'));
            }

        } catch (error) {
            console.error('Error saving news:', error);
            alert('Error de conexión al intentar guardar la noticia.');
        }
    });


      // Function to delete a news item
    async function deleteNoticia(noticiaId) {
        try {
            // Assuming an API endpoint for deleting news (e.g., api/noticias.php?id=...)
             const response = await fetch(`../api/noticias.php?id=${noticiaId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                     // Add authorization header if needed
                     // 'Authorization': `Bearer ${your_token}`
                }
            });

            const data = await response.json();

            if (data.ok) {
                alert('Noticia eliminada con éxito!');
                loadNoticias(); // Reload the news list
            } else {
                alert('Error al eliminar la noticia: ' + (data.error || 'Error desconocido'));
            }

        } catch (error) {
            console.error('Error deleting news:', error);
             alert('Error de conexión al intentar eliminar la noticia.');
        }
    }


  // Load news when the page is loaded
  document.addEventListener('DOMContentLoaded', loadNoticias);

</script>

</body>
</html>