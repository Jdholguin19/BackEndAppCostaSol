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
      <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
        <h3 style="margin: 0; font-family: 'Inter', sans-serif; font-weight: 600; color: #2d5a3d;">
          Lista de Usuarios
        </h3>
        <button 
          onclick="verReporteGeneral()" 
          class="btn btn-success"
          style="
            background: #2d5a3d;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
          "
          onmouseover="this.style.background='#4a7c59'"
          onmouseout="this.style.background='#2d5a3d'"
        >
          <i class="bi bi-graph-up-arrow"></i>
          Reporte General
        </button>
        <div class="filter-container" style="position: relative; display: inline-block;">
          <input 
            type="text" 
            id="userFilter" 
            placeholder="Filtrar por nombre, apellido o correo..."
            style="
              padding: 8px 12px;
              border: 2px solid #e5e7eb;
              border-radius: 8px;
              font-size: 14px;
              width: 300px;
              transition: border-color 0.2s;
            "
            onfocus="this.style.borderColor='#2d5a3d'"
            onblur="this.style.borderColor='#e5e7eb'"
          >
          <div id="filterDropdown" class="filter-dropdown" style="
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
          ">
          </div>
        </div>
      </div>
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
                <button class="edit-btn" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick='editarUsuario(<?= json_encode($u) ?>)' title="Editar Usuario">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="report-btn" onclick="verReporteUsuario(<?= $u['id'] ?>)" title="Ver Reporte">
                  <i class="bi bi-graph-up"></i>
                </button>
                <button class="delete-btn" onclick="borrarUsuario(<?= $u['id'] ?>)" title="Eliminar Usuario">
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

<?php 
$active_page = 'inicio';
include '../api/bottom_nav.php'; 
?>

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
    
    // Inicializar el filtro inmediatamente si ya hay contenido
    setTimeout(() => {
        initializeUserFilter();
    }, 100);
    
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
        initializeUserFilter();
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
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token // Añadir el token de autorización
                    },
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
        const response = await fetch('../api/user_crud.php?id=' + id, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token // Añadir el token de autorización
            }
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

// Variables globales para el filtro
let allUsers = [];
let filteredUsers = [];

// Función para inicializar el filtro de usuarios
function initializeUserFilter() {
    const filterInput = document.getElementById('userFilter');
    const dropdown = document.getElementById('filterDropdown');
    
    if (!filterInput || !dropdown) return;
    
    // Obtener todos los usuarios de la tabla
    allUsers = Array.from(document.querySelectorAll('#tbody tr')).map(row => {
        const cells = row.querySelectorAll('td');
        return {
            id: cells[0].textContent.trim(),
            nombres: cells[2].textContent.trim(),
            apellidos: cells[3].textContent.trim(),
            correo: cells[4].textContent.trim(),
            rol: cells[5].textContent.trim(),
            element: row
        };
    });
    
    // Event listener para el filtro
    filterInput.addEventListener('input', handleFilterInput);
    filterInput.addEventListener('focus', showDropdown);
    
    // Ocultar dropdown al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.filter-container')) {
            hideDropdown();
        }
    });
}

// Función para manejar la entrada del filtro
function handleFilterInput(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    
    if (searchTerm.length === 0) {
        hideDropdown();
        showAllUsers();
        return;
    }
    
    // Filtrar usuarios que coincidan con el término de búsqueda
    filteredUsers = allUsers.filter(user => {
        const nombres = user.nombres.toLowerCase();
        const apellidos = user.apellidos.toLowerCase();
        const correo = user.correo.toLowerCase();
        const searchLower = searchTerm.toLowerCase();
        
        return nombres.includes(searchLower) || 
               apellidos.includes(searchLower) || 
               correo.includes(searchLower);
    });
    
    // Mostrar resultados en el dropdown
    showFilterResults(filteredUsers);
    
    // Mostrar solo usuarios filtrados en la tabla
    showFilteredUsers(filteredUsers);
}

// Función para mostrar resultados en el dropdown
function showFilterResults(users) {
    const dropdown = document.getElementById('filterDropdown');
    
    if (users.length === 0) {
        dropdown.innerHTML = '<div style="padding: 12px; color: #6b7280; text-align: center;">No se encontraron usuarios</div>';
    } else {
        dropdown.innerHTML = users.map(user => `
            <div class="filter-item" style="
                padding: 10px 12px;
                cursor: pointer;
                border-bottom: 1px solid #f3f4f6;
                transition: background-color 0.2s;
            " 
            onmouseover="this.style.backgroundColor='#f9fafb'"
            onmouseout="this.style.backgroundColor='white'"
            onclick="selectUser('${user.id}')">
                <div style="font-weight: 500; color: #1f2937;">${user.nombres} ${user.apellidos}</div>
                <div style="font-size: 12px; color: #6b7280;">${user.correo}</div>
            </div>
        `).join('');
    }
    
    dropdown.style.display = 'block';
}

// Función para seleccionar un usuario del dropdown
function selectUser(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (user) {
        // Hacer scroll hacia el usuario seleccionado
        user.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Resaltar temporalmente la fila
        user.element.style.backgroundColor = '#fef3c7';
        setTimeout(() => {
            user.element.style.backgroundColor = '';
        }, 2000);
    }
    
    hideDropdown();
    document.getElementById('userFilter').value = '';
    showAllUsers();
}

// Función para mostrar solo usuarios filtrados en la tabla
function showFilteredUsers(users) {
    allUsers.forEach(user => {
        user.element.style.display = 'none';
    });
    
    users.forEach(user => {
        user.element.style.display = '';
    });
}

// Función para mostrar todos los usuarios
function showAllUsers() {
    allUsers.forEach(user => {
        user.element.style.display = '';
    });
}

// Función para mostrar el dropdown
function showDropdown() {
    const dropdown = document.getElementById('filterDropdown');
    if (dropdown) {
        dropdown.style.display = 'block';
    }
}

// Función para ocultar el dropdown
function hideDropdown() {
    const dropdown = document.getElementById('filterDropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
    }
}

// Función para ver reporte de usuario
function verReporteUsuario(userId) {
    window.open(`reportes_usuario.php?user_id=${userId}`, '_blank');
}

// Función para ver reporte general
function verReporteGeneral() {
    window.open(`reportes_general_usuario.php`, '_blank');
}
</script>

</body>
</html>
