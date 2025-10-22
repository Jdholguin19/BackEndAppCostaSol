<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<title>Gestión de Noticias | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css?v=<?php echo time(); ?>" rel="stylesheet">
<link href="assets/css/style_noticia.css?v=<?php echo time(); ?>" rel="stylesheet">
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
  <button id="btnNuevaNoticia" class="new-noticia-btn" data-bs-toggle="modal" data-bs-target="#modalNuevaNoticia">
    <i class="bi bi-plus"></i>
    Nueva Noticia
  </button>

  <div id="noticias-list">
      <!-- Las noticias se cargarán aquí -->
      <div class="loading-state">
          <div class="spinner-border"></div>
          <p class="mt-2">Cargando noticias...</p>
      </div>
  </div>
</div>

<!-- Modal Nueva Noticia -->
<div class="modal fade" id="modalNuevaNoticia" tabindex="-1" aria-labelledby="modalNuevaNoticiaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalNuevaNoticiaLabel">
          <i class="bi bi-plus-circle"></i> Nueva Noticia
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formNoticia">
          <div class="mb-3">
            <label for="tituloNoticia" class="form-label">Título</label>
            <input type="text" class="form-control" id="tituloNoticia" placeholder="Ingrese el título de la noticia" required>
          </div>
          <div class="mb-3">
            <label for="resumenNoticia" class="form-label">Resumen</label>
            <textarea class="form-control" id="resumenNoticia" rows="3" placeholder="Ingrese un resumen breve de la noticia" required></textarea>
          </div>
          <div class="mb-3">
            <label for="imagenNoticia" class="form-label">Imagen</label>
            <div class="image-upload-area">
              <input type="file" class="form-control" id="imagenNoticia" accept="image/*" required>
              <small class="text-muted d-block mt-2">Formatos permitidos: JPG, PNG, GIF (máx. 2MB)</small>
              <div id="imagePreview" class="mt-2"></div>
            </div>
          </div>
          <div class="mb-3">
            <label for="linkNoticia" class="form-label">Link Noticia (opcional)</label>
            <input type="url" class="form-control" id="linkNoticia" placeholder="https://ejemplo.com">
          </div>

          <!-- Sección de Destinatarios -->
          <div class="destinatarios-section">
            <h6 class="mb-3"><i class="bi bi-people"></i> Destinatarios de Notificación</h6>
            
            <div class="form-check mb-2">
              <input class="form-check-input destinatario-check" type="checkbox" id="chkTodos" value="todos">
              <label class="form-check-label" for="chkTodos">
                <strong>Enviar a todos los usuarios</strong>
              </label>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input destinatario-check" type="checkbox" id="chkClientes" value="clientes">
              <label class="form-check-label" for="chkClientes">
                Enviar a todos los usuarios con rol <span class="badge bg-info">Cliente (Rol ID: 1)</span>
              </label>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input destinatario-check" type="checkbox" id="chkResidentes" value="residentes">
              <label class="form-check-label" for="chkResidentes">
                Enviar a todos los usuarios con rol <span class="badge bg-success">Residente (Rol ID: 2)</span>
              </label>
            </div>

            <small class="text-muted d-block">
              <i class="bi bi-info-circle"></i> Selecciona al menos una opción de destinatarios
            </small>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarNoticia">
          <i class="bi bi-check-circle"></i> Guardar Noticia
        </button>
      </div>
    </div>
  </div>
</div>

<?php 
$active_page = 'inicio';
include '../api/bottom_nav.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  /* ================= REFERENCIAS DOM ================= */
  const noticiasListDiv = document.getElementById('noticias-list');
  const formNoticia = document.getElementById('formNoticia');
  const imagenInput = document.getElementById('imagenNoticia');
  const imagePreview = document.getElementById('imagePreview');
  const btnGuardarNoticia = document.getElementById('btnGuardarNoticia');
  const modalNuevaNoticia = new bootstrap.Modal(document.getElementById('modalNuevaNoticia'));

  /* ================= CARGAR NOTICIAS ================= */
  async function loadNoticias() {
      noticiasListDiv.innerHTML = `
          <div class="loading-state">
              <div class="spinner-border"></div>
              <p class="mt-2">Cargando noticias...</p>
          </div>
      `;
      try {
          const response = await fetch('../api/noticias.php?limit=100');
          const data = await response.json();

          noticiasListDiv.innerHTML = '';

          if (data.ok && data.noticias && data.noticias.length > 0) {
              const fragment = document.createDocumentFragment();
              data.noticias.forEach(noticia => {
                  const noticiaElement = document.createElement('div');
                  noticiaElement.classList.add('noticia-card');
                  noticiaElement.setAttribute('data-id', noticia.id);

                  const imagenSrc = noticia.url_imagen || 'https://via.placeholder.com/300x150?text=Sin+Imagen';
                  
                  noticiaElement.innerHTML = `
                      <div class="noticia-card-image">
                          <img src="${imagenSrc}" alt="Imagen de Noticia" loading="lazy">
                      </div>
                      <div class="noticia-card-content">
                          <h5 class="noticia-card-title">${noticia.titulo}</h5>
                          <p class="noticia-card-text">${noticia.resumen}</p>
                          ${noticia.link_noticia ? `<a href="${noticia.link_noticia}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Ver Noticia</a>` : ''}
                      </div>
                      <div class="noticia-card-actions">
                          <button class="btn btn-sm btn-danger btn-delete-noticia" title="Eliminar">
                              <i class="bi bi-trash"></i>
                          </button>
                      </div>
                  `;
                  
                  // Forzar tamaño de imagen después de cargar
                  const imgElement = noticiaElement.querySelector('img');
                  if (imgElement) {
                      imgElement.removeAttribute('width');
                      imgElement.removeAttribute('height');
                      imgElement.style.cssText = 'width: 100% !important; height: 200px !important; max-width: 100% !important; object-fit: cover !important; display: block !important;';
                  }
                  
                  fragment.appendChild(noticiaElement);
              });
              noticiasListDiv.appendChild(fragment);

              document.querySelectorAll('.btn-delete-noticia').forEach(button => {
                  button.addEventListener('click', async (event) => {
                      const noticiaElement = event.target.closest('.noticia-card');
                      const noticiaId = noticiaElement.getAttribute('data-id');
                      if (confirm('¿Estás seguro de que quieres eliminar esta noticia?')) {
                          await deleteNoticia(noticiaId);
                      }
                  });
              });

          } else if (data.ok && data.noticias && data.noticias.length === 0) {
              noticiasListDiv.innerHTML = `
                  <div class="empty-state">
                      <i class="bi bi-inbox"></i>
                      <p>No hay noticias disponibles</p>
                      <small>Crea una nueva noticia para comenzar</small>
                  </div>
              `;
          } else {
              throw new Error(data.error || 'Error al obtener noticias.');
          }

      } catch (error) {
          console.error('Error loading news:', error);
          noticiasListDiv.innerHTML = `
              <div class="error-state">
                  <div class="alert alert-danger" role="alert">
                      <i class="bi bi-exclamation-triangle"></i>
                      <p class="mt-2">Error al cargar las noticias</p>
                      <small>${error.message}</small>
                  </div>
              </div>
          `;
      }
  }

  /* ================= PREVIEW DE IMAGEN ================= */
  imagenInput.addEventListener('change', function(event) {
      const file = event.target.files[0];
      if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
              imagePreview.innerHTML = `
                  <div class="preview-image">
                      <img src="${e.target.result}" alt="Preview">
                      <small class="text-muted d-block mt-2">${file.name}</small>
                  </div>
              `;
          };
          reader.readAsDataURL(file);
      } else {
          imagePreview.innerHTML = '';
      }
  });

  /* ================= CONTROL DE DESTINATARIOS ================= */
  const chkTodos = document.getElementById('chkTodos');
  const chkClientes = document.getElementById('chkClientes');
  const chkResidentes = document.getElementById('chkResidentes');

  // Cuando se selecciona "Todos", deshabilitar los otros
  chkTodos.addEventListener('change', function() {
      if (this.checked) {
          chkClientes.checked = false;
          chkResidentes.checked = false;
          chkClientes.disabled = true;
          chkResidentes.disabled = true;
      } else {
          chkClientes.disabled = false;
          chkResidentes.disabled = false;
      }
  });

  // Cuando se selecciona cualquiera de los específicos, desseleccionar "Todos"
  [chkClientes, chkResidentes].forEach(chk => {
      chk.addEventListener('change', function() {
          if (this.checked) {
              chkTodos.checked = false;
          }
      });
  });

  /* ================= GUARDAR NOTICIA ================= */
  btnGuardarNoticia.addEventListener('click', async () => {
      if (!formNoticia.checkValidity()) {
          formNoticia.reportValidity();
          return;
      }

      // Validar que al menos un destinatario esté seleccionado
      const chkTodos = document.getElementById('chkTodos');
      const chkClientes = document.getElementById('chkClientes');
      const chkResidentes = document.getElementById('chkResidentes');

      if (!chkTodos.checked && !chkClientes.checked && !chkResidentes.checked) {
          alert('Por favor selecciona al menos un destinatario para la notificación');
          return;
      }

      const titulo = document.getElementById('tituloNoticia').value;
      const resumen = document.getElementById('resumenNoticia').value;
      const imagenFile = imagenInput.files[0];
      const linkNoticia = document.getElementById('linkNoticia').value;

      if (!imagenFile) {
          alert('Por favor selecciona una imagen');
          return;
      }

      // Validar tamaño de imagen (máx 2MB)
      if (imagenFile.size > 2 * 1024 * 1024) {
          alert('La imagen no debe superar 2MB');
          return;
      }

      // Determinar destinatarios
      let destinatarios = {
          todos: chkTodos.checked,
          clientes: chkClientes.checked,
          residentes: chkResidentes.checked
      };

      btnGuardarNoticia.disabled = true;
      btnGuardarNoticia.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

      const formData = new FormData();
      formData.append('titulo', titulo);
      formData.append('resumen', resumen);
      formData.append('imagen', imagenFile);
      formData.append('link_noticia', linkNoticia);
      formData.append('destinatarios', JSON.stringify(destinatarios));

      try {
          const response = await fetch('../api/noticias.php', {
              method: 'POST',
              body: formData
          });

          const data = await response.json();

          if (data.ok) {
              alert('¡Noticia guardada y notificaciones enviadas!');
              formNoticia.reset();
              imagePreview.innerHTML = '';
              chkTodos.checked = false;
              chkClientes.checked = false;
              chkResidentes.checked = false;
              modalNuevaNoticia.hide();
              loadNoticias();
          } else {
              alert('Error al guardar la noticia: ' + (data.error || 'Error desconocido'));
          }

      } catch (error) {
          console.error('Error saving news:', error);
          alert('Error de conexión al intentar guardar la noticia.');
      } finally {
          btnGuardarNoticia.disabled = false;
          btnGuardarNoticia.innerHTML = '<i class="bi bi-check-circle"></i> Guardar Noticia';
      }
  });

  /* ================= GUARDAR NOTICIA (VERSIÓN VIEJA - ELIMINAR) ================= */
  // Esta sección fue reemplazada arriba - el evento se maneja con onclick en el botón

  /* ================= VERSIÓN ANTERIOR DESHABILITADA
  btnGuardarNoticia.addEventListener('click', async () => {
      if (!formNoticia.checkValidity()) {
          formNoticia.reportValidity();
          return;
      }

      const titulo = document.getElementById('tituloNoticia').value;
      const resumen = document.getElementById('resumenNoticia').value;
      const imagenFile = imagenInput.files[0];
      const linkNoticia = document.getElementById('linkNoticia').value;

      if (!imagenFile) {
          alert('Por favor selecciona una imagen');
          return;
      }

      // Validar tamaño de imagen (máx 2MB)
      if (imagenFile.size > 2 * 1024 * 1024) {
          alert('La imagen no debe superar 2MB');
          return;
      }

      btnGuardarNoticia.disabled = true;
      btnGuardarNoticia.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

      const formData = new FormData();
      formData.append('titulo', titulo);
      formData.append('resumen', resumen);
      formData.append('imagen', imagenFile);
      formData.append('link_noticia', linkNoticia);

      try {
          const response = await fetch('../api/noticias.php', {
              method: 'POST',
              body: formData
          });

          const data = await response.json();

          if (data.ok) {
              alert('¡Noticia guardada con éxito!');
              formNoticia.reset();
              imagePreview.innerHTML = '';
              modalNuevaNoticia.hide();
              loadNoticias();
          } else {
              alert('Error al guardar la noticia: ' + (data.error || 'Error desconocido'));
          }

      } catch (error) {
          console.error('Error saving news:', error);
          alert('Error de conexión al intentar guardar la noticia.');
      } finally {
          btnGuardarNoticia.disabled = false;
          btnGuardarNoticia.innerHTML = '<i class="bi bi-check-circle"></i> Guardar Noticia';
      }
  });

  /* ================= ELIMINAR NOTICIA ================= */
  async function deleteNoticia(noticiaId) {
      try {
          const response = await fetch(`../api/noticias.php?id=${noticiaId}`, {
              method: 'DELETE',
              headers: {
                  'Content-Type': 'application/json'
              }
          });

          const data = await response.json();

          if (data.ok) {
              alert('¡Noticia eliminada con éxito!');
              loadNoticias();
          } else {
              alert('Error al eliminar la noticia: ' + (data.error || 'Error desconocido'));
          }

      } catch (error) {
          console.error('Error deleting news:', error);
          alert('Error de conexión al intentar eliminar la noticia.');
      }
  }

  /* ================= RESET FORMULARIO AL CERRAR MODAL ================= */
  document.getElementById('modalNuevaNoticia').addEventListener('hidden.bs.modal', () => {
      formNoticia.reset();
      imagePreview.innerHTML = '';
  });

  /* ================= CARGAR NOTICIAS AL INICIAR ================= */
  document.addEventListener('DOMContentLoaded', loadNoticias);

</script>

</body></html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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