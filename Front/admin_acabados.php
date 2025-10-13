<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administración de Acabados | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">
<link href="assets/css/style_acabados.css" rel="stylesheet">
<style>
    .admin-header {
        background: linear-gradient(135deg, #2d5a3d 0%, #3a7d4f 100%);
        color: white;
        padding: 20px 0;
        text-align: center;
        margin-bottom: 30px;
        border-radius: 0 0 20px 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .admin-header h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 600;
    }
    .admin-nav {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 0;
        border-radius: 15px 15px 0 0;
        margin-bottom: 20px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
    .admin-nav .nav-link {
        color: #2d5a3d;
        font-weight: 500;
        border-radius: 10px 10px 0 0;
        margin: 0 2px;
        transition: all 0.3s ease;
    }
    .admin-nav .nav-link.active {
        background-color: #2d5a3d;
        color: white;
        box-shadow: 0 2px 10px rgba(45, 90, 61, 0.3);
    }
    .admin-nav .nav-link:hover {
        background-color: rgba(45, 90, 61, 0.1);
    }
    .card {
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        border-radius: 15px;
        overflow: hidden;
    }
    .card-header {
        background-color: #2d5a3d;
        color: white;
        font-weight: 600;
        border-radius: 15px 15px 0 0 !important;
        padding: 15px 20px;
    }
    .btn-primary {
        background-color: #2d5a3d;
        border-color: #2d5a3d;
        border-radius: 10px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(45, 90, 61, 0.2);
    }
    .btn-primary:hover {
        background-color: #1e3a2a;
        border-color: #1e3a2a;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(45, 90, 61, 0.3);
    }
    .btn-secondary {
        border-radius: 10px;
        padding: 8px 20px;
        font-weight: 500;
    }
    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        color: #2d5a3d;
        padding: 12px;
    }
    .table td {
        padding: 12px;
        vertical-align: middle;
    }
    .modal-content {
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        border: none;
    }
    .modal-header {
        border-radius: 20px 20px 0 0;
        background: linear-gradient(135deg, #2d5a3d 0%, #3a7d4f 100%);
        color: white;
        border-bottom: none;
        padding: 20px;
    }
    .modal-body {
        padding: 25px;
    }
    .modal-footer {
        border-top: none;
        padding: 20px 25px;
        border-radius: 0 0 20px 20px;
    }
    .form-control {
        border-radius: 10px;
        border: 1px solid #dee2e6;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #2d5a3d;
        box-shadow: 0 0 0 0.2rem rgba(45, 90, 61, 0.15);
    }
    .form-select {
        border-radius: 10px;
    }
    .image-preview {
        max-width: 80px;
        max-height: 80px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .image-upload {
        display: none;
    }
    .image-upload-label {
        cursor: pointer;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px dashed #dee2e6;
        padding: 30px;
        text-align: center;
        border-radius: 15px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .image-upload-label:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        border-color: #2d5a3d;
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .btn-sm {
        border-radius: 8px;
        padding: 4px 10px;
    }
    .badge {
        border-radius: 20px;
        padding: 6px 12px;
        font-weight: 500;
    }
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }
    @media (max-width: 768px) {
        .admin-header h1 {
            font-size: 1.5rem;
        }
        .admin-nav .nav-link {
            font-size: 0.9rem;
            padding: 8px 12px;
        }
        .card-header {
            padding: 12px 15px;
        }
        .table-responsive {
            font-size: 0.9rem;
        }
        .btn {
            font-size: 0.9rem;
            padding: 6px 15px;
        }
        .modal-dialog {
            margin: 10px;
        }
        .modal-body {
            padding: 20px;
        }
    }
    @media (min-width: 768px) {
        body {
            padding-bottom: 80px;
        }
    }
</style>
</head>
<body>

<!-- Header -->
<div class="admin-header">
    <div class="container">
        <h1><i class="bi bi-gear-fill me-2"></i>Administración de Acabados</h1>
    </div>
</div>

<!-- Navigation -->
<div class="admin-nav">
    <div class="container">
        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="kits-tab" data-bs-toggle="tab" data-bs-target="#kits" type="button" role="tab">Kits de Acabados</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="packages-tab" data-bs-toggle="tab" data-bs-target="#packages" type="button" role="tab">Paquetes Adicionales</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="componentes-tab" data-bs-toggle="tab" data-bs-target="#componentes" type="button" role="tab">Componentes</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="color-options-tab" data-bs-toggle="tab" data-bs-target="#color-options" type="button" role="tab">Opciones de Color</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="acabado-details-tab" data-bs-toggle="tab" data-bs-target="#acabado-details" type="button" role="tab">Acabado Detalles</button>
            </li>
        </ul>
    </div>
</div>

<!-- Main Content -->
<div class="container mt-4">
    <div class="tab-content" id="adminTabContent">
        <!-- Kits Tab -->
        <div class="tab-pane fade show active" id="kits" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Kits de Acabados</h3>
                <button class="btn btn-primary" onclick="resetKitForm()" data-bs-toggle="modal" data-bs-target="#addKitModal">
                    <i class="bi bi-plus-circle"></i> Añadir Kit
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="kitsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Imagen Principal</th>
                                    <th>Costo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="kitsTableBody">
                                <!-- Kits will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Packages Tab -->
        <div class="tab-pane fade" id="packages" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Paquetes Adicionales</h3>
                <button class="btn btn-primary" onclick="resetPackageForm()" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                    <i class="bi bi-plus-circle"></i> Añadir Paquete
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="packagesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Fotos</th>
                                    <th>Activo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="packagesTableBody">
                                <!-- Packages will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Componentes Tab -->
        <div class="tab-pane fade" id="componentes" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Componentes</h3>
                <button class="btn btn-primary" onclick="resetComponenteForm()" data-bs-toggle="modal" data-bs-target="#addComponenteModal">
                    <i class="bi bi-plus-circle"></i> Añadir Componente
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="componentesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="componentesTableBody">
                                <!-- Componentes will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Color Options Tab -->
        <div class="tab-pane fade" id="color-options" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Opciones de Color</h3>
                <button class="btn btn-primary" onclick="resetColorOptionForm()" data-bs-toggle="modal" data-bs-target="#addColorOptionModal">
                    <i class="bi bi-plus-circle"></i> Añadir Opción de Color
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="colorOptionsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kit</th>
                                    <th>Nombre Opción</th>
                                    <th>Color</th>
                                    <th>Imagen</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="colorOptionsTableBody">
                                <!-- Color Options will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acabado Details Tab -->
        <div class="tab-pane fade" id="acabado-details" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Acabado Detalles</h3>
                <button class="btn btn-primary" onclick="resetAcabadoDetailForm()" data-bs-toggle="modal" data-bs-target="#addAcabadoDetailModal">
                    <i class="bi bi-plus-circle"></i> Añadir Detalle
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="acabadoDetailsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kit</th>
                                    <th>Componente</th>
                                    <th>Color</th>
                                    <th>Imagen</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="acabadoDetailsTableBody">
                                <!-- Acabado Details will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Kit Modal -->
<div class="modal fade" id="addKitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kitModalTitle">Añadir Kit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="kitForm">
                    <input type="hidden" id="kitId" name="kitId">
                    <div class="mb-3">
                        <label for="kitNombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="kitNombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="kitDescripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="kitDescripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="kitCosto" class="form-label">Costo Adicional</label>
                        <input type="number" class="form-control" id="kitCosto" name="costo" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagen Principal</label>
                        <input type="file" class="form-control image-upload" id="kitImagen" name="imagen" accept="image/*">
                        <label for="kitImagen" class="image-upload-label">
                            <i class="bi bi-cloud-upload"></i><br>
                            Haz clic para subir imagen
                        </label>
                        <div id="kitImagePreview" class="mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveKitBtn">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Package Modal -->
<div class="modal fade" id="addPackageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="packageModalTitle">Añadir Paquete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="packageForm">
                    <input type="hidden" id="packageId" name="packageId">
                    <div class="mb-3">
                        <label for="packageNombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="packageNombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="packageDescripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="packageDescripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="packagePrecio" class="form-label">Precio</label>
                        <input type="number" class="form-control" id="packagePrecio" name="precio" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fotos</label>
                        <input type="file" class="form-control image-upload" id="packageFotos" name="fotos" accept="image/*" multiple>
                        <label for="packageFotos" class="image-upload-label">
                            <i class="bi bi-cloud-upload"></i><br>
                            Haz clic para subir imágenes (múltiples)
                        </label>
                        <div id="packageImagesPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="packageActivo" name="activo" checked>
                        <label class="form-check-label" for="packageActivo">Activo</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="savePackageBtn">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Acabado Detail Modal -->
<div class="modal fade" id="addAcabadoDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="acabadoDetailModalTitle">Añadir Detalle de Acabado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="acabadoDetailForm">
                    <input type="hidden" id="acabadoDetailId" name="detailId">
                    <div class="mb-3">
                        <label for="acabadoDetailKit" class="form-label">Kit</label>
                        <select class="form-control" id="acabadoDetailKit" name="kitId" required>
                            <option value="">Seleccionar kit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="acabadoDetailComponente" class="form-label">Componente</label>
                        <select class="form-control" id="acabadoDetailComponente" name="componenteId" required>
                            <option value="">Seleccionar componente</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="acabadoDetailColor" class="form-label">Color</label>
                        <select class="form-control" id="acabadoDetailColor" name="color" required>
                            <option value="">Seleccionar color</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="acabadoDetailDescripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="acabadoDetailDescripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagen</label>
                        <input type="file" class="form-control image-upload" id="acabadoDetailImagen" name="imagen" accept="image/*">
                        <label for="acabadoDetailImagen" class="image-upload-label">
                            <i class="bi bi-cloud-upload"></i><br>
                            Haz clic para subir imagen
                        </label>
                        <div id="acabadoDetailImagePreview" class="mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveAcabadoDetailBtn">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Componente Modal -->
<div class="modal fade" id="addComponenteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="componenteModalTitle">Añadir Componente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="componenteForm">
                    <input type="hidden" id="componenteId" name="componenteId">
                    <div class="mb-3">
                        <label for="componenteNombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="componenteNombre" name="nombre" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveComponenteBtn">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Color Option Modal -->
<div class="modal fade" id="addColorOptionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="colorOptionModalTitle">Añadir Opción de Color</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="colorOptionForm">
                    <input type="hidden" id="colorOptionId" name="optionId">
                    <div class="mb-3">
                        <label for="colorOptionKit" class="form-label">Kit</label>
                        <select class="form-control" id="colorOptionKit" name="kitId" required>
                            <option value="">Seleccionar kit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="colorOptionNombre" class="form-label">Nombre de Opción</label>
                        <input type="text" class="form-control" id="colorOptionNombre" name="nombreOpcion" required>
                    </div>
                    <div class="mb-3">
                        <label for="colorOptionColor" class="form-label">Nombre del Color</label>
                        <input type="text" class="form-control" id="colorOptionColor" name="colorNombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagen de Opción</label>
                        <input type="file" class="form-control image-upload" id="colorOptionImagen" name="imagen" accept="image/*">
                        <label for="colorOptionImagen" class="image-upload-label">
                            <i class="bi bi-cloud-upload"></i><br>
                            Haz clic para subir imagen
                        </label>
                        <div id="colorOptionImagePreview" class="mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveColorOptionBtn">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php 
$active_page = 'inicio';
include '../api/bottom_nav.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="//code.jquery.com/jquery-latest.js"></script>
<script>
// Define global functions first to avoid reference errors
let currentKitId = null;
let currentColor = null;
const user = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
const token = localStorage.getItem('cs_token');

window.editKit = (id) => {
    if (!token) return;
    fetch(`/api/admin_acabados.php?action=get_kit&id=${id}`, {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            const kit = data.kit;
            const kitIdEl = document.getElementById('kitId');
            const kitNombreEl = document.getElementById('kitNombre');
            const kitDescripcionEl = document.getElementById('kitDescripcion');
            const kitCostoEl = document.getElementById('kitCosto');
            const kitImagePreviewEl = document.getElementById('kitImagePreview');
            const kitModalTitleEl = document.getElementById('kitModalTitle');
            if (kitIdEl) kitIdEl.value = kit.id;
            if (kitNombreEl) kitNombreEl.value = kit.nombre;
            if (kitDescripcionEl) kitDescripcionEl.value = kit.descripcion;
            if (kitCostoEl) kitCostoEl.value = kit.costo;
            if (kitImagePreviewEl && kit.url_imagen_principal) {
                kitImagePreviewEl.innerHTML = `<img src="${kit.url_imagen_principal}" class="image-preview">`;
            }
            if (kitModalTitleEl) kitModalTitleEl.textContent = 'Editar Kit';
            const modal = document.getElementById('addKitModal');
            if (modal) new bootstrap.Modal(modal).show();
        }
    });
};

window.viewKitDetails = (id) => {
    currentKitId = id;
    loadKitDetails(id);
    const modal = document.getElementById('kitDetailsModal');
    if (modal) new bootstrap.Modal(modal).show();
};

window.deleteKit = (id) => {
    if (!token) return;
    if (confirm('¿Estás seguro de eliminar este kit?')) {
        fetch('/api/admin_acabados.php', {
            method: 'POST',
            headers: { 
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'delete_kit', id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                loadKits();
            } else {
                alert('Error: ' + data.mensaje);
            }
        });
    }
};

window.editPackage = (id) => {
    if (!token) return;
    fetch(`/api/admin_acabados.php?action=get_package&id=${id}`, {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            const pkg = data.package;
            const packageIdEl = document.getElementById('packageId');
            const packageNombreEl = document.getElementById('packageNombre');
            const packageDescripcionEl = document.getElementById('packageDescripcion');
            const packagePrecioEl = document.getElementById('packagePrecio');
            const packageActivoEl = document.getElementById('packageActivo');
            const packageImagesPreviewEl = document.getElementById('packageImagesPreview');
            const packageModalTitleEl = document.getElementById('packageModalTitle');
            if (packageIdEl) packageIdEl.value = pkg.id;
            if (packageNombreEl) packageNombreEl.value = pkg.nombre;
            if (packageDescripcionEl) packageDescripcionEl.value = pkg.descripcion;
            if (packagePrecioEl) packagePrecioEl.value = pkg.precio;
            if (packageActivoEl) packageActivoEl.checked = pkg.activo;
            if (packageImagesPreviewEl && pkg.fotos && pkg.fotos.length > 0) {
                packageImagesPreviewEl.innerHTML = pkg.fotos.map(foto => `<img src="${foto}" class="image-preview existing">`).join('');
                packageImagesPreviewEl.setAttribute('data-existing', JSON.stringify(pkg.fotos));
            } else {
                packageImagesPreviewEl.innerHTML = '';
                packageImagesPreviewEl.setAttribute('data-existing', '[]');
            }
            if (packageModalTitleEl) packageModalTitleEl.textContent = 'Editar Paquete';
            const modal = document.getElementById('addPackageModal');
            if (modal) new bootstrap.Modal(modal).show();
        }
    });
};

window.deletePackage = (id) => {
    if (!token) return;
    if (confirm('¿Estás seguro de eliminar este paquete?')) {
        fetch('/api/admin_acabados.php', {
            method: 'POST',
            headers: { 
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'delete_package', id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                loadPackages();
            } else {
                alert('Error: ' + data.mensaje);
            }
        });
    }
};

window.editColorOption = (id) => {
    openColorOptionModal(id);
};

window.viewAcabadoDetails = (kitId, color) => {
    currentColor = color;
    if (!token) return;
    fetch(`/api/admin_acabados.php?action=get_acabado_details&kit_id=${kitId}&color=${encodeURIComponent(color)}`, {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            renderAcabadoDetailsTable(data.details);
        }
    });
};

window.deleteColorOption = (id) => {
    if (!token) return;
    if (confirm('¿Estás seguro de eliminar esta opción de color?')) {
        fetch('/api/admin_acabados.php', {
            method: 'POST',
            headers: { 
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'delete_color_option', id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                if (currentKitId) {
                    loadKitDetails(currentKitId);
                } else {
                    loadColorOptions();
                }
            } else {
                alert('Error: ' + data.mensaje);
            }
        });
    }
};

window.editAcabadoDetail = (id) => {
    openAcabadoDetailModal(id);
};

window.deleteAcabadoDetail = (id) => {
    if (!token) return;
    if (confirm('¿Estás seguro de eliminar este detalle de acabado?')) {
        fetch('/api/admin_acabados.php', {
            method: 'POST',
            headers: { 
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'delete_acabado_detail', id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                if (currentKitId && currentColor) {
                    viewAcabadoDetails(currentKitId, currentColor);
                } else {
                    loadAcabadoDetails();
                }
            } else {
                alert('Error: ' + data.mensaje);
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is responsable
    const user = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
    const token = localStorage.getItem('cs_token');
    
    if (!user.id || !token || !user.is_responsable) {
        document.querySelector('.container').innerHTML = '<div class="alert alert-danger text-center"><h4>Acceso Denegado</h4><p>Solo los responsables pueden acceder a esta página.</p></div>';
        return;
    }

    // Initialize
    loadKits();
    loadPackages();
    loadComponentes();
    loadColorOptions();
    loadAcabadoDetails();
    loadColors();

    // Event listeners
    const saveKitBtn = document.getElementById('saveKitBtn');
    if (saveKitBtn) saveKitBtn.addEventListener('click', saveKit);

    const savePackageBtn = document.getElementById('savePackageBtn');
    if (savePackageBtn) savePackageBtn.addEventListener('click', savePackage);

    const saveColorOptionBtn = document.getElementById('saveColorOptionBtn');
    if (saveColorOptionBtn) saveColorOptionBtn.addEventListener('click', saveColorOption);

    const saveAcabadoDetailBtn = document.getElementById('saveAcabadoDetailBtn');
    if (saveAcabadoDetailBtn) saveAcabadoDetailBtn.addEventListener('click', saveAcabadoDetail);

    const saveComponenteBtn = document.getElementById('saveComponenteBtn');
    if (saveComponenteBtn) saveComponenteBtn.addEventListener('click', saveComponente);

    // Reset functions
    window.resetKitForm = () => {
        document.getElementById('kitForm').reset();
        document.getElementById('kitId').value = '';
        document.getElementById('kitModalTitle').textContent = 'Añadir Kit';
        document.getElementById('kitImagePreview').innerHTML = '';
    };

    window.resetPackageForm = () => {
        document.getElementById('packageForm').reset();
        document.getElementById('packageId').value = '';
        document.getElementById('packageModalTitle').textContent = 'Añadir Paquete';
        document.getElementById('packageImagesPreview').innerHTML = '';
        document.getElementById('packageImagesPreview').setAttribute('data-existing', '[]');
    };

    window.resetComponenteForm = () => {
        document.getElementById('componenteForm').reset();
        document.getElementById('componenteId').value = '';
        document.getElementById('componenteModalTitle').textContent = 'Añadir Componente';
    };

    window.resetColorOptionForm = () => {
        document.getElementById('colorOptionForm').reset();
        document.getElementById('colorOptionId').value = '';
        document.getElementById('colorOptionModalTitle').textContent = 'Añadir Opción de Color';
        document.getElementById('colorOptionImagePreview').innerHTML = '';
    };

    window.resetAcabadoDetailForm = () => {
        document.getElementById('acabadoDetailForm').reset();
        document.getElementById('acabadoDetailId').value = '';
        document.getElementById('acabadoDetailModalTitle').textContent = 'Añadir Detalle de Acabado';
        document.getElementById('acabadoDetailImagePreview').innerHTML = '';
    };

    const addColorOptionBtn = document.getElementById('addColorOptionBtn');
    if (addColorOptionBtn) addColorOptionBtn.addEventListener('click', () => openColorOptionModal());

    const addAcabadoDetailBtn = document.getElementById('addAcabadoDetailBtn');
    if (addAcabadoDetailBtn) addAcabadoDetailBtn.addEventListener('click', () => openAcabadoDetailModal());

    // Image preview handlers
    const kitImagen = document.getElementById('kitImagen');
    if (kitImagen) kitImagen.addEventListener('change', previewImage);

    const packageFotos = document.getElementById('packageFotos');
    if (packageFotos) packageFotos.addEventListener('change', previewImages);

    const colorOptionImagen = document.getElementById('colorOptionImagen');
    if (colorOptionImagen) colorOptionImagen.addEventListener('change', (e) => previewImageSingle(e, 'colorOptionImagePreview'));

    const acabadoDetailImagen = document.getElementById('acabadoDetailImagen');
    if (acabadoDetailImagen) acabadoDetailImagen.addEventListener('change', (e) => previewImageSingle(e, 'acabadoDetailImagePreview'));

    function loadKits() {
        fetch('/api/admin_acabados.php?action=get_kits', {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderKitsTable(data.kits);
            } else {
                alert('Error al cargar kits: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function loadPackages() {
        fetch('/api/admin_acabados.php?action=get_packages', {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderPackagesTable(data.packages);
            } else {
                alert('Error al cargar paquetes: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function loadComponentes() {
        fetch('/api/admin_acabados.php?action=get_componentes', {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderComponentesTable(data.componentes);
                populateComponentes(data.componentes); // For acabado detail modal
            } else {
                alert('Error al cargar componentes: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function populateKits(kits) {
        const select = document.getElementById('acabadoDetailKit');
        select.innerHTML = '<option value="">Seleccionar kit</option>';
        kits.forEach(kit => {
            select.insertAdjacentHTML('beforeend', `<option value="${kit.id}">${kit.nombre}</option>`);
        });
    }

    function populateComponentes(componentes) {
        const select = document.getElementById('acabadoDetailComponente');
        select.innerHTML = '<option value="">Seleccionar componente</option>';
        componentes.forEach(comp => {
            select.insertAdjacentHTML('beforeend', `<option value="${comp.id}">${comp.nombre}</option>`);
        });
    }

    function populateColors(colors) {
        const select = document.getElementById('acabadoDetailColor');
        select.innerHTML = '<option value="">Seleccionar color</option>';
        colors.forEach(color => {
            select.insertAdjacentHTML('beforeend', `<option value="${color}">${color}</option>`);
        });
    }

    function populateKitsForColorOption(kits) {
        const select = document.getElementById('colorOptionKit');
        select.innerHTML = '<option value="">Seleccionar kit</option>';
        kits.forEach(kit => {
            select.insertAdjacentHTML('beforeend', `<option value="${kit.id}">${kit.nombre}</option>`);
        });
    }

    function loadColorOptions() {
        fetch('/api/admin_acabados.php?action=get_all_color_options', {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderColorOptionsTable(data.options);
            } else {
                alert('Error al cargar opciones de color: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function loadAcabadoDetails() {
        fetch('/api/admin_acabados.php?action=get_all_acabado_details', {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderAcabadoDetailsTable(data.details);
            } else {
                alert('Error al cargar detalles de acabado: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function loadColors() {
        fetch('/api/admin_acabados.php?action=get_color_names', {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                populateColors(data.colors);
            } else {
                alert('Error al cargar colores: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function loadKits() {
        fetch('/api/admin_acabados.php?action=get_kits', {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderKitsTable(data.kits);
                populateKits(data.kits);
                populateKitsForColorOption(data.kits);
            } else {
                alert('Error al cargar kits: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function renderKitsTable(kits) {
        const tbody = document.getElementById('kitsTableBody');
        tbody.innerHTML = '';
        kits.forEach(kit => {
            const row = `
                <tr>
                    <td>${kit.id}</td>
                    <td>${kit.nombre}</td>
                    <td>${kit.descripcion || ''}</td>
                    <td><img src="${kit.url_imagen_principal || ''}" class="image-preview" onerror="this.style.display='none'"></td>
                    <td>$${kit.costo}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editKit(${kit.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="viewKitDetails(${kit.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteKit(${kit.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    function renderPackagesTable(packages) {
        const tbody = document.getElementById('packagesTableBody');
        tbody.innerHTML = '';
        packages.forEach(pkg => {
            const fotosHtml = (pkg.fotos || []).map(foto => `<img src="${foto}" class="image-preview" onerror="this.style.display='none'">`).join('');
            const row = `
                <tr>
                    <td>${pkg.id}</td>
                    <td>${pkg.nombre}</td>
                    <td>${pkg.descripcion || ''}</td>
                    <td>$${pkg.precio}</td>
                    <td>${fotosHtml}</td>
                    <td><span class="badge ${pkg.activo ? 'bg-success' : 'bg-secondary'}">${pkg.activo ? 'Activo' : 'Inactivo'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editPackage(${pkg.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deletePackage(${pkg.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    function renderComponentesTable(componentes) {
        const tbody = document.getElementById('componentesTableBody');
        tbody.innerHTML = '';
        componentes.forEach(comp => {
            const row = `
                <tr>
                    <td>${comp.id}</td>
                    <td>${comp.nombre}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editComponente(${comp.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteComponente(${comp.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    function renderColorOptionsTable(options) {
        const tbody = document.getElementById('colorOptionsTableBody');
        tbody.innerHTML = '';
        options.forEach(option => {
            const row = `
                <tr>
                    <td>${option.id}</td>
                    <td>${option.kit}</td>
                    <td>${option.nombre_opcion}</td>
                    <td>${option.color_nombre}</td>
                    <td><img src="${option.url_imagen_opcion || ''}" class="image-preview" onerror="this.style.display='none'"></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editColorOption(${option.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteColorOption(${option.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    function renderAcabadoDetailsTable(details) {
        const tbody = document.getElementById('acabadoDetailsTableBody');
        tbody.innerHTML = '';
        details.forEach(detail => {
            const row = `
                <tr>
                    <td>${detail.id}</td>
                    <td>${detail.kit}</td>
                    <td>${detail.componente}</td>
                    <td>${detail.color}</td>
                    <td><img src="${detail.url_imagen || ''}" class="image-preview" onerror="this.style.display='none'"></td>
                    <td>${detail.descripcion || ''}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editAcabadoDetail(${detail.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAcabadoDetail(${detail.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    function saveKit() {
        const formData = new FormData(document.getElementById('kitForm'));
        formData.append('action', 'save_kit');

        fetch('/api/admin_acabados.php', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                bootstrap.Modal.getInstance(document.getElementById('addKitModal')).hide();
                loadKits();
                document.getElementById('kitForm').reset();
                document.getElementById('kitImagePreview').innerHTML = '';
            } else {
                alert('Error: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function savePackage() {
        const formData = new FormData(document.getElementById('packageForm'));
        formData.append('action', 'save_package');

        fetch('/api/admin_acabados.php', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                bootstrap.Modal.getInstance(document.getElementById('addPackageModal')).hide();
                loadPackages();
                document.getElementById('packageForm').reset();
                document.getElementById('packageImagesPreview').innerHTML = '';
            } else {
                alert('Error: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('kitImagePreview').innerHTML = `<img src="${e.target.result}" class="image-preview">`;
            };
            reader.readAsDataURL(file);
        }
    }

    function previewImageSingle(event, previewId) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById(previewId).innerHTML = `<img src="${e.target.result}" class="image-preview">`;
            };
            reader.readAsDataURL(file);
        }
    }

    function previewImages(event) {
        const files = event.target.files;
        const preview = document.getElementById('packageImagesPreview');
        const existing = JSON.parse(preview.getAttribute('data-existing') || '[]');
        preview.innerHTML = existing.map(foto => `<img src="${foto}" class="image-preview existing">`).join('');
        Array.from(files).forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.insertAdjacentHTML('beforeend', `<img src="${e.target.result}" class="image-preview new">`);
            };
            reader.readAsDataURL(file);
        });
    }

    // Global functions for onclick
    window.editKit = (id) => {
        // Load kit data and show modal
        fetch(`/api/admin_acabados.php?action=get_kit&id=${id}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                const kit = data.kit;
                document.getElementById('kitId').value = kit.id;
                document.getElementById('kitNombre').value = kit.nombre;
                document.getElementById('kitDescripcion').value = kit.descripcion;
                document.getElementById('kitCosto').value = kit.costo;
                if (kit.url_imagen_principal) {
                    document.getElementById('kitImagePreview').innerHTML = `<img src="${kit.url_imagen_principal}" class="image-preview">`;
                }
                document.getElementById('kitModalTitle').textContent = 'Editar Kit';
                new bootstrap.Modal(document.getElementById('addKitModal')).show();
            }
        });
    };

    window.viewKitDetails = (id) => {
        // Load kit details
        loadKitDetails(id);
        new bootstrap.Modal(document.getElementById('kitDetailsModal')).show();
    };

    window.deleteKit = (id) => {
        if (confirm('¿Estás seguro de eliminar este kit?')) {
            fetch('/api/admin_acabados.php', {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete_kit', id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    loadKits();
                } else {
                    alert('Error: ' + data.mensaje);
                }
            });
        }
    };

    window.editPackage = (id) => {
        // Similar to editKit
        fetch(`/api/admin_acabados.php?action=get_package&id=${id}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                const pkg = data.package;
                document.getElementById('packageId').value = pkg.id;
                document.getElementById('packageNombre').value = pkg.nombre;
                document.getElementById('packageDescripcion').value = pkg.descripcion;
                document.getElementById('packagePrecio').value = pkg.precio;
                document.getElementById('packageActivo').checked = pkg.activo;
                if (pkg.fotos && pkg.fotos.length > 0) {
                    const preview = document.getElementById('packageImagesPreview');
                    preview.innerHTML = pkg.fotos.map(foto => `<img src="${foto}" class="image-preview">`).join('');
                }
                document.getElementById('packageModalTitle').textContent = 'Editar Paquete';
                new bootstrap.Modal(document.getElementById('addPackageModal')).show();
            }
        });
    };

    window.deletePackage = (id) => {
        if (confirm('¿Estás seguro de eliminar este paquete?')) {
            fetch('/api/admin_acabados.php', {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete_package', id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    loadPackages();
                } else {
                    alert('Error: ' + data.mensaje);
                }
            });
        }
    };

    function loadKitDetails(kitId) {
        // Load color options and acabado details for the kit
        // This would require additional API endpoints
        // For now, placeholder
        document.getElementById('colorOptionsTableBody').innerHTML = '<tr><td colspan="5">Cargando...</td></tr>';
        document.getElementById('acabadoDetailsTableBody').innerHTML = '<tr><td colspan="6">Cargando...</td></tr>';
    }

    function openColorOptionModal(optionId = null) {
        // Open modal for adding/editing color option
        if (optionId) {
            // Load option data
            fetch(`/api/admin_acabados.php?action=get_color_option&id=${optionId}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    const option = data.option;
                    document.getElementById('colorOptionId').value = option.id;
                    document.getElementById('colorOptionKit').value = option.acabado_kit_id;
                    document.getElementById('colorOptionNombre').value = option.nombre_opcion;
                    document.getElementById('colorOptionColor').value = option.color_nombre;
                    if (option.url_imagen_opcion) {
                        document.getElementById('colorOptionImagePreview').innerHTML = `<img src="${option.url_imagen_opcion}" class="image-preview">`;
                    }
                    document.getElementById('colorOptionModalTitle').textContent = 'Editar Opción de Color';
                }
            });
        } else {
            document.getElementById('colorOptionForm').reset();
            document.getElementById('colorOptionId').value = '';
            document.getElementById('colorOptionKitId').value = currentKitId; // Need to set currentKitId
            document.getElementById('colorOptionImagePreview').innerHTML = '';
            document.getElementById('colorOptionModalTitle').textContent = 'Añadir Opción de Color';
        }
        new bootstrap.Modal(document.getElementById('addColorOptionModal')).show();
    }

    function openColorOptionModal(optionId = null) {
        // Open modal for adding/editing color option
        if (optionId) {
            // Load option data
            fetch(`/api/admin_acabados.php?action=get_color_option&id=${optionId}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    const option = data.option;
                    document.getElementById('colorOptionId').value = option.id;
                    document.getElementById('colorOptionKit').value = option.acabado_kit_id;
                    document.getElementById('colorOptionNombre').value = option.nombre_opcion;
                    document.getElementById('colorOptionColor').value = option.color_nombre;
                    if (option.url_imagen_opcion) {
                        document.getElementById('colorOptionImagePreview').innerHTML = `<img src="${option.url_imagen_opcion}" class="image-preview">`;
                    }
                    document.getElementById('colorOptionModalTitle').textContent = 'Editar Opción de Color';
                }
            });
        } else {
            document.getElementById('colorOptionForm').reset();
            document.getElementById('colorOptionId').value = '';
            document.getElementById('colorOptionKit').value = currentKitId || '';
            document.getElementById('colorOptionImagePreview').innerHTML = '';
            document.getElementById('colorOptionModalTitle').textContent = 'Añadir Opción de Color';
        }
        new bootstrap.Modal(document.getElementById('addColorOptionModal')).show();
    }

    function openAcabadoDetailModal(detailId = null) {
        // Open modal for adding/editing acabado detail
        if (detailId) {
            // Load detail data
            fetch(`/api/admin_acabados.php?action=get_acabado_detail&id=${detailId}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    const detail = data.detail;
                    document.getElementById('acabadoDetailId').value = detail.id;
                    document.getElementById('acabadoDetailKit').value = detail.acabado_kit_id;
                    document.getElementById('acabadoDetailComponente').value = detail.componente_id;
                    document.getElementById('acabadoDetailColor').value = detail.color;
                    document.getElementById('acabadoDetailDescripcion').value = detail.descripcion;
                    if (detail.url_imagen) {
                        document.getElementById('acabadoDetailImagePreview').innerHTML = `<img src="${detail.url_imagen}" class="image-preview">`;
                    }
                    document.getElementById('acabadoDetailModalTitle').textContent = 'Editar Detalle de Acabado';
                }
            });
        } else {
            document.getElementById('acabadoDetailForm').reset();
            document.getElementById('acabadoDetailId').value = '';
            document.getElementById('acabadoDetailKit').value = currentKitId;
            document.getElementById('acabadoDetailImagePreview').innerHTML = '';
            document.getElementById('acabadoDetailModalTitle').textContent = 'Añadir Detalle de Acabado';
        }
        new bootstrap.Modal(document.getElementById('addAcabadoDetailModal')).show();
    }

    function renderColorOptionsTable(options, kitId) {
        const tbody = document.getElementById('colorOptionsTableBody');
        tbody.innerHTML = '';
        options.forEach(option => {
            const row = `
                <tr>
                    <td>${option.id}</td>
                    <td>${option.kit}</td>
                    <td>${option.nombre_opcion}</td>
                    <td>${option.color_nombre}</td>
                    <td><img src="${option.url_imagen_opcion || ''}" class="image-preview" onerror="this.style.display='none'"></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editColorOption(${option.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="viewAcabadoDetails(${kitId}, '${option.color_nombre}')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteColorOption(${option.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    function populateComponentes(componentes) {
        const select = document.getElementById('acabadoDetailComponente');
        select.innerHTML = '<option value="">Seleccionar componente</option>';
        componentes.forEach(comp => {
            select.insertAdjacentHTML('beforeend', `<option value="${comp.id}">${comp.nombre}</option>`);
        });
    }

    function saveColorOption() {
        const formData = new FormData(document.getElementById('colorOptionForm'));
        formData.append('action', 'save_color_option');

        fetch('/api/admin_acabados.php', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                bootstrap.Modal.getInstance(document.getElementById('addColorOptionModal')).hide();
                if (currentKitId) {
                    loadKitDetails(currentKitId);
                } else {
                    loadColorOptions();
                }
            } else {
                alert('Error: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function saveAcabadoDetail() {
        const formData = new FormData(document.getElementById('acabadoDetailForm'));
        formData.append('action', 'save_acabado_detail');

        fetch('/api/admin_acabados.php', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                bootstrap.Modal.getInstance(document.getElementById('addAcabadoDetailModal')).hide();
                // Reload details for current color
                if (currentKitId && currentColor) {
                    viewAcabadoDetails(currentKitId, currentColor);
                } else {
                    loadAcabadoDetails();
                }
            } else {
                alert('Error: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function saveComponente() {
        const formData = new FormData(document.getElementById('componenteForm'));
        formData.append('action', 'save_componente');

        fetch('/api/admin_acabados.php', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                bootstrap.Modal.getInstance(document.getElementById('addComponenteModal')).hide();
                loadComponentes();
                document.getElementById('componenteForm').reset();
            } else {
                alert('Error: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Global variables for current kit and color
    let currentKitId = null;
    let currentColor = null;

    // Update viewKitDetails to set currentKitId
    window.viewKitDetails = (id) => {
        currentKitId = id;
        loadKitDetails(id);
        new bootstrap.Modal(document.getElementById('kitDetailsModal')).show();
    };

    window.editColorOption = (id) => {
        openColorOptionModal(id);
    };

    window.viewAcabadoDetails = (kitId, color) => {
        currentColor = color;
        fetch(`/api/admin_acabados.php?action=get_acabado_details&kit_id=${kitId}&color=${encodeURIComponent(color)}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderAcabadoDetailsTable(data.details);
            }
        });
    };

    window.deleteColorOption = (id) => {
        if (confirm('¿Estás seguro de eliminar esta opción de color?')) {
            fetch('/api/admin_acabados.php', {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete_color_option', id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    loadKitDetails(currentKitId);
                } else {
                    alert('Error: ' + data.mensaje);
                }
            });
        }
    };

    function renderAcabadoDetailsTable(details) {
        const tbody = document.getElementById('acabadoDetailsTableBody');
        tbody.innerHTML = '';
        details.forEach(detail => {
            const row = `
                <tr>
                    <td>${detail.id}</td>
                    <td>${detail.kit}</td>
                    <td>${detail.componente}</td>
                    <td>${detail.color}</td>
                    <td><img src="${detail.url_imagen || ''}" class="image-preview" onerror="this.style.display='none'"></td>
                    <td>${detail.descripcion || ''}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editAcabadoDetail(${detail.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAcabadoDetail(${detail.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    window.deleteAcabadoDetail = (id) => {
        if (confirm('¿Estás seguro de eliminar este detalle de acabado?')) {
            fetch('/api/admin_acabados.php', {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete_acabado_detail', id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    viewAcabadoDetails(currentKitId, currentColor);
                } else {
                    alert('Error: ' + data.mensaje);
                }
            });
        }
    };

    window.editComponente = (id) => {
        fetch(`/api/admin_acabados.php?action=get_componente&id=${id}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                const comp = data.componente;
                document.getElementById('componenteId').value = comp.id;
                document.getElementById('componenteNombre').value = comp.nombre;
                document.getElementById('componenteModalTitle').textContent = 'Editar Componente';
                new bootstrap.Modal(document.getElementById('addComponenteModal')).show();
            }
        });
    };

    window.deleteComponente = (id) => {
        if (confirm('¿Estás seguro de eliminar este componente?')) {
            fetch('/api/admin_acabados.php', {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete_componente', id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    loadComponentes();
                } else {
                    alert('Error: ' + data.mensaje);
                }
            });
        }
    };

    window.editAcabadoDetail = (id) => {
        openAcabadoDetailModal(id);
    };

    window.deleteAcabadoDetail = (id) => {
        if (confirm('¿Estás seguro de eliminar este detalle de acabado?')) {
            fetch('/api/admin_acabados.php', {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete_acabado_detail', id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    loadAcabadoDetails();
                } else {
                    alert('Error: ' + data.mensaje);
                }
            });
        }
    };

    window.editColorOption = (id) => {
        fetch(`/api/admin_acabados.php?action=get_color_option&id=${id}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                const option = data.option;
                document.getElementById('colorOptionId').value = option.id;
                document.getElementById('colorOptionKit').value = option.acabado_kit_id;
                document.getElementById('colorOptionNombre').value = option.nombre_opcion;
                document.getElementById('colorOptionColor').value = option.color_nombre;
                if (option.url_imagen_opcion) {
                    document.getElementById('colorOptionImagePreview').innerHTML = `<img src="${option.url_imagen_opcion}" class="image-preview">`;
                }
                document.getElementById('colorOptionModalTitle').textContent = 'Editar Opción de Color';
                new bootstrap.Modal(document.getElementById('addColorOptionModal')).show();
            }
        });
    };

    window.deleteColorOption = (id) => {
        if (confirm('¿Estás seguro de eliminar esta opción de color?')) {
            fetch('/api/admin_acabados.php', {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete_color_option', id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    loadColorOptions();
                } else {
                    alert('Error: ' + data.mensaje);
                }
            });
        }
    };
});
</script>
</body>
</html></content>
