<?php
require_once __DIR__ . '/../config/db.php';
$db = DB::getDB();

// Validación de autenticación y rol
session_start();

// Obtener token desde POST (enviado por JavaScript)
$token = $_POST['token'] ?? null;
$user_id = $_GET['user_id'] ?? null;

if (!$token) {
    $showAccessDenied = true;
} else {
    try {
        $stmt = $db->prepare('SELECT id, nombre, correo FROM responsable WHERE token = ? AND estado = 1');
        $stmt->execute([$token]);
        $responsable = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$responsable) {
            $showAccessDenied = true;
        } else {
            $showAccessDenied = false;
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
  <title>Reporte de Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/style_users.css" rel="stylesheet">
  <link href="assets/css/style_report_user.css" rel="stylesheet">
</head>
<body>

<?php if ($showAccessDenied): ?>
<div class="access-denied">
  <i class="bi bi-shield-exclamation"></i>
  <h2>Acceso Denegado</h2>
  <p>No tienes permisos para acceder a esta página.<br>Solo los responsables pueden ver reportes de usuarios.</p>
  <button class="btn btn-primary mt-3" onclick="window.close()">
    <i class="bi bi-x-circle"></i> Cerrar
  </button>
</div>

<?php else: ?>
<div class="report-container">
  <div class="container">
    
    <!-- Header del Reporte -->
    <div class="report-card">
      <div class="report-header">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <button class="btn btn-outline-light" onclick="window.close()" style="border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
              <i class="bi bi-arrow-left"></i>
            </button>
            <div>
              <h1 class="mb-0"><i class="bi bi-graph-up me-2"></i>Reporte de Usuario</h1>
              <p class="mb-0 opacity-75">Análisis completo de actividad del cliente</p>
            </div>
          </div>
          <div>
            <button class="btn btn-light" onclick="window.print()" style="border-radius: 8px;">
              <i class="bi bi-printer me-2"></i>Imprimir
            </button>
          </div>
        </div>
      </div>
      
      <div class="p-4">
        <div id="loadingState" class="loading-spinner">
          <div class="text-center">
            <div class="spinner-border text-success" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Generando reporte...</p>
          </div>
        </div>
        
        <div id="errorState" class="error-message" style="display: none;">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <span id="errorText">Error al cargar el reporte</span>
        </div>
        
        <div id="reportContent" style="display: none;">
          
          <!-- Información del Usuario -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="report-card">
                <div class="p-4">
                  <h3 class="section-title">
                    <i class="bi bi-person-circle"></i>
                    Información del Cliente
                  </h3>
                  <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                      <img id="userPhoto" src="" alt="Foto" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                    </div>
                    <div class="col-md-10">
                      <div class="row">
                        <div class="col-md-6">
                          <h4 id="userName" class="mb-1"></h4>
                          <p id="userRole" class="text-muted mb-1"></p>
                          <p id="userEmail" class="mb-1"><i class="bi bi-envelope me-2"></i></p>
                        </div>
                        <div class="col-md-6">
                          <p id="userCedula" class="mb-1"><i class="bi bi-card-text me-2"></i></p>
                          <p id="userTelefono" class="mb-1"><i class="bi bi-telephone me-2"></i></p>
                          <p id="userRegistro" class="mb-1"><i class="bi bi-calendar me-2"></i></p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Métricas Principales -->
          <div class="row mb-4">
            <div class="col-12">
              <h3 class="section-title">
                <i class="bi bi-bar-chart-line"></i>
                Resumen de Actividad
              </h3>
            </div>
          </div>
          
          <div class="row mb-4">
            <!-- Propiedades -->
            <div class="col-lg-3 col-md-6 mb-3">
              <div class="metric-card clickable" onclick="togglePropiedadesAccordion()" style="cursor: pointer;">
                <i class="bi bi-house metric-icon"></i>
                <div id="totalPropiedades" class="metric-number">-</div>
                <div class="metric-label">Propiedades Totales</div>
                <small class="text-muted" id="propiedadesToggleText">Click para ver detalles</small>
              </div>
            </div>
            
            <!-- CTG -->
            <div class="col-lg-3 col-md-6 mb-3">
              <div class="metric-card">
                <i class="bi bi-tools metric-icon"></i>
                <div id="totalCTG" class="metric-number">-</div>
                <div class="metric-label">Solicitudes CTG</div>
              </div>
            </div>
            
            <!-- PQR -->
            <div class="col-lg-3 col-md-6 mb-3">
              <div class="metric-card">
                <i class="bi bi-chat-dots metric-icon"></i>
                <div id="totalPQR" class="metric-number">-</div>
                <div class="metric-label">Solicitudes PQR</div>
              </div>
            </div>
            
            <!-- Citas -->
            <div class="col-lg-3 col-md-6 mb-3">
              <div class="metric-card">
                <i class="bi bi-calendar-check metric-icon"></i>
                <div id="totalCitas" class="metric-number">-</div>
                <div class="metric-label">Citas Programadas</div>
              </div>
            </div>
          </div>
          
          <!-- Acordeón de Propiedades Detalladas -->
          <div class="row mb-4" id="propiedadesAccordion" style="display: none;">
            <div class="col-12">
              <div class="report-card">
                <div class="p-4">
                  <h4 class="section-title">
                    <i class="bi bi-house-door"></i>
                    Detalles de Propiedades - 
                  </h4>
                  <div id="propiedadesDetalle">
                    <!-- Se llena dinámicamente -->
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Métricas Detalladas -->
          <div class="row mb-4">
            <div class="col-md-6 mb-4">
              <div class="report-card">
                <div class="p-4">
                  <h4 class="section-title">
                    <i class="bi bi-clipboard-data"></i>
                    Estados de Solicitudes
                  </h4>
                  <div class="row">
                    <div class="col-6 mb-3">
                      <div class="d-flex align-items-center">
                        <div class="progress-ring me-3" style="position: relative;">
                          <svg class="progress-ring" width="60" height="60">
                            <circle class="progress-ring-circle" stroke-dasharray="0" r="25" cx="30" cy="30"></circle>
                          </svg>
                          <div class="progress-text" id="ctgResueltasPercent">0%</div>
                        </div>
                        <div>
                          <div class="fw-bold">CTG Resueltas</div>
                          <div class="text-muted small" id="ctgResueltas">0 de 0</div>
                        </div>
                      </div>
                    </div>
                    <div class="col-6 mb-3">
                      <div class="d-flex align-items-center">
                        <div class="progress-ring me-3" style="position: relative;">
                          <svg class="progress-ring" width="60" height="60">
                            <circle class="progress-ring-circle" stroke-dasharray="0" r="25" cx="30" cy="30"></circle>
                          </svg>
                          <div class="progress-text" id="pqrResueltasPercent">0%</div>
                        </div>
                        <div>
                          <div class="fw-bold">PQR Resueltas</div>
                          <div class="text-muted small" id="pqrResueltas">0 de 0</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-md-6 mb-4">
              <div class="report-card">
                <div class="p-4">
                  <h4 class="section-title">
                    <i class="bi bi-chat-text"></i>
                    Actividad de Mensajería
                  </h4>
                  <div class="row text-center">
                    <div class="col-4">
                      <div class="metric-number" style="font-size: 1.8rem;" id="mensajesCTG">-</div>
                      <div class="metric-label">Mensajes CTG</div>
                    </div>
                    <div class="col-4">
                      <div class="metric-number" style="font-size: 1.8rem;" id="mensajesPQR">-</div>
                      <div class="metric-label">Mensajes PQR</div>
                    </div>
                    <div class="col-4">
                      <div class="metric-number" style="font-size: 1.8rem;" id="mensajesTotal">-</div>
                      <div class="metric-label">Total Mensajes</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          

                  <!-- Información de Login -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="report-card">
                <div class="p-4">
                  <h4 class="section-title">
                    <i class="bi bi-person-check"></i>
                    Historial de Acceso
                  </h4>
                  <div class="row text-center">
                    <div class="col-md-4 mb-3">
                      <div class="metric-number" style="font-size: 1.8rem;" id="totalLogins">-</div>
                      <div class="metric-label">Total de Logins</div>
                    </div>
                    <div class="col-md-4 mb-3">
                      <div class="metric-number" style="font-size: 1.8rem;" id="loginsExitosos">-</div>
                      <div class="metric-label">Logins Exitosos</div>
                    </div>
                    <div class="col-md-4 mb-3">
                      <div class="metric-number" style="font-size: 1.8rem;" id="ultimoLogin">-</div>
                      <div class="metric-label">Último Login</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>



          <!-- Estadísticas de Tiempo -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="report-card">
                <div class="p-4">
                  <h4 class="section-title">
                    <i class="bi bi-speedometer2"></i>
                    Tiempos de Respuesta
                  </h4>
                  <div class="row text-center">
                    <div class="col-md-6 mb-3">
                      <div class="metric-number" style="font-size: 2rem;" id="tiempoPromedioCTG">-</div>
                      <div class="metric-label">Tiempo Promedio CTG (horas)</div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <div class="metric-number" style="font-size: 2rem;" id="tiempoPromedioPQR">-</div>
                      <div class="metric-label">Tiempo Promedio PQR (horas)</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <!-- Actividad Reciente -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="report-card">
                <div class="p-4">
                  <h4 class="section-title">
                    <i class="bi bi-clock-history"></i>
                    Actividad Reciente
                  </h4>
                  <div id="actividadReciente">
                    <!-- Se llena dinámicamente -->
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Estadísticas de Tiempo -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="report-card">
                <div class="p-4">
                  <h4 class="section-title">
                    <i class="bi bi-speedometer2"></i>
                    Tiempos de Respuesta
                  </h4>
                  <div class="row text-center">
                    <div class="col-md-6 mb-3">
                      <div class="metric-number" style="font-size: 2rem;" id="tiempoPromedioCTG">-</div>
                      <div class="metric-label">Tiempo Promedio CTG (horas)</div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <div class="metric-number" style="font-size: 2rem;" id="tiempoPromedioPQR">-</div>
                      <div class="metric-label">Tiempo Promedio PQR (horas)</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          
          <!-- Pie de página -->
          <div class="text-center text-muted mb-4">
            <small>
              <i class="bi bi-calendar me-1"></i>
              Reporte generado el <span id="fechaGeneracion"></span>
            </small>
          </div>
          
        </div>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
console.log('Script cargado - Iniciando verificación de autenticación');

// Verificar autenticación inmediatamente
const token = localStorage.getItem('cs_token');
const userId = new URLSearchParams(window.location.search).get('user_id');

console.log('Token encontrado:', token ? 'SÍ' : 'NO');
console.log('User ID:', userId);

if (!token) {
    console.log('No hay token, redirigiendo al login');
    window.location.href = 'login_front.php';
} else if (!userId) {
    console.log('No hay user_id en la URL');
    showError('ID de usuario no especificado');
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
        document.body.innerHTML = html;
        
        // Reinicializar Bootstrap
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
        // Cargar datos del reporte
        loadReportData(userId);
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        showError('Error de autenticación');
    });
}

function showError(message) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('errorText').textContent = message;
}

function loadReportData(userId) {
    const token = localStorage.getItem('cs_token');
    
    fetch(`../api/user_report_data.php?user_id=${userId}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            displayReport(data);
        } else {
            showError(data.mensaje || 'Error al cargar el reporte');
        }
    })
    .catch(error => {
        console.error('Error al cargar reporte:', error);
        showError('Error al cargar los datos del reporte');
    });
}

function displayReport(data) {
    const usuario = data.usuario;
    const metricas = data.metricas;
    
    // Ocultar loading y mostrar contenido
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('reportContent').style.display = 'block';
    
    // Información del usuario
    document.getElementById('userPhoto').src = usuario.url_foto_perfil || 'https://via.placeholder.com/80';
    document.getElementById('userName').textContent = `${usuario.nombres} ${usuario.apellidos}`;
    document.getElementById('userRole').textContent = usuario.rol_nombre || 'Usuario';
    document.getElementById('userEmail').textContent = usuario.correo;
    document.getElementById('userCedula').textContent = usuario.cedula || 'No especificada';
    document.getElementById('userTelefono').textContent = usuario.telefono || 'No especificado';
    document.getElementById('userRegistro').textContent = `Miembro desde: ${new Date(usuario.fecha_insertado).toLocaleDateString('es-ES')}`;
    
    // Métricas principales
    document.getElementById('totalPropiedades').textContent = metricas.propiedades.total_propiedades || 0;
    document.getElementById('totalCTG').textContent = metricas.ctg.total_ctg || 0;
    document.getElementById('totalPQR').textContent = metricas.pqr.total_pqr || 0;
    document.getElementById('totalCitas').textContent = metricas.citas.total_citas || 0;
    
    // Mensajes
    document.getElementById('mensajesCTG').textContent = metricas.mensajes.ctg || 0;
    document.getElementById('mensajesPQR').textContent = metricas.mensajes.pqr || 0;
    document.getElementById('mensajesTotal').textContent = metricas.mensajes.total || 0;
    
    // Estados de solicitudes con círculos de progreso
    updateProgressCircle('ctgResueltasPercent', 'ctgResueltas', metricas.ctg.ctg_resueltas || 0, metricas.ctg.total_ctg || 0);
    updateProgressCircle('pqrResueltasPercent', 'pqrResueltas', metricas.pqr.pqr_resueltas || 0, metricas.pqr.total_pqr || 0);
    
    // Tiempos promedio
    document.getElementById('tiempoPromedioCTG').textContent = metricas.tiempo_promedio.tiempo_promedio_resolucion_ctg_horas ? 
        Math.round(metricas.tiempo_promedio.tiempo_promedio_resolucion_ctg_horas) : '-';
    document.getElementById('tiempoPromedioPQR').textContent = metricas.tiempo_promedio.tiempo_promedio_resolucion_pqr_horas ? 
        Math.round(metricas.tiempo_promedio.tiempo_promedio_resolucion_pqr_horas) : '-';
    
    // Login stats
    document.getElementById('totalLogins').textContent = metricas.login.total_logins || 0;
    document.getElementById('loginsExitosos').textContent = metricas.login.logins_exitosos || 0;
    document.getElementById('ultimoLogin').textContent = metricas.login.ultimo_login ? 
        new Date(metricas.login.ultimo_login).toLocaleDateString('es-ES') : 'Nunca';
    
    // Actividad reciente
    displayRecentActivity(data.actividad_reciente);
    
    // Fecha de generación
    document.getElementById('fechaGeneracion').textContent = new Date(data.fecha_generacion).toLocaleString('es-ES');
}

function updateProgressCircle(percentId, textId, resolved, total) {
    const percent = total > 0 ? Math.round((resolved / total) * 100) : 0;
    document.getElementById(percentId).textContent = `${percent}%`;
    document.getElementById(textId).textContent = `${resolved} de ${total}`;
    
    // Actualizar círculo de progreso
    const circle = document.querySelector(`#${percentId}`).parentElement.querySelector('circle');
    const radius = 25;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (percent / 100) * circumference;
    circle.style.strokeDasharray = `${circumference} ${circumference}`;
    circle.style.strokeDashoffset = offset;
}

function displayRecentActivity(activities) {
    const container = document.getElementById('actividadReciente');
    
    if (!activities || activities.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No hay actividad reciente</p>';
        return;
    }
    
    container.innerHTML = activities.map(activity => {
        const fecha = new Date(activity.fecha).toLocaleString('es-ES');
        const tipoClass = activity.tipo.toLowerCase();
        
        return `
            <div class="activity-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <span class="activity-type ${tipoClass}">${activity.tipo}</span>
                        <p class="mb-1 mt-2">${activity.descripcion || 'Sin descripción'}</p>
                        <small class="text-muted"><i class="bi bi-clock me-1"></i>${fecha}</small>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Variables globales para el acordeón
let propiedadesAccordionOpen = false;
let propiedadesData = null;

function togglePropiedadesAccordion() {
    const accordion = document.getElementById('propiedadesAccordion');
    const toggleText = document.getElementById('propiedadesToggleText');
    
    if (propiedadesAccordionOpen) {
        accordion.style.display = 'none';
        toggleText.textContent = 'Click para ver detalles';
        propiedadesAccordionOpen = false;
    } else {
        accordion.style.display = 'block';
        toggleText.textContent = 'Click para ocultar';
        propiedadesAccordionOpen = true;
        
        // Cargar datos de propiedades si no están cargados
        if (!propiedadesData) {
            loadPropiedadesDetalle();
        }
    }
}

function loadPropiedadesDetalle() {
    const token = localStorage.getItem('cs_token');
    const userId = new URLSearchParams(window.location.search).get('user_id');
    
    fetch(`../api/user_report_data.php?user_id=${userId}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.propiedades_detalle) {
            propiedadesData = data.propiedades_detalle;
            displayPropiedadesDetalle(data.propiedades_detalle);
        }
    })
    .catch(error => {
        console.error('Error al cargar propiedades:', error);
    });
}

function displayPropiedadesDetalle(propiedades) {
    const container = document.getElementById('propiedadesDetalle');
    
    if (!propiedades || propiedades.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No se encontraron propiedades</p>';
        return;
    }
    
    container.innerHTML = propiedades.map(propiedad => {
        const fechaCompra = propiedad.fecha_compra ? new Date(propiedad.fecha_compra).toLocaleDateString('es-ES') : 'No especificada';
        const fechaEntrega = propiedad.fecha_entrega ? new Date(propiedad.fecha_entrega).toLocaleDateString('es-ES') : 'No especificada';
        const fechaHipotecario = propiedad.fecha_hipotecario ? new Date(propiedad.fecha_hipotecario).toLocaleDateString('es-ES') : 'No especificada';
        
        const ubicacion = `${propiedad.manzana ? `Mz ${propiedad.manzana}` : ''} ${propiedad.villa ? `Villa ${propiedad.villa}` : ''} ${propiedad.solar ? `Solar ${propiedad.solar}` : ''}`.trim();
        
        return `
            <div class="propiedad-card">
                <div class="propiedad-header">
                    <h5 class="propiedad-title">
                        <i class="bi bi-house me-2"></i>
                        ${propiedad.tipo_propiedad || 'Propiedad'} - ${ubicacion || 'Ubicación no especificada'}
                    </h5>
                    <span class="propiedad-badge">${propiedad.estado_propiedad || 'Estado no especificado'}</span>
                </div>
                
                <div class="propiedad-info-grid">
                    <div class="info-item">
                        <div class="info-label">Urbanización</div>
                        <div class="info-value">${propiedad.urbanizacion || 'No especificada'}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Tipo de Propiedad</div>
                        <div class="info-value">${propiedad.tipo_propiedad || 'No especificado'}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Estado Actual</div>
                        <div class="info-value">${propiedad.estado_propiedad || 'No especificado'}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Etapa de Construcción</div>
                        <div class="info-value">${propiedad.etapa_construccion || 'No especificada'}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Fecha de Compra</div>
                        <div class="info-value">${fechaCompra}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Fecha de Entrega</div>
                        <div class="info-value">${fechaEntrega}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Fecha Hipotecario</div>
                        <div class="info-value">${fechaHipotecario}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Ubicación</div>
                        <div class="info-value">${ubicacion || 'No especificada'}</div>
                    </div>
                </div>
                
                ${propiedad.etapa_construccion && propiedad.porcentaje_etapa ? `
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="info-label">Progreso de Construcción</span>
                            <span class="info-value">${propiedad.porcentaje_etapa}%</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: ${propiedad.porcentaje_etapa}%"></div>
                        </div>
                    </div>
                ` : ''}
                
                ${propiedad.acabado_kit_nombre ? `
                    <div class="mb-3">
                        <div class="section-subtitle">
                            <i class="bi bi-palette"></i>
                            Kit de Acabados Seleccionado
                        </div>
                        <div class="info-item">
                            <div class="info-label">Kit de Acabados</div>
                            <div class="info-value">${propiedad.acabado_kit_nombre}</div>
                        </div>
                        ${propiedad.acabado_kit_costo ? `
                            <div class="info-item">
                                <div class="info-label">Costo del Kit</div>
                                <div class="info-value">$${Number(propiedad.acabado_kit_costo).toLocaleString('es-ES')}</div>
                            </div>
                        ` : ''}
                        ${propiedad.acabado_color_seleccionado ? `
                            <div class="info-item">
                                <div class="info-label">Color Seleccionado</div>
                                <div class="info-value">${propiedad.acabado_color_seleccionado}</div>
                            </div>
                        ` : ''}
                    </div>
                ` : ''}
                
                ${propiedad.paquetes_adicionales && propiedad.paquetes_adicionales.length > 0 ? `
                    <div class="mb-3">
                        <div class="section-subtitle">
                            <i class="bi bi-plus-square"></i>
                            Paquetes Adicionales
                        </div>
                        ${propiedad.paquetes_adicionales.map(paquete => `
                            <div class="paquete-item">
                                <div class="paquete-info">
                                    <h6>${paquete.nombre}</h6>
                                    ${paquete.descripcion ? `<small class="text-muted">${paquete.descripcion}</small>` : ''}
                                </div>
                                <div class="paquete-precio">$${Number(paquete.precio).toLocaleString('es-ES')}</div>
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
                
                ${propiedad.progreso_construccion && propiedad.progreso_construccion.length > 0 ? `
                    <div class="mb-3">
                        <div class="section-subtitle">
                            <i class="bi bi-camera"></i>
                            Progreso de Construcción
                        </div>
                        ${propiedad.progreso_construccion.map(progreso => `
                            <div class="progreso-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">${progreso.etapa_nombre}</h6>
                                    <span class="badge bg-success">${progreso.porcentaje}%</span>
                                </div>
                                ${progreso.descripcion ? `<p class="mb-2">${progreso.descripcion}</p>` : ''}
                                <div class="progreso-fecha">
                                    <i class="bi bi-calendar me-1"></i>
                                    ${new Date(progreso.fecha_registro).toLocaleDateString('es-ES')}
                                </div>
                                ${progreso.url_imagen ? `
                                    <img src="${progreso.url_imagen}" alt="Progreso" class="progreso-imagen" 
                                         style="max-width: 200px; height: auto; cursor: pointer;" 
                                         onclick="window.open('${progreso.url_imagen}', '_blank')">
                                ` : ''}
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}
</script>

</body>
</html>
