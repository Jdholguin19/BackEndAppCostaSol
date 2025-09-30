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
          <i class="bi bi-pie-chart"></i>
          Distribución de Auditorías
        </h4>
        <div class="chart-container">
          <canvas id="auditChart"></canvas>
        </div>
        <div class="chart-legend" id="chartLegend"></div>
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
            <input type="text" id="filterAction" class="form-control" placeholder="Ej: LOGIN_SUCCESS">
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
function showModuleDetail(resource) {
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
    clearFilters();
    
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
    
    // Verificar cache primero (solo si no hay filtros activos)
    const hasActiveFilters = Object.values(currentFilters).some(value => value !== '');
    if (!hasActiveFilters) {
        const cachedData = cache.getModuleAudits(currentModule, currentFilters);
        if (cachedData) {
            console.log('Usando datos de módulo desde cache');
            renderModuleAudits(cachedData.audits, cachedData.total, cachedData.chart_data);
            return;
        }
    }
    
    showLoadingIndicator('auditTableContainer', `Cargando datos de ${moduleName}...`);
    
    try {
        const requestData = {
            action: 'get_module_audits',
            resource: currentModule,
            offset: currentOffset,
            limit: 20,
            date_from: currentFilters.date_from || '',
            date_to: currentFilters.date_to || '',
            user_type: currentFilters.user_type || '',
            action_filter: currentFilters.action || '', // Renombrar para evitar conflicto
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

// Función para crear el gráfico de pastel
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
        // EXCLUIR DELETE_CITA del gráfico de pastel
        const propositoCounts = {};
        audits.forEach(audit => {
            const detail = audit.formatted_details || audit.details || 'Sin detalles';
            
            // Saltar solo DELETE_CITA (acción de eliminación) para el gráfico
            // Pero permitir que las citas eliminadas aparezcan si fueron creadas originalmente
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
    
    // Crear el gráfico
    const ctx = document.getElementById('auditChart').getContext('2d');

    // Plugin para dibujar líneas guía y etiquetas manualmente
    const leaderLinesPlugin = {
        id: 'leaderLines',
        afterDatasetsDraw(chart, args, pluginOptions) {
            const {ctx} = chart;
            const meta = chart.getDatasetMeta(0);
            if (!meta || !meta.data) return;

            ctx.save();
            
            // Dibujar líneas guía
            ctx.lineWidth = 1.5;
            ctx.strokeStyle = '#6c757d';

            meta.data.forEach((arc, index) => {
                // Obtener propiedades del arco de forma segura en Chart.js 4
                const props = arc.getProps(['x','y','startAngle','endAngle','outerRadius'], true);
                const angle = (props.startAngle + props.endAngle) / 2;
                const label = chart.data.labels[index];
                const value = chart.data.datasets[0].data[index];
                const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                const percentage = (value / total) * 100;

                // Punto inicial en el borde del pastel
                const r1 = props.outerRadius * 0.98;
                const x1 = props.x + Math.cos(angle) * r1;
                const y1 = props.y + Math.sin(angle) * r1;

                // Punto final fuera del pastel
                const r2 = props.outerRadius + 60;
                const x2 = props.x + Math.cos(angle) * r2;
                const y2 = props.y + Math.sin(angle) * r2;

                // Segmento horizontal
                const horizontal = 25 * (Math.cos(angle) >= 0 ? 1 : -1);
                const x3 = x2 + horizontal;

                // Dibujar línea guía
                ctx.beginPath();
                ctx.moveTo(x1, y1);
                ctx.lineTo(x2, y2);
                ctx.lineTo(x3, y2);
                ctx.stroke();

                // Dibujar etiqueta manualmente
                const displayLabel = percentage < 3 && label.length > 12 ? label.slice(0, 12) + '…' : label;
                const fontSize = percentage < 4 ? 10 : 11;
                
                ctx.font = `bold ${fontSize}px Arial`;
                ctx.fillStyle = '#333';
                ctx.textAlign = Math.cos(angle) >= 0 ? 'left' : 'right';
                ctx.textBaseline = 'middle';
                
                // Fondo de la etiqueta
                const textMetrics = ctx.measureText(displayLabel);
                const padding = 10; // Más padding para que el texto no toque los bordes
                const labelWidth = textMetrics.width + (padding * 2);
                const labelHeight = fontSize + (padding * 2);
                
                const labelX = x3 + (Math.cos(angle) >= 0 ? 5 : -labelWidth - 5);
                const labelY = y2;
                
                // Dibujar fondo
                ctx.fillStyle = 'rgba(255, 255, 255, 0.95)';
                ctx.strokeStyle = '#ccc';
                ctx.lineWidth = 1;
                ctx.fillRect(labelX, labelY - labelHeight/2, labelWidth, labelHeight);
                ctx.strokeRect(labelX, labelY - labelHeight/2, labelWidth, labelHeight);
                
                // Dibujar texto
                ctx.fillStyle = '#333';
                ctx.fillText(displayLabel, labelX + padding, labelY);
            });

            ctx.restore();
        }
    };

    auditChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: '#fff',
                borderWidth: 2,
                offset: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    right: 180, // Reducido un poco
                    left: 200, // Reducido un poco
                    top: 40,
                    bottom: 40
                }
            },
            elements: {
                arc: {
                    borderWidth: 2,
                    borderColor: '#fff'
                }
            },
            cutout: '0%',
            radius: '65%', // Un poco más pequeño
            plugins: {
                legend: {
                    display: false // Usaremos nuestra propia leyenda
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    display: false // Desactivar datalabels del plugin
                }
            }
        },
        plugins: [leaderLinesPlugin]
    });
    
    // Crear leyenda personalizada
    createChartLegend(labels, data, colors.slice(0, labels.length));
}

// Función para crear la leyenda del gráfico
function createChartLegend(labels, data, colors) {
    const legendContainer = document.getElementById('chartLegend');
    const total = data.reduce((a, b) => a + b, 0);
    
    const legendHtml = labels.map((label, index) => {
        const count = data[index];
        const percentage = ((count / total) * 100).toFixed(1);
        return `
            <div class="legend-item">
                <div class="legend-color" style="background-color: ${colors[index]}"></div>
                <span class="legend-text">${label}</span>
                <span class="legend-count">${count} (${percentage}%)</span>
            </div>
        `;
    }).join('');
    
    legendContainer.innerHTML = legendHtml;
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
        chartTitle.innerHTML = '<i class="bi bi-pie-chart"></i> Distribución por Módulos Accedidos';
    } else if (currentModule === 'acabados') {
        chartTitle.innerHTML = '<i class="bi bi-pie-chart"></i> Distribución por Kits Seleccionados';
    } else if (currentModule === 'cita') {
        chartTitle.innerHTML = '<i class="bi bi-pie-chart"></i> Distribución por Propósitos de Citas';
    } else {
        chartTitle.innerHTML = '<i class="bi bi-pie-chart"></i> Distribución de Auditorías';
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
function loadMoreAudits() {
    currentOffset += 20;
    loadModuleAudits();
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
    
    if (currentModule) {
        loadModuleAudits();
    } else {
        // Ocultar gráfico si no hay módulo seleccionado
        document.getElementById('chartSection').style.display = 'none';
    }
}

// Función para configurar event listeners
function setupEventListeners() {
    // Enter key en filtros
    const filterInputs = document.querySelectorAll('#filterAction, #filterTargetId, #filterSearch');
    filterInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
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
