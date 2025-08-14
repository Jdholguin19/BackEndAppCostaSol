<?php
require_once __DIR__ . '/../config/db.php';
$db = DB::getDB();

// Validación de autenticación y rol
session_start();

// Obtener token desde POST (enviado por JavaScript)
$token = $_POST['token'] ?? null;

// Debug temporal
error_log("Token recibido: " . ($token ? $token : 'NULL'));
error_log("POST data: " . print_r($_POST, true));

if (!$token) {
    // Si no hay token, mostrar página de acceso denegado
    $showAccessDenied = true;
    error_log("No hay token - acceso denegado");
} else {
    // Verificar si el usuario es responsable en la tabla responsable
    try {
        $stmt = $db->prepare('SELECT id, nombre, correo FROM responsable WHERE token = ? AND estado = 1');
        $stmt->execute([$token]);
        $responsable = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("Consulta responsable - Resultado: " . ($responsable ? 'ENCONTRADO' : 'NO ENCONTRADO'));
        if ($responsable) {
            error_log("Responsable encontrado - ID: " . $responsable['id'] . ", Nombre: " . $responsable['nombre']);
        }
        
        if (!$responsable) {
            $showAccessDenied = true;
            error_log("Responsable no encontrado - acceso denegado");
        } else {
            $showAccessDenied = false;
            error_log("Responsable válido - acceso permitido");
            
            /* --------- 1. Lista de roles para el <select> --------- */
            $roles = $db->query('SELECT id, nombre FROM rol ORDER BY nombre')->fetchAll(PDO::FETCH_KEY_PAIR);

            /* --------- 2. Usuarios (incluye rol_id para edición) --------- */
            $usuarios = $db->query(
              'SELECT u.id, u.url_foto_perfil, u.nombres, u.apellidos, u.correo,
                      u.rol_id, r.nombre AS rol
                 FROM usuario u
                 JOIN rol r ON r.id = u.rol_id
                 ORDER BY u.id DESC'
            )->fetchAll();
        }
    } catch (Exception $e) {
        $showAccessDenied = true;
        error_log("Error en consulta responsable: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Usuarios</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/style_users.css" rel="stylesheet">
</head>
<body>

<?php if ($showAccessDenied): ?>
<!-- Access Denied Section -->
<div class="access-denied">
  <i class="bi bi-shield-exclamation"></i>
  <h2>Acceso Denegado</h2>
  <p>No tienes permisos para acceder a esta página.<br>Solo los responsables pueden gestionar usuarios.</p>
  <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 1rem;">
    Debug: Token = <?= $token ? 'Presente' : 'Ausente' ?>
  </p>
  <button class="btn btn-primary mt-3" onclick="history.back()">
    <i class="bi bi-arrow-left"></i> <br> Volver
  </button>
</div>

<?php else: ?>
<!-- Header Section -->
<div class="users-header">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <div>
      <h2 class="users-title">Gestión de Usuarios</h2>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="users-container">
    
    <!-- Action Bar -->
    <div class="action-bar">
      <h3 style="margin: 0; font-family: 'Inter', sans-serif; font-weight: 600; color: #2d5a3d;">
        Lista de Usuarios
      </h3>
      <button class="add-user-btn" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="nuevoUsuario()">
        <i class="bi bi-plus"></i> Nuevo Usuario
      </button>
    </div>

    <!-- Users Table -->
    <div class="users-table">
      <div class="scroll-indicator">
        <i class="bi bi-arrow-left-right"></i>
        <span>Desliza horizontalmente para ver más columnas</span>
      </div>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Foto</th>
            <th>Nombres</th>
            <th>Apellidos</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td><?= $u['id'] ?></td>
              <td><img src="<?= htmlspecialchars($u['url_foto_perfil'] ?? 'https://via.placeholder.com/30') ?>" alt="Foto" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;"></td>
              <td><?= htmlspecialchars($u['nombres']) ?></td>
              <td><?= htmlspecialchars($u['apellidos']) ?></td>
              <td><?= htmlspecialchars($u['correo']) ?></td>
              <td><?= htmlspecialchars($u['rol']) ?></td>
              <td class="action-buttons">
                <button class="edit-btn" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick='editarUsuario(<?= json_encode($u) ?>)'>
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="delete-btn" onclick="borrarUsuario(<?= $u['id'] ?>)">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal alta/edición -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="formUsuario">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nuevo usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="id">
        <div class="col-6">
          <input required class="form-control" name="nombres" id="nombres" placeholder="Nombres">
        </div>
        <div class="col-6">
          <input required class="form-control" name="apellidos" id="apellidos" placeholder="Apellidos">
        </div>
        <div class="col-8">
          <input required type="email" class="form-control" name="correo" id="correo" placeholder="Correo">
        </div>
        <div class="col-4">
          <select class="form-select" name="rol_id" id="rol_id" required>
            <?php foreach ($roles as $id => $nombre): ?>
              <option value="<?= $id ?>"><?= htmlspecialchars($nombre) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <input type="password" class="form-control" name="contrasena" id="contrasena" placeholder="Contraseña (solo al crear / cambiar)">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary">Guardar</button>
      </div>
    </form>
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

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
console.log('Script cargado - Iniciando verificación de autenticación');

// Verificar autenticación inmediatamente
const token = localStorage.getItem('cs_token');
console.log('Token encontrado:', token ? 'SÍ' : 'NO');
console.log('Token completo:', token);
console.log('localStorage completo:', localStorage);

if (!token) {
    // Si no hay token, redirigir al login
    console.log('No hay token, redirigiendo al login');
    window.location.href = 'login_front.php';
} else {
    console.log('Enviando token al servidor...');
    console.log('URL actual:', window.location.href);
    
    // Enviar token al servidor para validación
    const formData = new FormData();
    formData.append('token', token);
    
    // Debug: verificar que FormData tiene los datos
    for (let [key, value] of formData.entries()) {
        console.log('FormData -', key + ':', value);
    }
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Respuesta del servidor recibida');
        console.log('Status:', response.status);
        console.log('Headers:', response.headers);
        return response.text();
    })
    .then(html => {
        console.log('Reemplazando contenido del body');
        console.log('HTML recibido (primeros 200 chars):', html.substring(0, 200));
        // Reemplazar todo el contenido del body
        document.body.innerHTML = html;
        
        // Reinicializar Bootstrap después de reemplazar el contenido
        if (typeof bootstrap !== 'undefined') {
            // Reinicializar tooltips, modales, etc.
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
        // Inicializar funcionalidades después de cargar el contenido
        initializeUserFunctions();
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        alert('Error de autenticación');
    });
}

// Función para inicializar las funcionalidades del usuario
function initializeUserFunctions() {
    const form = document.getElementById('formUsuario');
    
    if (form) {
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(form).entries());
            const metodo = data.id ? 'PUT' : 'POST';
            
            try {
                const response = await fetch('../api/user_crud.php', {
                    method: metodo,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                
                if (!result.ok) {
                    alert(result.mensaje);
                    return;
                }
                
                location.reload();
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            }
        });
    }
}

function nuevoUsuario() {
    const form = document.getElementById('formUsuario');
    if (form) {
        form.reset();
        document.getElementById('id').value = '';
        document.getElementById('modalTitle').textContent = 'Nuevo usuario';
    }
}

function editarUsuario(u) {
    document.getElementById('modalTitle').textContent = 'Editar usuario';
    for (const k in u) {
        if (document.getElementById(k)) {
            document.getElementById(k).value = u[k];
        }
    }
    document.getElementById('rol_id').value = u.rol_id;
}

async function borrarUsuario(id) {
    if (!confirm('¿Estás seguro de que quieres borrar este usuario?')) {
        return;
    }
    
    try {
        const response = await fetch('user_crud.php?id=' + id, { 
            method: 'DELETE' 
        });
        const result = await response.json();
        
        if (!result.ok) {
            alert(result.mensaje);
            return;
        }
        
        location.reload();
    } catch (error) {
        console.error('Error:', error);
        alert('Error al borrar el usuario');
    }
}
</script>

</body>
</html>
