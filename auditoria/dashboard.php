<?php
require_once __DIR__ . '/../config/db.php';
$db = DB::getDB();

// Validación de autenticación y rol
session_start();

// Obtener token desde POST (enviado por JavaScript)
$token = $_POST['token'] ?? null;

if (!$token) {
    // Si no hay token, mostrar página de acceso denegado
    $showAccessDenied = true;
} else {
    // Verificar si el usuario es responsable en la tabla responsable
    try {
        $stmt = $db->prepare('SELECT id, nombre, correo FROM responsable WHERE token = ? AND estado = 1');
        $stmt->execute([$token]);
        $responsable = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$responsable) {
            $showAccessDenied = true;
        } else {
            $showAccessDenied = false;
            
            // Registrar acceso al dashboard de auditoría
            require_once __DIR__ . '/../api/helpers/audit_helper.php';
            log_audit_action($db, 'ACCESS_DASHBOARD', $responsable['id'], 'responsable', 'auditoria', null, [
                'dashboard' => 'audit_dashboard',
                'responsable_name' => $responsable['nombre']
            ]);
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
  <title>Dashboard de Auditoría</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/style_audit_dashboard.css" rel="stylesheet">
  <style>
    .section-title {
        position: relative;
    }
    .btn-close-detail {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        color: #6c757d;
    }
    .btn-close-detail:hover {
        color: #343a40;
    }
  </style>
</head>
<body>

<?php if ($showAccessDenied): ?>
<!-- Access Denied Section -->
<div class="access-denied">
  <i class="bi bi-shield-exclamation"></i>
  <h2>Acceso Denegado</h2>
  <p>No tienes permisos para acceder a esta página.<br>Solo los responsables pueden acceder al dashboard de auditoría.</p>
  <button class="btn btn-primary mt-3" onclick="history.back()">
    <i class="bi bi-arrow-left"></i> Volver
  </button>
</div>

<?php else: ?>
<!-- Header Section -->
<div class="audit-header">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()" 
            aria-label="Volver a la página anterior" 
            tabindex="0"
            onkeydown="handleKeyDown(event, () => history.back())">
      <i class="bi bi-arrow-left" aria-hidden="true"></i>
    </button>
    <div>
      <h2 class="audit-title">Dashboard de Auditoría</h2>
    </div>
    <button class="refresh-button" onclick="refreshData()" 
            aria-label="Actualizar todos los datos del dashboard" 
            tabindex="0"
            onkeydown="handleKeyDown(event, refreshData)">
      <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
    </button>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="audit-container">
    
    <!-- Modules Grid -->
    <div class="modules-grid" id="modulesGrid" role="grid" aria-label="Módulos de auditoría disponibles">
      <div class="loading" role="status" aria-live="polite">
        <i class="bi bi-hourglass-split" aria-hidden="true"></i>
        <p>Cargando módulos...</p>
      </div>
    </div>

    <!-- Recent Audits Section -->
    <div class="recent-audits-section">
      <h3 class="section-title">
        <i class="bi bi-clock-history" aria-hidden="true"></i>
        Últimas 10 Auditorías
      </h3>
      <div id="recentAudits" role="region" aria-label="Auditorías recientes">
        <div class="loading" role="status" aria-live="polite">
          <i class="bi bi-hourglass-split" aria-hidden="true"></i>
          <p>Cargando auditorías recientes...</p>
        </div>
      </div>
    </div>

    <!-- Module Detail Section -->
    <div class="module-detail-section" id="moduleDetailSection">
      <h3 class="section-title" id="moduleDetailTitle">
        <i class="bi bi-list-ul"></i>
        Detalle del Módulo
      </h3>
      
      <!-- Chart Section -->
      <div class="chart-section" id="chartSection" style="display: none;">
        <h4 class="section-title">
          <i class="bi bi-bar-chart"></i>
          Distribución de Auditorías
        </h4>
        <div class="chart-container">
          <canvas id="auditChart"></canvas>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="filters-section">
        <div class="filters-row">
          <div class="filter-group">
            <label for="filterDateFrom">Desde:</label>
            <input type="date" id="filterDateFrom" class="form-control">
          </div>
          <div class="filter-group">
            <label for="filterDateTo">Hasta:</label>
            <input type="date" id="filterDateTo" class="form-control">
          </div>
          <div class="filter-group">
            <label for="filterUserType">Tipo de Usuario:</label>
            <select id="filterUserType" class="form-control">
              <option value="">Todos</option>
              <option value="usuario">Usuario</option>
              <option value="responsable">Responsable</option>
              <option value="sistema">Sistema</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="filterAction">Acción:</label>
            <select id="filterAction" class="form-control">
              <option value="">Todas las acciones</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="filterTargetId">ID Objetivo:</label>
            <input type="number" id="filterTargetId" class="form-control" placeholder="Ej: 123">
          </div>
          <div class="filter-group">
            <label for="filterSearch">Búsqueda:</label>
            <input type="text" id="filterSearch" class="form-control" placeholder="Buscar en detalles...">
          </div>
          <div class="filter-buttons">
            <button class="btn-filter" onclick="applyFilters()">
              <i class="bi bi-funnel"></i> Filtrar
            </button>
            <button class="btn-clear" onclick="clearFilters()">
              <i class="bi bi-x-circle"></i> Limpiar
            </button>
          </div>
        </div>
      </div>

      <!-- Audit Table -->
      <div id="auditTableContainer">
        <div class="loading">
          <i class="bi bi-hourglass-split"></i>
          <p>Cargando datos...</p>
        </div>
      </div>

      <!-- Pagination -->
      <div class="pagination-section" id="paginationSection" style="display: none;">
        <div>
          <span id="resultsInfo">Mostrando 0 resultados</span>
        </div>
        <button class="btn-load-more" id="loadMoreBtn" onclick="loadMoreAudits()">
          <i class="bi bi-arrow-down-circle"></i> Cargar más
        </button>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
console.log('Script cargado - Iniciando verificación de autenticación');

// Registrar plugins de Chart.js inmediatamente
if (typeof ChartDataLabels !== 'undefined') {
    Chart.register(ChartDataLabels);
}

// Verificar autenticación inmediatamente
const token = localStorage.getItem('cs_token');
console.log('Token encontrado:', token ? 'SÍ' : 'NO');

if (!token) {
    // Si no hay token, redirigir al login
    console.log('No hay token, redirigiendo al login');
    window.location.href = '../Front/login_front.php';
} else {
    console.log('Enviando token al servidor...');
    
    // Enviar token al servidor para validación
    const formData = new FormData();
    formData.append('token', token);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Respuesta del servidor recibida');
        return response.text();
    })
    .then(html => {
        console.log('Reemplazando contenido del body');
        // Reemplazar todo el contenido del body
        document.body.innerHTML = html;
        
        // Reinicializar Bootstrap después de reemplazar el contenido
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
        // Inicializar funcionalidades después de cargar el contenido
        initializeAuditDashboard();
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        alert('Error de autenticación');
    });
}

// Variables globales para el dashboard
let currentModule = null;
let currentOffset = 0;
let currentFilters = {};
let auditChart = null;

// Sistema de cache simple
const cache = {
    modules: null,
    modulesTimestamp: null,
    recentAudits: null,
    recentAuditsTimestamp: null,
    moduleAudits: {},
    moduleAuditsTimestamp: {},
    
    // Cache válido por 5 minutos para módulos y auditorías recientes
    CACHE_DURATION: 5 * 60 * 1000,
    
    // Cache válido por 2 minutos para auditorías de módulos específicos
    MODULE_CACHE_DURATION: 2 * 60 * 1000,
    
    isExpired(timestamp, duration) {
        return !timestamp || (Date.now() - timestamp) > duration;
    },
    
    getModules() {
        if (this.modules && !this.isExpired(this.modulesTimestamp, this.CACHE_DURATION)) {
            return this.modules;
        }
        return null;
    },
    
    setModules(data) {
        this.modules = data;
        this.modulesTimestamp = Date.now();
    },
    
    getRecentAudits() {
        if (this.recentAudits && !this.isExpired(this.recentAuditsTimestamp, this.CACHE_DURATION)) {
            return this.recentAudits;
        }
        return null;
    },
    
    setRecentAudits(data) {
        this.recentAudits = data;
        this.recentAuditsTimestamp = Date.now();
    },
    
    getModuleAudits(module, filters) {
        const key = `${module}_${JSON.stringify(filters)}`;
        if (this.moduleAudits[key] && !this.isExpired(this.moduleAuditsTimestamp[key], this.MODULE_CACHE_DURATION)) {
            return this.moduleAudits[key];
        }
        return null;
    },
    
    setModuleAudits(module, filters, data) {
        const key = `${module}_${JSON.stringify(filters)}`;
        this.moduleAudits[key] = data;
        this.moduleAuditsTimestamp[key] = Date.now();
    },
    
    clearCache() {
        this.modules = null;
        this.modulesTimestamp = null;
        this.recentAudits = null;
        this.recentAuditsTimestamp = null;
        this.moduleAudits = {};
        this.moduleAuditsTimestamp = {};
    }
};

// Función helper para mostrar mensajes de error
function showErrorMessage(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = `
            <div class="error-message">
                <i class="bi bi-exclamation-triangle"></i>
                <p>${message}</p>
                <button class="btn btn-sm btn-outline-primary" onclick="retryLastAction()">
                    <i class="bi bi-arrow-clockwise"></i> Reintentar
                </button>
            </div>
        `;
    }
}

// Función para reintentar la última acción
function retryLastAction() {
    if (currentModule) {
        loadModuleAudits();
    } else {
        loadModulesData();
        loadRecentAudits();
    }
}

// Función para actualizar todos los datos (limpiar cache y recargar)
function refreshData() {
    cache.clearCache();
    console.log('Cache limpiado, recargando datos...');
    
    if (currentModule) {
        loadModuleAudits();
    } else {
        loadModulesData();
        loadRecentAudits();
    }
}

// Función helper para manejar navegación por teclado
function handleKeyDown(event, callback) {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        callback();
    }
}

// Función para mostrar indicador de carga específico
function showLoadingIndicator(elementId, message = 'Cargando...') {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = `
            <div class="loading">
                <i class="bi bi-hourglass-split"></i>
                <p>${message}</p>
            </div>
        `;
    }
}

// Función para mostrar progreso de carga
function showProgressIndicator(elementId, current, total, message = 'Procesando...') {
    const element = document.getElementById(elementId);
    if (element) {
        const percentage = Math.round((current / total) * 100);
        element.innerHTML = `
            <div class="loading">
                <i class="bi bi-hourglass-split"></i>
                <p>${message}</p>
                <div class="progress mt-2" style="width: 200px; margin: 0 auto;">
                    <div class="progress-bar" role="progressbar" style="width: ${percentage}%" 
                         aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                        ${percentage}%
                    </div>
                </div>
            </div>
        `;
    }
}

// Función para inicializar el dashboard de auditoría
function initializeAuditDashboard() {
    console.log('Inicializando dashboard de auditoría');
    
    // Cargar datos iniciales
    loadModulesData();
    loadRecentAudits();
    
    // Configurar eventos
    setupEventListeners();
}

// Función para cargar los datos de los módulos
async function loadModulesData() {
    // Verificar cache primero
    const cachedData = cache.getModules();
    if (cachedData) {
        console.log('Usando datos de módulos desde cache');
        renderModulesGrid(cachedData);
        return;
    }
    
    showLoadingIndicator('modulesGrid', 'Cargando módulos de auditoría...');
    
    try {
        const response = await fetch('api/audit_dashboard_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                action: 'get_modules_data'
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.ok) {
            cache.setModules(data.modules);
            renderModulesGrid(data.modules);
        } else {
            console.error('Error al cargar módulos:', data.mensaje);
            showErrorMessage('modulesGrid', 'Error al cargar los módulos: ' + data.mensaje);
        }
    } catch (error) {
        console.error('Error en loadModulesData:', error);
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            showErrorMessage('modulesGrid', 'Error de conexión. Verifica tu conexión a internet.');
        } else if (error.name === 'SyntaxError') {
            showErrorMessage('modulesGrid', 'Error al procesar la respuesta del servidor.');
        } else {
            showErrorMessage('modulesGrid', 'Error inesperado: ' + error.message);
        }
    }
}

// Función para renderizar la grilla de módulos
function renderModulesGrid(modules) {
    const modulesGrid = document.getElementById('modulesGrid');
    
    const modulesHtml = modules.map((module, index) => `
        <div class="module-card" 
             onclick="showModuleDetail('${module.resource}')"
             onkeydown="handleKeyDown(event, () => showModuleDetail('${module.resource}'))"
             role="gridcell"
             tabindex="0"
             aria-label="Módulo ${module.name} con ${module.count} auditorías"
             data-module="${module.resource}">
            <div class="module-icon" aria-hidden="true">
                <i class="bi ${getModuleIcon(module.resource)}"></i>
            </div>
            <div class="module-title">${module.name}</div>
            <div class="module-count" aria-label="${module.count} auditorías">${module.count}</div>
            <div class="module-description">${module.description}</div>
        </div>
    `).join('');
    
    modulesGrid.innerHTML = modulesHtml;
}

// Función para obtener el icono del módulo
function getModuleIcon(resource) {
    const icons = {
        'autenticacion': 'bi-shield-lock',
        'usuario': 'bi-people',
        'cita': 'bi-calendar-event',
        'ctg': 'bi-exclamation-triangle',
        'pqr': 'bi-chat-dots',
        'acabados': 'bi-palette',
        'perfil': 'bi-person-circle',
        'notificaciones': 'bi-bell',
        'acceso_modulo': 'bi-door-open'
    };
    return icons[resource] || 'bi-folder';
}

// Función para cargar auditorías recientes
async function loadRecentAudits() {
    // Verificar cache primero
    const cachedData = cache.getRecentAudits();
    if (cachedData) {
        console.log('Usando auditorías recientes desde cache');
        renderRecentAudits(cachedData);
        return;
    }
    
    showLoadingIndicator('recentAudits', 'Cargando auditorías recientes...');
    
    try {
        const response = await fetch('api/audit_dashboard_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                action: 'get_recent_audits'
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.ok) {
            cache.setRecentAudits(data.audits);
            renderRecentAudits(data.audits);
        } else {
            console.error('Error al cargar auditorías recientes:', data.mensaje);
            showErrorMessage('recentAudits', 'Error al cargar las auditorías recientes: ' + data.mensaje);
        }
    } catch (error) {
        console.error('Error en loadRecentAudits:', error);
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            showErrorMessage('recentAudits', 'Error de conexión. Verifica tu conexión a internet.');
        } else if (error.name === 'SyntaxError') {
            showErrorMessage('recentAudits', 'Error al procesar la respuesta del servidor.');
        } else {
            showErrorMessage('recentAudits', 'Error inesperado: ' + error.message);
        }
    }
}

// Función para renderizar auditorías recientes
function renderRecentAudits(audits) {
    const recentAudits = document.getElementById('recentAudits');
    
    if (audits.length === 0) {
        recentAudits.innerHTML = '<div class="no-data">No hay auditorías recientes</div>';
        return;
    }
    
    const auditsHtml = `
        <table class="audit-table" role="table" aria-label="Tabla de auditorías recientes">
            <thead>
                <tr role="row">
                    <th role="columnheader" scope="col">Fecha</th>
                    <th role="columnheader" scope="col">Usuario</th>
                    <th role="columnheader" scope="col">Acción</th>
                    <th role="columnheader" scope="col">Recurso</th>
                    <th role="columnheader" scope="col">IP</th>
                </tr>
            </thead>
            <tbody>
                ${audits.map(audit => `
                    <tr role="row">
                        <td role="cell">${formatDateTime(audit.timestamp)}</td>
                        <td role="cell">
                            <span class="user-type-badge user-${audit.user_type}" 
                                  aria-label="Tipo de usuario: ${audit.user_type}">${audit.user_type}</span>
                            ${audit.user_display_name ? ` (${audit.user_display_name})` : ''}
                        </td>
                        <td role="cell">
                            <span class="action-badge action-${getActionType(audit.action)}" 
                                  aria-label="Acción: ${audit.action}">${audit.action}</span>
                        </td>
                        <td role="cell">${audit.target_resource || '-'}</td>
                        <td role="cell">${audit.ip_address}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
    
    recentAudits.innerHTML = auditsHtml;
}

// Función para mostrar el detalle de un módulo
async function showModuleDetail(resource) {
    currentModule = resource;
    currentOffset = 0;
    currentFilters = {};

    // Ocultar auditorías recientes y mostrar detalle
    document.querySelector('.recent-audits-section').style.display = 'none';
    document.getElementById('moduleDetailSection').style.display = 'block';
    
    // Actualizar el título
    const moduleNames = {
        'autenticacion': 'Autenticación',
        'usuario': 'Usuarios',
        'cita': 'Citas',
        'ctg': 'CTG',
        'pqr': 'PQR',
        'acabados': 'Acabados',
        'perfil': 'Perfil',
        'notificaciones': 'Notificaciones',
        'acceso_modulo': 'Acceso a Módulos'
    };
    
    document.getElementById('moduleDetailTitle').innerHTML = `
        <i class="bi ${getModuleIcon(resource)}"></i>
        Detalle del Módulo: ${moduleNames[resource] || resource}
        <button class="btn-close-detail" onclick="hideModuleDetail()" title="Cerrar detalle">
            <i class="bi bi-x-lg"></i>
        </button>
    `;
    
    // Limpiar filtros
    resetFilters();
    
    // Poblar el combo box de acciones según el módulo
    await populateActionFilter(resource);
    
    // Cargar datos del módulo
    loadModuleAudits();
}

// Función para ocultar el detalle del módulo y mostrar la vista principal
function hideModuleDetail() {
    document.getElementById('moduleDetailSection').style.display = 'none';
    document.querySelector('.recent-audits-section').style.display = 'block';
    currentModule = null;
}

// Función para cargar auditorías de un módulo específico
async function loadModuleAudits() {
    const moduleNames = {
        'autenticacion': 'Autenticación',
        'usuario': 'Usuarios',
        'cita': 'Citas',
        'ctg': 'CTG',
        'pqr': 'PQR',
        'acabados': 'Acabados',
        'perfil': 'Perfil',
        'notificaciones': 'Notificaciones',
        'acceso_modulo': 'Acceso a Módulos'
    };
    
    const moduleName = moduleNames[currentModule] || currentModule;
    
    // Verificar cache primero (solo si no hay filtros activos y no hay paginación)
    const hasActiveFilters = Object.values(currentFilters).some(value => value !== '');
    if (!hasActiveFilters && currentOffset === 0) {
        const cachedData = cache.getModuleAudits(currentModule, currentFilters);
        if (cachedData) {
            console.log('Usando datos de módulo desde cache');
            renderModuleAudits(cachedData.audits, cachedData.total, cachedData.chart_data);
            return;
        }
    }
    
    showLoadingIndicator('auditTableContainer', `Cargando datos de ${moduleName}...`);
    
    try {
        // Determinar qué tipo de filtro usar según el módulo
        const isDetailsModule = currentModule === 'acabados' || currentModule === 'acceso_modulo';
        
        const requestData = {
            action: 'get_module_audits',
            resource: currentModule,
            offset: currentOffset,
            limit: 20,
            date_from: currentFilters.date_from || '',
            date_to: currentFilters.date_to || '',
            user_type: currentFilters.user_type || '',
            action_filter: isDetailsModule ? '' : (currentFilters.action || ''), // Solo para módulos de acción
            details_filter: isDetailsModule ? (currentFilters.action || '') : '', // Solo para módulos de detalles
            target_id: currentFilters.target_id || '',
            search: currentFilters.search || ''
        };
        
        const response = await fetch('api/audit_dashboard_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(requestData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.ok) {
            // Guardar en cache solo si no hay filtros activos
            if (!hasActiveFilters) {
                cache.setModuleAudits(currentModule, currentFilters, data);
            }
            renderModuleAudits(data.audits, data.total, data.chart_data);
        } else {
            console.error('Error al cargar auditorías del módulo:', data.mensaje);
            showErrorMessage('auditTableContainer', 'Error al cargar los datos del módulo: ' + data.mensaje);
        }
    } catch (error) {
        console.error('Error en loadModuleAudits:', error);
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            showErrorMessage('auditTableContainer', 'Error de conexión. Verifica tu conexión a internet.');
        } else if (error.name === 'SyntaxError') {
            showErrorMessage('auditTableContainer', 'Error al procesar la respuesta del servidor.');
        } else {
            showErrorMessage('auditTableContainer', 'Error inesperado: ' + error.message);
        }
    }
}

// Función para crear el gráfico de barras horizontales
function createAuditChart(audits) {
    // Destruir gráfico anterior si existe
    if (auditChart) {
        auditChart.destroy();
    }
    
    let labels, data, colors;
    
    // Si es el módulo de acceso a módulos, mostrar detalles en lugar de acciones
    if (currentModule === 'acceso_modulo') {
        // Contar detalles (nombres de menús) por tipo
        const detailCounts = {};
        audits.forEach(audit => {
            const detail = audit.formatted_details || audit.details || 'Sin detalles';
            detailCounts[detail] = (detailCounts[detail] || 0) + 1;
        });
        
        labels = Object.keys(detailCounts);
        data = Object.values(detailCounts);
    } else if (currentModule === 'acabados') {
        // Para el módulo de acabados, mostrar nombres de kits (solo el kit, sin color ni paquetes)
        const kitCounts = {};
        audits.forEach(audit => {
            const detail = audit.formatted_details || audit.details || 'Sin detalles';
            // Extraer el nombre del kit (antes del +, pero incluyendo espacios)
            const kitName = detail.split(' +')[0];
            kitCounts[kitName] = (kitCounts[kitName] || 0) + 1;
        });
        
        labels = Object.keys(kitCounts);
        data = Object.values(kitCounts);
    } else if (currentModule === 'cita') {
        // Para el módulo de citas, mostrar nombres de propósitos o tipos de acción
        const propositoCounts = {};
        audits.forEach(audit => {
            const detail = audit.formatted_details || audit.details || 'Sin detalles';
            
            // Saltar solo DELETE_CITA (acción de eliminación) para el gráfico
            if (audit.action === 'DELETE_CITA') {
                return;
            }
            
            // Si el detalle contiene "Cita cancelada", usar ese texto
            if (detail.includes('Cita cancelada')) {
                propositoCounts['Cita Cancelada'] = (propositoCounts['Cita Cancelada'] || 0) + 1;
            }
            // Si el detalle contiene "(Eliminada)", mostrar el propósito original (sin "Eliminada")
            else if (detail.includes('(Eliminada)')) {
                const propositoName = detail.split(' (Eliminada)')[0];
                propositoCounts[propositoName] = (propositoCounts[propositoName] || 0) + 1;
            }
            // Para citas normales, extraer solo el nombre del propósito (antes de la primera coma)
            else {
                const propositoName = detail.split(',')[0];
                propositoCounts[propositoName] = (propositoCounts[propositoName] || 0) + 1;
            }
        });
        
        labels = Object.keys(propositoCounts);
        data = Object.values(propositoCounts);
    } else if (currentModule === 'autenticacion') {
        // Para el módulo de autenticación, mostrar acciones específicas con nombres descriptivos
        const actionCounts = {};
        audits.forEach(audit => {
            const action = audit.action;
            let actionName;
            
            // Mapear acciones específicas de autenticación
            switch (action) {
                case 'LOGOUT':
                    actionName = 'Cerró Sesión';
                    break;
                case 'LOGIN_SUCCESS':
                    actionName = 'Inicio Sesión';
                    break;
                case 'LOGIN_FAILURE':
                    actionName = 'Falló la Sesión';
                    break;
                default:
                    actionName = action; // Mantener el nombre original para otras acciones
            }
            
            actionCounts[actionName] = (actionCounts[actionName] || 0) + 1;
        });
        
        labels = Object.keys(actionCounts);
        data = Object.values(actionCounts);
    } else {
        // Para otros módulos, contar acciones por tipo
        const actionCounts = {};
        audits.forEach(audit => {
            const actionType = getActionType(audit.action);
            actionCounts[actionType] = (actionCounts[actionType] || 0) + 1;
        });
        
        // Preparar datos para el gráfico
        labels = Object.keys(actionCounts).map(type => {
            const typeNames = {
                'login': 'Autenticación',
                'create': 'Creación',
                'update': 'Actualización',
                'delete': 'Eliminación',
                'access': 'Acceso'
            };
            return typeNames[type] || type;
        });
        
        data = Object.values(actionCounts);
    }
    
    colors = [
        '#2d5a3d', // Verde principal
        '#4a7c59', // Verde claro
        '#6c757d', // Gris
        '#dc3545', // Rojo
        '#fd7e14', // Naranja
        '#17a2b8', // Azul
        '#6f42c1', // Púrpura
        '#e83e8c', // Rosa
        '#20c997', // Verde agua
        '#ffc107'  // Amarillo
    ];
    
    // Crear el gráfico de barras horizontales
    const ctx = document.getElementById('auditChart').getContext('2d');
    auditChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad',
                data: data,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: colors.slice(0, labels.length),
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // Hacer el gráfico horizontal
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed.x;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                        }
                    }
                },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                    display: true,
                        text: 'Número de Auditorías'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Categorías'
                    }
                }
            }
        }
    });
}

// Función para renderizar auditorías de un módulo
function renderModuleAudits(audits, total, chartData) {
    const container = document.getElementById('auditTableContainer');
    
    if (audits.length === 0) {
        container.innerHTML = '<div class="no-data">No se encontraron auditorías para este módulo</div>';
        document.getElementById('paginationSection').style.display = 'none';
        document.getElementById('chartSection').style.display = 'none';
        return;
    }
    
    // Mostrar el gráfico
    document.getElementById('chartSection').style.display = 'block';
    
    // Actualizar título del gráfico según el módulo
    const chartTitle = document.querySelector('#chartSection .section-title');
    if (currentModule === 'acceso_modulo') {
        chartTitle.innerHTML = '<i class="bi bi-bar-chart"></i> Distribución por Módulos Accedidos';
    } else if (currentModule === 'acabados') {
        chartTitle.innerHTML = '<i class="bi bi-bar-chart"></i> Distribución por Kits Seleccionados';
    } else if (currentModule === 'cita') {
        chartTitle.innerHTML = '<i class="bi bi-bar-chart"></i> Distribución por Propósitos de Citas';
    } else if (currentModule === 'autenticacion') {
        chartTitle.innerHTML = '<i class="bi bi-bar-chart"></i> Distribución por Acciones de Autenticación';
    } else {
        chartTitle.innerHTML = '<i class="bi bi-bar-chart"></i> Distribución de Auditorías';
    }
    
    createAuditChart(chartData);
    
    const auditsHtml = `
        <table class="audit-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>ID Objetivo</th>
                    <th>IP</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                ${audits.map(audit => `
                    <tr>
                        <td>${formatDateTime(audit.timestamp)}</td>
                        <td>
                            <span class="user-type-badge user-${audit.user_type}">${audit.user_type}</span>
                            ${audit.user_display_name ? ` (${audit.user_display_name})` : ''}
                        </td>
                        <td><span class="action-badge action-${getActionType(audit.action)}">${audit.action}</span></td>
                        <td>${audit.target_id || '-'}</td>
                        <td>${audit.ip_address}</td>
                        <td>${audit.formatted_details || '-'}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
    
    container.innerHTML = auditsHtml;
    
    // Actualizar información de paginación
    const resultsInfo = document.getElementById('resultsInfo');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    
    resultsInfo.textContent = `Mostrando ${audits.length} de ${total} resultados`;
    
    if (audits.length < total) {
        document.getElementById('paginationSection').style.display = 'flex';
        loadMoreBtn.disabled = false;
    } else {
        document.getElementById('paginationSection').style.display = 'flex';
        loadMoreBtn.disabled = true;
        loadMoreBtn.textContent = 'No hay más resultados';
    }
}

// Función para cargar más auditorías
async function loadMoreAudits() {
    currentOffset += 20;
    
    const moduleNames = {
        'autenticacion': 'Autenticación',
        'usuario': 'Usuarios',
        'cita': 'Citas',
        'ctg': 'CTG',
        'pqr': 'PQR',
        'acabados': 'Acabados',
        'perfil': 'Perfil',
        'notificaciones': 'Notificaciones',
        'acceso_modulo': 'Acceso a Módulos'
    };
    
    const moduleName = moduleNames[currentModule] || currentModule;
    
    // Mostrar indicador de carga en el botón
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const originalText = loadMoreBtn.innerHTML;
    loadMoreBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Cargando...';
    loadMoreBtn.disabled = true;
    
    try {
        // Determinar qué tipo de filtro usar según el módulo
        const isDetailsModule = currentModule === 'acabados' || currentModule === 'acceso_modulo';
        
        const requestData = {
            action: 'get_module_audits',
            resource: currentModule,
            offset: currentOffset,
            limit: 20,
            date_from: currentFilters.date_from || '',
            date_to: currentFilters.date_to || '',
            user_type: currentFilters.user_type || '',
            action_filter: isDetailsModule ? '' : (currentFilters.action || ''), // Solo para módulos de acción
            details_filter: isDetailsModule ? (currentFilters.action || '') : '', // Solo para módulos de detalles
            target_id: currentFilters.target_id || '',
            search: currentFilters.search || ''
        };
        
        console.log('Enviando petición:', requestData); // Debug
        
        const response = await fetch('api/audit_dashboard_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(requestData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        console.log('Respuesta del servidor:', result); // Debug
        
        // Manejar la estructura real de la respuesta del servidor
        if (result.ok || result.success) {
            // Verificar que los datos existen
            const audits = result.audits || result.data?.audits;
            const total = result.total || result.data?.total;
            const chartData = result.chart_data || result.data?.chart_data;
            
            if (!audits) {
                throw new Error('El servidor no devolvió datos de auditorías válidos');
            }
            
            // Agregar nuevas filas a la tabla existente
            appendAuditsToTable(audits);
            
            // Actualizar información de paginación
            updatePaginationInfo(total, audits.length);
            
            // Si no hay más datos, deshabilitar el botón
            if (audits.length < 20) {
                loadMoreBtn.innerHTML = '<i class="bi bi-check-circle"></i> No hay más resultados';
                loadMoreBtn.disabled = true;
            } else {
                loadMoreBtn.innerHTML = originalText;
                loadMoreBtn.disabled = false;
            }
        } else {
            console.error('Error del servidor:', result);
            throw new Error(result.message || 'Error del servidor: ' + JSON.stringify(result));
        }
    } catch (error) {
        console.error('Error al cargar más auditorías:', error);
        showErrorMessage('auditTableContainer', 'Error al cargar más auditorías: ' + error.message);
        
        // Restaurar botón
        loadMoreBtn.innerHTML = originalText;
        loadMoreBtn.disabled = false;
        
        // Revertir offset
        currentOffset -= 20;
    }
}

// Función para agregar nuevas auditorías a la tabla existente
function appendAuditsToTable(audits) {
    const tbody = document.querySelector('#auditTableContainer .audit-table tbody');
    
    if (!tbody) {
        console.error('No se encontró el tbody de la tabla');
        return;
    }
    
    const newRowsHtml = audits.map(audit => `
        <tr>
            <td>${formatDateTime(audit.timestamp)}</td>
            <td>
                <span class="user-type-badge user-${audit.user_type}">${audit.user_type}</span>
                ${audit.user_display_name ? ` (${audit.user_display_name})` : ''}
            </td>
            <td>
                <span class="action-badge action-${getActionType(audit.action)}">${getActionType(audit.action)}</span>
            </td>
            <td>${audit.target_id || '-'}</td>
            <td>${audit.ip_address || '-'}</td>
            <td class="details-cell">
                <div class="details-content">
                    ${audit.formatted_details || audit.details || 'Sin detalles'}
                </div>
            </td>
        </tr>
    `).join('');
    
    tbody.insertAdjacentHTML('beforeend', newRowsHtml);
}

// Función para actualizar la información de paginación
function updatePaginationInfo(total, newCount) {
    const resultsInfo = document.getElementById('resultsInfo');
    const currentCount = document.querySelectorAll('#auditTableContainer .audit-table tbody tr').length;
    
    resultsInfo.textContent = `Mostrando ${currentCount} de ${total} resultados`;
}

// Función para obtener los nombres de kits de acabados dinámicamente
async function getAcabadosKits() {
    try {
        const response = await fetch('api/audit_dashboard_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                action: 'get_acabados_kits'
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        console.log('Respuesta de kits de acabados:', result); // Debug
        
        if (result.ok && result.kits && result.kits.length > 0) {
            console.log('Kits encontrados:', result.kits); // Debug adicional
            const mappedKits = result.kits.map(kit => ({
                value: kit.nombre,
                label: kit.nombre
            }));
            console.log('Kits mapeados:', mappedKits); // Debug adicional
            return mappedKits;
        } else {
            console.log('No se encontraron kits reales, usando opciones básicas');
            // Fallback: devolver opciones básicas si no hay datos
            return [
                { value: 'Full', label: 'Full' },
                { value: 'Standar', label: 'Standar' },
                { value: 'Premium', label: 'Premium' },
                { value: 'Básico', label: 'Básico' },
                { value: 'Deluxe', label: 'Deluxe' }
            ];
        }
    } catch (error) {
        console.error('Error al obtener kits de acabados:', error);
        // Fallback: devolver opciones básicas si hay error
        return [
            { value: 'Full', label: 'Full' },
            { value: 'Standar', label: 'Standar' },
            { value: 'Premium', label: 'Premium' },
            { value: 'Básico', label: 'Básico' },
            { value: 'Deluxe', label: 'Deluxe' }
        ];
    }
}

// Función para poblar las opciones del combo box de acciones según el módulo
async function populateActionFilter(module) {
    const actionSelect = document.getElementById('filterAction');
    const actionLabel = document.querySelector('label[for="filterAction"]');
    
    // Cambiar el label según el módulo
    if (module === 'acabados' || module === 'acceso_modulo') {
        actionLabel.textContent = 'Detalles:';
        actionSelect.innerHTML = '<option value="">Todos los detalles</option>';
    } else {
        actionLabel.textContent = 'Acción:';
        actionSelect.innerHTML = '<option value="">Todas las acciones</option>';
    }
    
    // Definir acciones específicas por módulo
    const moduleActions = {
        'autenticacion': [
            { value: 'LOGIN_SUCCESS', label: 'Inicio Sesión' },
            { value: 'LOGIN_FAILURE', label: 'Falló la Sesión' },
            { value: 'LOGOUT', label: 'Cerró Sesión' }
        ],
        'usuario': [
            { value: 'CREATE_USER', label: 'Crear Usuario' },
            { value: 'UPDATE_USER', label: 'Actualizar Usuario' },
            { value: 'DELETE_USER', label: 'Eliminar Usuario' },
            { value: 'VIEW_USER', label: 'Ver Usuario' }
        ],
        'cita': [
            { value: 'CREATE_CITA', label: 'Crear Cita' },
            { value: 'UPDATE_CITA', label: 'Actualizar Cita' },
            { value: 'DELETE_CITA', label: 'Eliminar Cita' },
            { value: 'CANCEL_CITA', label: 'Cancelar Cita' }
        ],
        'ctg': [
            { value: 'CREATE_CTG', label: 'Crear CTG' },
            { value: 'UPDATE_CTG', label: 'Actualizar CTG' },
            { value: 'DELETE_CTG', label: 'Eliminar CTG' },
            { value: 'VIEW_CTG', label: 'Ver CTG' }
        ],
        'pqr': [
            { value: 'CREATE_PQR', label: 'Crear PQR' },
            { value: 'UPDATE_PQR', label: 'Actualizar PQR' },
            { value: 'DELETE_PQR', label: 'Eliminar PQR' },
            { value: 'RESPOND_PQR', label: 'Responder PQR' }
        ],
        'acabados': [], // Se poblará dinámicamente
        'perfil': [
            { value: 'UPDATE_PROFILE', label: 'Actualizar Perfil' },
            { value: 'CHANGE_PASSWORD', label: 'Cambiar Contraseña' },
            { value: 'UPDATE_PICTURE', label: 'Actualizar Foto' }
        ],
        'notificaciones': [
            { value: 'SEND_NOTIFICATION', label: 'Enviar Notificación' },
            { value: 'MARK_READ', label: 'Marcar como Leído' },
            { value: 'DELETE_NOTIFICATION', label: 'Eliminar Notificación' }
        ],
        'acceso_modulo': [
            { value: 'Auditoria', label: 'Auditoria' },
            { value: 'Ver más', label: 'Ver más' },
            { value: 'Selección Acabados', label: 'Selección Acabados' },
            { value: 'Calendario Responsable', label: 'Calendario Responsable' },
            { value: 'Admin User', label: 'Admin User' },
            { value: 'PQR', label: 'PQR' },
            { value: 'CTG', label: 'CTG' },
            { value: 'Garantias', label: 'Garantias' },
            { value: 'Paleta Vegetal', label: 'Paleta Vegetal' },
            { value: 'MCM', label: 'MCM' },
            { value: 'Crédito Hipotecario', label: 'Crédito Hipotecario' }
        ]
    };
    
    // Obtener acciones para el módulo actual
    let actions = moduleActions[module] || [];
    
    // Si es el módulo de acabados, obtener los nombres de kits dinámicamente
    if (module === 'acabados') {
        console.log('Obteniendo kits para módulo acabados...'); // Debug
        actions = await getAcabadosKits();
        console.log('Actions obtenidas:', actions); // Debug
    }
    
    // Agregar opciones al select
    console.log('Agregando opciones al select:', actions); // Debug
    actions.forEach(action => {
        const option = document.createElement('option');
        option.value = action.value;
        option.textContent = action.label;
        actionSelect.appendChild(option);
    });
}

// Función para aplicar filtros
function applyFilters() {
    currentFilters = {
        date_from: document.getElementById('filterDateFrom').value,
        date_to: document.getElementById('filterDateTo').value,
        user_type: document.getElementById('filterUserType').value,
        action: document.getElementById('filterAction').value,
        target_id: document.getElementById('filterTargetId').value,
        search: document.getElementById('filterSearch').value
    };
    
    currentOffset = 0;
    // Limpiar cache cuando se aplican filtros
    cache.clearCache();
    loadModuleAudits();
}

// Función para limpiar filtros
function clearFilters() {
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterUserType').value = '';
    document.getElementById('filterAction').value = '';
    document.getElementById('filterTargetId').value = '';
    document.getElementById('filterSearch').value = '';
    
    currentFilters = {};
    currentOffset = 0;
    // Limpiar cache cuando se limpian filtros
    cache.clearCache();
    
    if (currentModule) {
        loadModuleAudits();
    } else {
        // Ocultar gráfico si no hay módulo seleccionado
        document.getElementById('chartSection').style.display = 'none';
    }
}

// Función para limpiar filtros sin recargar datos (usada internamente)
function resetFilters() {
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterUserType').value = '';
    document.getElementById('filterAction').value = '';
    document.getElementById('filterTargetId').value = '';
    document.getElementById('filterSearch').value = '';
    
    currentFilters = {};
    currentOffset = 0;
}

// Función para configurar event listeners
function setupEventListeners() {
    // Enter key en filtros
    const filterInputs = document.querySelectorAll('#filterTargetId, #filterSearch');
    filterInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    });
    
    // Change event en el select de acciones
    document.getElementById('filterAction').addEventListener('change', function() {
        applyFilters();
    });
}

// Funciones de utilidad
function formatDateTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getActionType(action) {
    if (action.includes('LOGIN')) return 'login';
    if (action.includes('CREATE')) return 'create';
    if (action.includes('UPDATE')) return 'update';
    if (action.includes('DELETE')) return 'delete';
    if (action.includes('ACCESS')) return 'access';
    return 'login';
}

function formatDetails(details) {
    if (!details) return '-';
    
    try {
        const parsed = typeof details === 'string' ? JSON.parse(details) : details;
        return Object.entries(parsed)
            .map(([key, value]) => `<strong>${key}:</strong> ${value}`)
            .join('<br>');
    } catch (e) {
        return details;
    }
}
</script>

</body>
</html>
