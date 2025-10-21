<?php /* Front/admin/admin_garantias.php */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administrar Garantías | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../assets/css/style_main2.css" rel="stylesheet">
<link href="../assets/css/style_garantia.css" rel="stylesheet">

<style>
.admin-container {
    padding: 1rem 1.5rem;
    padding-bottom: 5rem;
}

@media (max-width: 768px) {
    .admin-container {
        padding: 0.75rem 1rem;
    }
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem 0;
    border-bottom: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
}

.admin-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #2d5a3d;
    margin: 0;
}

@media (max-width: 768px) {
    .admin-title {
        font-size: 1.5rem;
    }
}

.btn-add {
    background: #2d5a3d;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: background-color 0.2s ease;
}

.btn-add:hover {
    background: #1f4229;
    color: white;
}

@media (max-width: 768px) {
    .btn-add {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
    }
}

.garantias-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-header {
    background: #f8fafc;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

@media (max-width: 768px) {
    .table-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
}

.table-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.filter-section {
    display: flex;
    gap: 1rem;
    align-items: center;
}

@media (max-width: 768px) {
    .filter-section {
        flex-direction: column;
        align-items: stretch;
    }
}

.filter-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    min-width: 200px;
}

@media (max-width: 768px) {
    .filter-select {
        min-width: auto;
    }
}

.garantias-table table {
    width: 100%;
    border-collapse: collapse;
}

.garantias-table th,
.garantias-table td {
    padding: 1rem 1.5rem;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
}

@media (max-width: 768px) {
    .garantias-table th,
    .garantias-table td {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }

    .garantias-table {
        overflow-x: auto;
    }

    .garantias-table table {
        min-width: 800px;
    }
}

.garantias-table th {
    background: #f8fafc;
    font-weight: 600;
    color: #475569;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.garantias-table td {
    color: #1f2937;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #dc2626;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
    }

    .btn-action {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
    }
}

.btn-action {
    padding: 0.375rem 0.75rem;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-edit {
    background: #3b82f6;
    color: white;
}

.btn-edit:hover {
    background: #2563eb;
}

.btn-delete {
    background: #dc2626;
    color: white;
}

.btn-delete:hover {
    background: #b91c1c;
}

.modal-content {
    border-radius: 12px;
    border: none;
}

@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
    }

    .modal-content {
        border-radius: 8px;
    }
}

.modal-header {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    border-radius: 12px 12px 0 0;
}

@media (max-width: 576px) {
    .modal-header {
        border-radius: 8px 8px 0 0;
        padding: 1rem;
    }
}

.modal-title {
    font-weight: 600;
    color: #1f2937;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    display: block;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.875rem;
}

.form-control:focus {
    outline: none;
    border-color: #2d5a3d;
    box-shadow: 0 0 0 3px rgba(45, 90, 61, 0.1);
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-group input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.loading {
    text-align: center;
    padding: 2rem;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>
</head>
<body>

<!-- Header Section -->
<div class="header-section">
  <div class="welcome-card">
    <div class="welcome-info">
      <p class="welcome-name" id="welcomeName">Hola, Administrador</p>
    </div>
    <div class="avatar-container">
        <img src="" class="avatar" id="welcomeAvatar" alt="Avatar">
        <button id="logoutButton" class="logout-button" style="display: none;">Cerrar sesión</button>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="admin-container">
    <div class="admin-header">
      <div>
        <button class="back-button" onclick="location.href='../garantias.php'">
          <i class="bi bi-arrow-left"></i>
        </button>
        <h1 class="admin-title">Administrar Garantías</h1>
      </div>
      <button class="btn-add" onclick="openCreateModal()">
        <i class="bi bi-plus-circle"></i>
        Nueva Garantía
      </button>
    </div>

    <div class="garantias-table">
      <div class="table-header">
        <h2 class="table-title">Lista de Garantías</h2>
        <div class="filter-section">
          <select class="filter-select" id="tipoPropiedadFilter" onchange="filterGarantias()">
            <option value="">Todos los tipos</option>
          </select>
        </div>
      </div>

      <div id="garantiasTableContainer">
        <div class="loading">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Crear/Editar Garantía -->
<div class="modal fade" id="garantiaModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nueva Garantía</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="garantiaForm">
          <input type="hidden" id="garantiaId" name="id">

          <div class="form-group">
            <label for="nombre" class="form-label">Nombre *</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
          </div>

          <div class="form-group">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
          </div>

          <div class="form-group">
            <label for="tiempoGarantiaMeses" class="form-label">Tiempo de Garantía (meses) *</label>
            <input type="number" class="form-control" id="tiempoGarantiaMeses" name="tiempo_garantia_meses" min="1">
          </div>

          <div class="form-group">
            <div class="checkbox-group">
              <input type="checkbox" id="validaHastaEntrega" name="valida_hasta_entrega" onchange="toggleTiempoGarantia()">
              <label for="validaHastaEntrega" class="form-label">Válida hasta la entrega</label>
            </div>
            <small class="text-muted">Si está marcado, esta garantía será válida hasta la fecha de entrega de la propiedad (no requiere duración en meses)</small>
          </div>

          <div class="form-group">
            <label for="tipoPropiedadId" class="form-label">Tipo de Propiedad</label>
            <select class="form-control" id="tipoPropiedadId" name="tipo_propiedad_id">
              <option value="">Todos los tipos</option>
            </select>
          </div>

          <div class="form-group">
            <label for="orden" class="form-label">Orden</label>
            <input type="number" class="form-control" id="orden" name="orden" min="0">
          </div>

          <div class="form-group">
            <div class="checkbox-group">
              <input type="checkbox" id="estado" name="estado" checked>
              <label for="estado" class="form-label">Activo</label>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="saveGarantia()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de que desea eliminar esta garantía?</p>
        <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" onclick="confirmDelete()">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<?php
$active_page = 'inicio';
include '../../api/bottom_nav.php';
?>

<script>
// Verificar autenticación
const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
if (!u.id || !u.is_responsable) location.href = '../login_front.php';

// Variables globales
let currentGarantiaId = null;
let tipoPropiedadOptions = [];
let allGarantias = []; // Almacenar todas las garantías para filtrado

// APIs
const API_GARANTIAS = '../../api/admin_garantias.php';
const API_TIPO_PROPIEDAD = '../../api/admin_garantias.php?action=get_tipo_propiedad';

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    loadGarantias();
    loadTipoPropiedad();
    setupUserInfo();
});

// Configurar información del usuario
function setupUserInfo() {
    document.getElementById('welcomeName').textContent = `Hola, ${u.nombres || u.nombre}`;
    document.getElementById('welcomeAvatar').src = u.url_foto_perfil || 'https://via.placeholder.com/48';
}

// Cargar garantías
async function loadGarantias() {
    try {
        const response = await fetch(API_GARANTIAS, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('cs_token')}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (!data.ok) {
            throw new Error(data.error || 'Error al cargar garantías');
        }

        allGarantias = data.garantias;
        renderGarantiasTable(allGarantias);
    } catch (error) {
        console.error('Error:', error);
        showError('Error al cargar garantías: ' + error.message);
    }
}

// Cargar tipos de propiedad
async function loadTipoPropiedad() {
    try {
        const response = await fetch(API_TIPO_PROPIEDAD, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('cs_token')}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.ok) {
            tipoPropiedadOptions = data.tipos;
            populateTipoPropiedadSelect();
        }
    } catch (error) {
        console.error('Error cargando tipos de propiedad:', error);
    }
}

// Poblar select de tipos de propiedad
function populateTipoPropiedadSelect() {
    const filterSelect = document.getElementById('tipoPropiedadFilter');
    const modalSelect = document.getElementById('tipoPropiedadId');

    // Limpiar opciones existentes
    filterSelect.innerHTML = '<option value="">Todos los tipos</option>';
    modalSelect.innerHTML = '<option value="">Todos los tipos</option>';

    tipoPropiedadOptions.forEach(tipo => {
        // Para el filtro
        const filterOption = document.createElement('option');
        filterOption.value = tipo.id;
        filterOption.textContent = tipo.nombre;
        filterSelect.appendChild(filterOption);

        // Para el modal
        const modalOption = document.createElement('option');
        modalOption.value = tipo.id;
        modalOption.textContent = tipo.nombre;
        modalSelect.appendChild(modalOption);
    });
}

// Filtrar garantías
function filterGarantias() {
    const tipoFilter = document.getElementById('tipoPropiedadFilter').value;

    let filteredGarantias = allGarantias;

    if (tipoFilter) {
        filteredGarantias = allGarantias.filter(garantia => {
            return !garantia.tipo_propiedad_id || garantia.tipo_propiedad_id == tipoFilter;
        });
    }

    renderGarantiasTable(filteredGarantias);
}

// Renderizar tabla de garantías
function renderGarantiasTable(garantias) {
    const container = document.getElementById('garantiasTableContainer');

    if (garantias.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-info-circle"></i>
                <p>No hay garantías registradas</p>
            </div>
        `;
        return;
    }

    const tableHtml = `
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Tipo Garantía</th>
                    <th>Tipo Propiedad</th>
                    <th>Estado</th>
                    <th>Orden</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                ${garantias.map(garantia => `
                    <tr>
                        <td>${garantia.nombre}</td>
                        <td>${garantia.descripcion || '-'}</td>
                        <td>
                            <span class="status-badge ${garantia.valida_hasta_entrega ? 'status-active' : 'status-inactive'}">
                                ${garantia.valida_hasta_entrega ? 'Hasta entrega' : garantia.tiempo_garantia_meses + ' meses'}
                            </span>
                        </td>
                        <td>${garantia.tipo_propiedad_nombre || 'Todos'}</td>
                        <td>
                            <span class="status-badge ${garantia.estado ? 'status-active' : 'status-inactive'}">
                                ${garantia.estado ? 'Activo' : 'Inactivo'}
                            </span>
                        </td>
                        <td>${garantia.orden}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" onclick="editGarantia(${garantia.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-action btn-delete" onclick="deleteGarantia(${garantia.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;

    container.innerHTML = tableHtml;
}

// Abrir modal para crear
function openCreateModal() {
    currentGarantiaId = null;
    document.getElementById('modalTitle').textContent = 'Nueva Garantía';
    document.getElementById('garantiaForm').reset();
    document.getElementById('estado').checked = true;
    document.getElementById('validaHastaEntrega').checked = false;
    toggleTiempoGarantia();

    // Establecer orden automáticamente (máximo + 1)
    const maxOrden = allGarantias.length > 0 ? Math.max(...allGarantias.map(g => g.orden || 0)) : 0;
    document.getElementById('orden').value = maxOrden + 1;

    const modal = new bootstrap.Modal(document.getElementById('garantiaModal'));
    modal.show();
}

// Toggle para habilitar/deshabilitar tiempo de garantía
function toggleTiempoGarantia() {
    const checkbox = document.getElementById('validaHastaEntrega');
    const tiempoInput = document.getElementById('tiempoGarantiaMeses');
    
    if (checkbox.checked) {
        tiempoInput.disabled = true;
        tiempoInput.value = '';
    } else {
        tiempoInput.disabled = false;
    }
}

// Editar garantía
function editGarantia(id) {
    currentGarantiaId = id;

    // Buscar la garantía en el array allGarantias
    const garantia = allGarantias.find(g => g.id === id);

    if (garantia) {
        document.getElementById('modalTitle').textContent = 'Editar Garantía';
        document.getElementById('garantiaId').value = id;
        document.getElementById('nombre').value = garantia.nombre;
        document.getElementById('descripcion').value = garantia.descripcion || '';
        document.getElementById('tiempoGarantiaMeses').value = garantia.tiempo_garantia_meses || '';
        document.getElementById('validaHastaEntrega').checked = garantia.valida_hasta_entrega ? true : false;
        document.getElementById('orden').value = garantia.orden || '';
        document.getElementById('estado').checked = garantia.estado ? true : false;
        
        toggleTiempoGarantia();

        // Seleccionar tipo de propiedad
        const tipoSelect = document.getElementById('tipoPropiedadId');
        tipoSelect.value = garantia.tipo_propiedad_id || '';

        const modal = new bootstrap.Modal(document.getElementById('garantiaModal'));
        modal.show();
    }
}

// Guardar garantía
async function saveGarantia() {
    const form = document.getElementById('garantiaForm');
    const formData = new FormData(form);

    // Validar que o bien tiempo_garantia_meses o valida_hasta_entrega estén definidos
    const tiempoMeses = formData.get('tiempo_garantia_meses');
    const validaHastaEntrega = formData.get('valida_hasta_entrega') ? '1' : '0';

    if (!validaHastaEntrega && !tiempoMeses) {
        showError('Debe especificar una duración o marcar "Válida hasta la entrega"');
        return;
    }

    // Convertir checkboxes a valores numéricos
    formData.set('estado', formData.get('estado') ? '1' : '0');
    formData.set('valida_hasta_entrega', validaHastaEntrega);

    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch(API_GARANTIAS, {
            method: currentGarantiaId ? 'PUT' : 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('cs_token')}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.ok) {
            bootstrap.Modal.getInstance(document.getElementById('garantiaModal')).hide();
            loadGarantias();
            showSuccess(result.mensaje || 'Garantía guardada correctamente');
        } else {
            throw new Error(result.error || 'Error al guardar');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error al guardar garantía: ' + error.message);
    }
}

// Eliminar garantía
function deleteGarantia(id) {
    currentGarantiaId = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Confirmar eliminación
async function confirmDelete() {
    try {
        const response = await fetch(`${API_GARANTIAS}?id=${currentGarantiaId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('cs_token')}`
            }
        });

        const result = await response.json();

        if (result.ok) {
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            loadGarantias();
            showSuccess('Garantía eliminada correctamente');
        } else {
            throw new Error(result.error || 'Error al eliminar');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error al eliminar garantía: ' + error.message);
    }
}

// Funciones de utilidad
function showSuccess(message) {
    // Implementar notificación de éxito
    alert(message);
}

function showError(message) {
    // Implementar notificación de error
    alert(message);
}

// Logout
async function logout() {
    const token = localStorage.getItem('cs_token');
    if (!token) {
        window.location.href = '../login_front.php';
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
        window.location.href = '../login_front.php';
    }
}

// Event listeners
document.getElementById('welcomeAvatar').addEventListener('click', function() {
    const logoutButton = document.getElementById('logoutButton');
    logoutButton.style.display = logoutButton.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('logoutButton').addEventListener('click', logout);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>