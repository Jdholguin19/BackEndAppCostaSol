<?php
require_once __DIR__ . '/../../../config/db.php';

// Validación básica - la autenticación real se hace via JavaScript con el token
session_start();
$user_id = $_GET['user_id'] ?? null;

// Por ahora no validamos el token aquí, se hace en JavaScript
$showAccessDenied = false;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil de Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --chat-primary: #1677ff;
      --chat-bg: #f5f7fb;
      --chat-panel: #ffffff;
      --chat-text: #1f2937;
      --chat-muted: #6b7280;
      --chat-border: #e5e7eb;
      --chat-success: #10b981;
      --chat-warning: #f59e0b;
      --chat-danger: #ef4444;
    }

    body {
      background: var(--chat-bg);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      margin: 0;
      padding: 0;
    }

    .profile-container {
      min-height: 100vh;
      padding: 20px;
    }

    .profile-header {
      background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
      color: white;
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }

    .profile-card {
      background: var(--chat-panel);
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 20px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.05);
      border: 1px solid var(--chat-border);
    }

    .profile-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: var(--chat-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 32px;
      font-weight: 600;
      margin-bottom: 16px;
    }

    .metric-card {
      background: white;
      border-radius: 12px;
      padding: 24px;
      text-align: center;
      border: 1px solid var(--chat-border);
      transition: all 0.2s ease;
      height: 100%;
      position: relative;
      cursor: pointer;
    }

    .metric-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }

    .metric-details {
      background: white;
      border: 1px solid var(--chat-border);
      border-top: none;
      border-radius: 0 0 12px 12px;
      margin-top: -1px;
      animation: slideDown 0.3s ease-out;
    }

    /* Estilos para métricas expandidas */
    .metrics-row.expanded .metric-column:not(.expanded-column) {
      display: none;
    }

    .metrics-row.expanded .expanded-column {
      flex: 0 0 100%;
      max-width: 100%;
    }

    .expanded-column .metric-details {
      border-radius: 0 0 12px 12px;
      width: 100%;
      max-width: 100%;
    }

    .expanded-column .metric-card {
      border-radius: 12px 12px 0 0;
    }

    /* Ocultar secciones cuando hay métricas expandidas */
    .content-container.metrics-expanded .activity-section {
      display: none;
    }

    /* Limitar altura de detalles expandidos */
    .expanded-column .details-content {
      max-height: 500px;
      overflow-y: auto;
    }

    .details-content {
      padding: 16px;
      max-height: 400px;
      overflow-y: auto;
    }

    .loading-details {
      text-align: center;
      padding: 20px;
      color: var(--chat-muted);
    }

    .detail-item {
      padding: 12px 0;
      border-bottom: 1px solid #f3f4f6;
    }

    .detail-item:last-child {
      border-bottom: none;
    }

    .detail-title {
      font-weight: 600;
      color: var(--chat-text);
      margin-bottom: 4px;
    }

    .detail-subtitle {
      font-size: 12px;
      color: var(--chat-muted);
      margin-bottom: 8px;
    }

    .detail-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 14px;
    }

    .detail-badge {
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 500;
    }

    .badge-success {
      background-color: #d4edda;
      color: #155724;
    }

    .badge-warning {
      background-color: #fff3cd;
      color: #856404;
    }

    .badge-danger {
      background-color: #f8d7da;
      color: #721c24;
    }

    .badge-info {
      background-color: #d1ecf1;
      color: #0c5460;
    }

    .expand-icon {
      transition: transform 0.3s ease;
    }

    .metric-card.expanded .expand-icon {
      transform: rotate(180deg);
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .metric-number {
      font-size: 2rem;
      font-weight: 700;
      color: var(--chat-primary);
      margin-bottom: 8px;
    }

    .metric-label {
      color: var(--chat-muted);
      font-size: 14px;
      font-weight: 500;
    }

    .metric-icon {
      font-size: 24px;
      color: var(--chat-primary);
      margin-bottom: 12px;
    }

    .section-title {
      color: var(--chat-text);
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .info-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid #f3f4f6;
    }

    .info-item:last-child {
      border-bottom: none;
    }

    .info-icon {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      background: var(--chat-bg);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--chat-primary);
    }

    .activity-item {
      padding: 16px;
      border-left: 4px solid var(--chat-primary);
      background: #f8fafc;
      border-radius: 0 8px 8px 0;
      margin-bottom: 12px;
    }

    .activity-date {
      font-size: 12px;
      color: var(--chat-muted);
      margin-bottom: 4px;
    }

    .activity-title {
      font-weight: 600;
      color: var(--chat-text);
      margin-bottom: 4px;
    }

    .activity-desc {
      font-size: 14px;
      color: var(--chat-muted);
    }

    .back-btn {
      background: rgba(255,255,255,0.2);
      border: 1px solid rgba(255,255,255,0.3);
      color: white;
      border-radius: 8px;
      padding: 8px 16px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
    }

    .back-btn:hover {
      background: rgba(255,255,255,0.3);
      color: white;
      text-decoration: none;
    }

    .loading-spinner {
      text-align: center;
      padding: 40px;
    }

    .error-message {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #dc2626;
      padding: 16px;
      border-radius: 8px;
      text-align: center;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .profile-container {
        padding: 12px;
      }
      
      .profile-header {
        padding: 16px;
        margin-bottom: 16px;
      }
      
      .profile-card {
        padding: 16px;
        margin-bottom: 16px;
      }
      
      .profile-avatar {
        width: 60px;
        height: 60px;
        font-size: 24px;
      }
      
      .metric-number {
        font-size: 1.5rem;
      }
      
      .section-title {
        font-size: 16px;
      }
    }

    @media (max-width: 576px) {
      .profile-header h1 {
        font-size: 1.5rem;
      }
      
      .metric-card {
        padding: 16px;
      }
      
      .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }
    }
  </style>
</head>
<body>

<?php if ($showAccessDenied): ?>
<div class="profile-container">
  <div class="profile-card text-center">
    <i class="bi bi-shield-exclamation" style="font-size: 48px; color: var(--chat-danger); margin-bottom: 16px;"></i>
    <h2>Acceso Denegado</h2>
    <p class="text-muted">No tienes permisos para acceder a esta página.<br>Solo los responsables pueden ver perfiles de usuarios.</p>
    <button class="btn btn-primary mt-3" onclick="window.close()">
      <i class="bi bi-x-circle"></i> Cerrar
    </button>
  </div>
</div>

<?php else: ?>
<div class="profile-container">
  <!-- Header del Perfil -->
  <div class="profile-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <a href="#" class="back-btn" onclick="window.close(); return false;">
          <i class="bi bi-arrow-left"></i>
          Volver
        </a>
        <div>
          <h1 class="mb-1"><i class="bi bi-person-circle me-2"></i>Perfil de Usuario</h1>
          <p class="mb-0 opacity-75">Información detallada del cliente</p>
        </div>
      </div>
    </div>
  </div>

  <div class="content-container">
    <!-- Estados de Carga -->
    <div id="loadingState" class="loading-spinner">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-2 text-muted">Cargando perfil del usuario...</p>
    </div>

    <div id="errorState" class="error-message" style="display: none;">
      <i class="bi bi-exclamation-triangle me-2"></i>
      <span id="errorText">Error al cargar el perfil</span>
    </div>

    <div id="profileContent" style="display: none;">
    <!-- Información Personal -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="profile-card">
          <h3 class="section-title">
            <i class="bi bi-person-fill"></i>
            Información Personal
          </h3>
          <div class="row align-items-center">
            <div class="col-md-2 text-center">
              <div id="userAvatar" class="profile-avatar mx-auto">
                <!-- Avatar generado dinámicamente -->
              </div>
            </div>
            <div class="col-md-10">
              <div class="row">
                <div class="col-md-6">
                  <div class="info-item">
                    <div class="info-icon">
                      <i class="bi bi-person"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">Nombre Completo</div>
                      <div id="userName" class="text-muted">-</div>
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-icon">
                      <i class="bi bi-envelope"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">Correo Electrónico</div>
                      <div id="userEmail" class="text-muted">-</div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-item">
                    <div class="info-icon">
                      <i class="bi bi-card-text"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">Cédula</div>
                      <div id="userCedula" class="text-muted">-</div>
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-icon">
                      <i class="bi bi-telephone"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">Teléfono</div>
                      <div id="userTelefono" class="text-muted">-</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notas y Contexto del Cliente -->
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="profile-card">
          <h3 class="section-title">
            <i class="bi bi-journal-text"></i>
            Notas de Cliente
          </h3>
          <div class="notes-container">
            <textarea 
              id="clientNotes" 
              class="form-control" 
              placeholder="Escriba aquí las notas del cliente..."
              rows="8"
              style="resize: vertical; min-height: 200px; border: 1px solid var(--chat-border); border-radius: 8px; padding: 12px;"
            ></textarea>
            <div class="notes-status mt-2">
              <small id="saveStatus" class="text-muted">
                <i class="bi bi-check-circle text-success"></i> Guardado automáticamente
              </small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="profile-card">
          <h3 class="section-title">
            <i class="bi bi-robot"></i>
            Contexto del Cliente
            <button 
              id="refreshContextBtn" 
              class="btn btn-sm btn-outline-primary ms-2"
              onclick="generateClientContext()"
              title="Generar nuevo contexto con IA"
            >
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </h3>
          <div class="context-container">
            <div id="clientContext" class="context-content" style="min-height: 200px; max-height: 300px; overflow-y: auto; padding: 12px; background: #f8f9fa; border-radius: 8px; border: 1px solid var(--chat-border);">
              <div class="text-center text-muted p-3">
                <i class="bi bi-robot"></i>
                <p class="mb-2">Contexto generado por IA</p>
                <button class="btn btn-primary btn-sm" onclick="generateClientContext()">
                  <i class="bi bi-magic"></i> Generar Contexto
                </button>
              </div>
            </div>
            <div id="contextLoading" class="text-center p-3" style="display: none;">
              <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
              <span class="ms-2 text-muted">Generando contexto con IA...</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Métricas de Actividad -->
    <div class="row mb-4">
      <div class="col-12">
        <h3 class="section-title">
          <i class="bi bi-bar-chart-line"></i>
          Resumen de Actividad
        </h3>
      </div>
    </div>

    <div class="row mb-4 metrics-row">
      <div class="col-lg-3 col-md-6 mb-3 metric-column">
        <div class="metric-card" data-metric="propiedades" onclick="toggleMetricDetails(this)">
          <i class="bi bi-house metric-icon"></i>
          <div id="totalPropiedades" class="metric-number">-</div>
          <div class="metric-label">Propiedades</div>
          <i class="bi bi-chevron-down expand-icon" style="position: absolute; top: 10px; right: 10px; font-size: 14px; opacity: 0.7;"></i>
        </div>
        <div class="metric-details" id="details-propiedades" style="display: none;">
          <div class="details-content">
            <div class="loading-details">
              <div class="spinner-border spinner-border-sm" role="status"></div>
              <span class="ms-2">Cargando detalles...</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3 metric-column">
        <div class="metric-card" data-metric="ctg" onclick="toggleMetricDetails(this)">
          <i class="bi bi-tools metric-icon"></i>
          <div id="totalCTG" class="metric-number">-</div>
          <div class="metric-label">Solicitudes CTG</div>
          <i class="bi bi-chevron-down expand-icon" style="position: absolute; top: 10px; right: 10px; font-size: 14px; opacity: 0.7;"></i>
        </div>
        <div class="metric-details" id="details-ctg" style="display: none;">
          <div class="details-content">
            <div class="loading-details">
              <div class="spinner-border spinner-border-sm" role="status"></div>
              <span class="ms-2">Cargando detalles...</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3 metric-column">
        <div class="metric-card" data-metric="pqr" onclick="toggleMetricDetails(this)">
          <i class="bi bi-chat-dots metric-icon"></i>
          <div id="totalPQR" class="metric-number">-</div>
          <div class="metric-label">Solicitudes PQR</div>
          <i class="bi bi-chevron-down expand-icon" style="position: absolute; top: 10px; right: 10px; font-size: 14px; opacity: 0.7;"></i>
        </div>
        <div class="metric-details" id="details-pqr" style="display: none;">
          <div class="details-content">
            <div class="loading-details">
              <div class="spinner-border spinner-border-sm" role="status"></div>
              <span class="ms-2">Cargando detalles...</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3 metric-column">
        <div class="metric-card" data-metric="citas" onclick="toggleMetricDetails(this)">
          <i class="bi bi-calendar-check metric-icon"></i>
          <div id="totalCitas" class="metric-number">-</div>
          <div class="metric-label">Citas</div>
          <i class="bi bi-chevron-down expand-icon" style="position: absolute; top: 10px; right: 10px; font-size: 14px; opacity: 0.7;"></i>
        </div>
        <div class="metric-details" id="details-citas" style="display: none;">
          <div class="details-content">
            <div class="loading-details">
              <div class="spinner-border spinner-border-sm" role="status"></div>
              <span class="ms-2">Cargando detalles...</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="row activity-section">
      <div class="col-md-6 mb-4">
        <div class="profile-card">
          <h4 class="section-title">
            <i class="bi bi-clock-history"></i>
            Actividad Reciente CTG
          </h4>
          <div id="recentCTG">
            <!-- Se llena dinámicamente -->
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-4">
        <div class="profile-card">
          <h4 class="section-title">
            <i class="bi bi-chat-left-dots"></i>
            Actividad Reciente PQR
          </h4>
          <div id="recentPQR">
            <!-- Se llena dinámicamente -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('user_id');
    
    if (!userId) {
        showError('ID de usuario no proporcionado');
        return;
    }

    // Obtener token del localStorage (enviado desde chat_responsable.php)
    const token = localStorage.getItem('responsable_token');
    if (!token) {
        showError('Token de autenticación no encontrado');
        return;
    }

    loadUserProfile(userId, token);
});

async function loadUserProfile(userId, token) {
    try {
        const response = await fetch(`../../../api/chat/perfil/perfil.php?user_id=${userId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Error al cargar el perfil');
        }

        if (data.ok) {
            displayUserProfile(data);
        } else {
            throw new Error(data.error || 'Error en la respuesta del servidor');
        }
    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

function displayUserProfile(data) {
    // Información personal
    const fullName = `${data.usuario.nombres || ''} ${data.usuario.apellidos || ''}`.trim();
    document.getElementById('userName').textContent = fullName || 'Usuario';
    document.getElementById('userEmail').textContent = data.usuario.correo || '-';
    document.getElementById('userCedula').textContent = data.usuario.cedula || '-';
    document.getElementById('userTelefono').textContent = data.usuario.telefono || '-';

    // Avatar con iniciales o foto de perfil
    if (data.usuario.url_foto_perfil) {
        const avatarElement = document.getElementById('userAvatar');
        avatarElement.innerHTML = `<img src="${data.usuario.url_foto_perfil}" alt="Foto de perfil" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
    } else {
        const initials = getInitials(fullName || 'Usuario');
        document.getElementById('userAvatar').textContent = initials;
    }

    // Métricas
    document.getElementById('totalPropiedades').textContent = data.estadisticas.propiedades.total_propiedades || 0;
    document.getElementById('totalCTG').textContent = data.estadisticas.ctg.total_ctg || 0;
    document.getElementById('totalPQR').textContent = data.estadisticas.pqr.total_pqr || 0;
    document.getElementById('totalCitas').textContent = data.estadisticas.citas.total_citas || 0;

    // Actividad reciente CTG
    const recentCTGContainer = document.getElementById('recentCTG');
    if (data.actividad_reciente.ctg && data.actividad_reciente.ctg.length > 0) {
        recentCTGContainer.innerHTML = data.actividad_reciente.ctg.map(item => `
            <div class="activity-item">
                <div class="activity-date">${formatDate(item.fecha_creacion)}</div>
                <div class="activity-title">${item.descripcion}</div>
                <div class="activity-desc">Estado: ${item.estado}</div>
            </div>
        `).join('');
    } else {
        recentCTGContainer.innerHTML = '<p class="text-muted text-center">No hay actividad reciente</p>';
    }

    // Actividad reciente PQR
    const recentPQRContainer = document.getElementById('recentPQR');
    if (data.actividad_reciente.pqr && data.actividad_reciente.pqr.length > 0) {
        recentPQRContainer.innerHTML = data.actividad_reciente.pqr.map(item => `
            <div class="activity-item">
                <div class="activity-date">${formatDate(item.fecha_creacion)}</div>
                <div class="activity-title">${item.descripcion}</div>
                <div class="activity-desc">Estado: ${item.estado}</div>
            </div>
        `).join('');
    } else {
        recentPQRContainer.innerHTML = '<p class="text-muted text-center">No hay actividad reciente</p>';
    }

    // Mostrar contenido y ocultar loading
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('profileContent').style.display = 'block';
    
    // Cargar notas del cliente y establecer ID actual
    const urlParams = new URLSearchParams(window.location.search);
    currentUserId = urlParams.get('user_id');
    if (currentUserId) {
        loadClientNotes(currentUserId);
    }
}

function getInitials(name) {
    return name.split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showError(message) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorText').textContent = message;
    document.getElementById('errorState').style.display = 'block';
}

// Funciones para métricas expandibles
async function toggleMetricDetails(cardElement) {
    const metricType = cardElement.getAttribute('data-metric');
    const detailsElement = document.getElementById(`details-${metricType}`);
    const expandIcon = cardElement.querySelector('.expand-icon');
    const metricsRow = document.querySelector('.metrics-row');
    const currentColumn = cardElement.closest('.metric-column');
    const contentContainer = document.querySelector('.content-container');
    
    // Verificar si esta métrica ya está expandida
    const isCurrentlyExpanded = cardElement.classList.contains('expanded');
    
    // Cerrar cualquier métrica expandida previamente
    const expandedColumns = document.querySelectorAll('.expanded-column');
    const expandedCards = document.querySelectorAll('.metric-card.expanded');
    
    expandedColumns.forEach(col => {
        col.classList.remove('expanded-column');
        const details = col.querySelector('.metric-details');
        if (details) details.style.display = 'none';
    });
    
    expandedCards.forEach(card => {
        card.classList.remove('expanded');
        const icon = card.querySelector('.expand-icon');
        if (icon) {
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
        }
    });
    
    metricsRow.classList.remove('expanded');
    contentContainer.classList.remove('metrics-expanded');
    
    // Si no estaba expandida, expandir
    if (!isCurrentlyExpanded) {
        cardElement.classList.add('expanded');
        currentColumn.classList.add('expanded-column');
        metricsRow.classList.add('expanded');
        contentContainer.classList.add('metrics-expanded');
        detailsElement.style.display = 'block';
        
        // Cambiar icono
        expandIcon.classList.remove('bi-chevron-down');
        expandIcon.classList.add('bi-chevron-up');
        
        // Cargar detalles si no están cargados
        if (!detailsElement.hasAttribute('data-loaded')) {
            await loadMetricDetails(metricType, detailsElement);
            detailsElement.setAttribute('data-loaded', 'true');
        }
    }
    // Si ya estaba expandida, ya se cerró en el código anterior
}

async function loadMetricDetails(metricType, detailsElement) {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('user_id');
    const token = localStorage.getItem('responsable_token');
    
    try {
        const response = await fetch(`../../../api/chat/perfil/detalles_metricas.php?user_id=${userId}&tipo=${metricType}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Error al cargar detalles');
        }

        if (data.ok) {
            displayMetricDetails(metricType, data.detalles, detailsElement);
        } else {
            throw new Error(data.error || 'Error en la respuesta del servidor');
        }
    } catch (error) {
        console.error('Error:', error);
        detailsElement.querySelector('.details-content').innerHTML = `
            <div class="text-center text-danger p-3">
                <i class="bi bi-exclamation-triangle"></i>
                <p class="mb-0 mt-2">Error al cargar detalles: ${error.message}</p>
            </div>
        `;
    }
}

function displayMetricDetails(metricType, detalles, detailsElement) {
    const contentElement = detailsElement.querySelector('.details-content');
    
    if (!detalles || detalles.length === 0) {
        contentElement.innerHTML = `
            <div class="text-center text-muted p-3">
                <i class="bi bi-info-circle"></i>
                <p class="mb-0 mt-2">No hay información disponible</p>
            </div>
        `;
        return;
    }

    let html = '';
    
    switch (metricType) {
        case 'propiedades':
            html = detalles.map(propiedad => `
                <div class="detail-item">
                    <div class="detail-title">
                        ${propiedad.tipo_propiedad || 'Propiedad'} - ${propiedad.urbanizacion || 'N/A'}
                    </div>
                    <div class="detail-subtitle">
                        Mz: ${propiedad.manzana || 'N/A'}, Solar: ${propiedad.solar || 'N/A'}, Villa: ${propiedad.villa || 'N/A'}
                    </div>
                    <div class="detail-info">
                        <div>
                            <strong>Etapa:</strong> ${propiedad.etapa_construccion || 'N/A'} 
                            ${propiedad.porcentaje_construccion ? `(${propiedad.porcentaje_construccion}%)` : ''}
                        </div>
                        <span class="detail-badge badge-info">${propiedad.estado_propiedad || 'N/A'}</span>
                    </div>
                    ${propiedad.fecha_entrega ? `
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-calendar-event"></i> 
                                Entrega: ${formatDate(propiedad.fecha_entrega)}
                            </small>
                        </div>
                    ` : ''}
                    ${propiedad.acabado_kit ? `
                        <div class="mt-1">
                            <small class="text-muted">
                                <i class="bi bi-palette"></i> 
                                Acabado: ${propiedad.acabado_kit}
                                ${propiedad.acabado_color_seleccionado ? ` - ${propiedad.acabado_color_seleccionado}` : ''}
                            </small>
                        </div>
                    ` : ''}
                </div>
            `).join('');
            break;
            
        case 'ctg':
            html = detalles.map(ctg => `
                <div class="detail-item">
                    <div class="detail-title">Solicitud #${ctg.numero_solicitud}</div>
                    <div class="detail-subtitle">${formatDate(ctg.fecha_ingreso)}</div>
                    <div class="detail-info">
                        <div>
                            <strong>Tipo:</strong> ${ctg.tipo_ctg || 'N/A'}<br>
                            <small class="text-muted">${ctg.descripcion || 'Sin descripción'}</small>
                        </div>
                        <span class="detail-badge ${getStatusBadgeClass(ctg.estado)}">${ctg.estado || 'N/A'}</span>
                    </div>
                    ${ctg.fecha_resolucion ? `
                        <div class="mt-2">
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i> 
                                Resuelto: ${formatDate(ctg.fecha_resolucion)}
                            </small>
                        </div>
                    ` : ''}
                </div>
            `).join('');
            break;
            
        case 'pqr':
            html = detalles.map(pqr => `
                <div class="detail-item">
                    <div class="detail-title">Solicitud #${pqr.numero_solicitud}</div>
                    <div class="detail-subtitle">${formatDate(pqr.fecha_ingreso)}</div>
                    <div class="detail-info">
                        <div>
                            <strong>Tipo:</strong> ${pqr.tipo_pqr || 'N/A'}<br>
                            <small class="text-muted">${pqr.descripcion || 'Sin descripción'}</small>
                        </div>
                        <span class="detail-badge ${getStatusBadgeClass(pqr.estado)}">${pqr.estado || 'N/A'}</span>
                    </div>
                    ${pqr.fecha_resolucion ? `
                        <div class="mt-2">
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i> 
                                Resuelto: ${formatDate(pqr.fecha_resolucion)}
                            </small>
                        </div>
                    ` : ''}
                </div>
            `).join('');
            break;
            
        case 'citas':
            html = detalles.map(cita => `
                <div class="detail-item">
                    <div class="detail-title">
                        ${cita.proposito || 'Cita'} - ${formatDate(cita.fecha_reunion)} ${cita.hora_reunion}
                    </div>
                    <div class="detail-subtitle">
                        Responsable: ${cita.responsable_nombre || 'N/A'}
                        ${cita.manzana && cita.solar ? ` | Propiedad: Mz ${cita.manzana}, Solar ${cita.solar}` : ''}
                    </div>
                    <div class="detail-info">
                        <div>
                            <strong>Estado:</strong> ${cita.estado || 'N/A'}<br>
                            <strong>Asistencia:</strong> ${getAsistenciaText(cita.asistencia)}
                        </div>
                        <span class="detail-badge ${getAsistenciaBadgeClass(cita.asistencia)}">${getAsistenciaText(cita.asistencia)}</span>
                    </div>
                    ${cita.resultado ? `
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-clipboard-check"></i> 
                                Resultado: ${cita.resultado}
                            </small>
                        </div>
                    ` : ''}
                </div>
            `).join('');
            break;
    }
    
    contentElement.innerHTML = html;
}

function getStatusBadgeClass(estado) {
    if (!estado) return 'badge-info';
    
    const estadoLower = estado.toLowerCase();
    if (estadoLower.includes('resuelto') || estadoLower.includes('completado') || estadoLower.includes('cerrado')) {
        return 'badge-success';
    } else if (estadoLower.includes('pendiente') || estadoLower.includes('proceso') || estadoLower.includes('abierto')) {
        return 'badge-warning';
    } else if (estadoLower.includes('cancelado') || estadoLower.includes('rechazado')) {
        return 'badge-danger';
    }
    return 'badge-info';
}

function getAsistenciaText(asistencia) {
    switch (asistencia) {
        case 'ASISTIO': return 'Asistió';
        case 'NO_ASISTIO': return 'No asistió';
        case 'NO_REGISTRADO': return 'No registrado';
        default: return 'N/A';
    }
}

function getAsistenciaBadgeClass(asistencia) {
    switch (asistencia) {
        case 'ASISTIO': return 'badge-success';
        case 'NO_ASISTIO': return 'badge-danger';
        case 'NO_REGISTRADO': return 'badge-warning';
        default: return 'badge-info';
    }
}



// Variables globales para notas
let currentUserId = null;
let notesTimeout = null;

// Función para cargar notas del cliente
async function loadClientNotes(userId) {
    const token = localStorage.getItem('responsable_token');
    
    try {
        const response = await fetch(`../../../api/chat/perfil/notas.php?user_id=${userId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (data.ok) {
            const notesTextarea = document.getElementById('clientNotes');
            if (notesTextarea) {
                notesTextarea.value = data.notas || '';
            }
        } else {
            console.error('Error al cargar notas:', data.error);
        }
    } catch (error) {
        console.error('Error al cargar notas:', error);
    }
}

// Función para guardar notas del cliente
async function saveClientNotes(userId, notes) {
    const token = localStorage.getItem('responsable_token');
    const statusElement = document.getElementById('saveStatus');
    
    try {
        if (statusElement) {
            statusElement.innerHTML = '<i class="bi bi-clock text-warning"></i> Guardando...';
        }
        
        const response = await fetch('../../../api/chat/perfil/notas.php', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                notas: notes
            })
        });

        const data = await response.json();
        
        if (data.ok) {
            if (statusElement) {
                statusElement.innerHTML = '<i class="bi bi-check-circle text-success"></i> Guardado';
                setTimeout(() => {
                    statusElement.innerHTML = '';
                }, 2000);
            }
        } else {
            throw new Error(data.error || 'Error al guardar');
        }
    } catch (error) {
        console.error('Error al guardar notas:', error);
        if (statusElement) {
            statusElement.innerHTML = '<i class="bi bi-exclamation-triangle text-danger"></i> Error al guardar';
            setTimeout(() => {
                statusElement.innerHTML = '';
            }, 3000);
        }
    }
}

// Función para generar contexto del cliente con IA
async function generateClientContext() {
    const token = localStorage.getItem('responsable_token');
    const contextElement = document.getElementById('clientContext');
    const loadingElement = document.getElementById('contextLoading');
    
    try {
        contextElement.style.display = 'none';
        loadingElement.style.display = 'block';
        
        const response = await fetch(`../../../api/chat/perfil/contexto_ia.php?user_id=${currentUserId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (data.ok) {
            contextElement.innerHTML = `
                <div class="context-text">
                    ${data.contexto.replace(/\n/g, '<br>')}
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> Generado: ${new Date().toLocaleString('es-ES')}
                    </small>
                </div>
            `;
        } else {
            throw new Error(data.error || 'Error al generar contexto');
        }
    } catch (error) {
        console.error('Error al generar contexto:', error);
        contextElement.innerHTML = `
            <div class="text-center text-danger p-3">
                <i class="bi bi-exclamation-triangle"></i>
                <p class="mb-2">Error al generar contexto</p>
                <small>${error.message}</small>
                <br>
                <button class="btn btn-primary btn-sm mt-2" onclick="generateClientContext()">
                    <i class="bi bi-arrow-clockwise"></i> Reintentar
                </button>
            </div>
        `;
    } finally {
        loadingElement.style.display = 'none';
        contextElement.style.display = 'block';
    }
}

// Event listeners para notas
document.addEventListener('DOMContentLoaded', function() {
    const notesTextarea = document.getElementById('clientNotes');
    
    if (notesTextarea) {
        // Auto-save con debounce
        notesTextarea.addEventListener('input', function() {
            clearTimeout(notesTimeout);
            notesTimeout = setTimeout(() => {
                if (currentUserId) {
                    saveClientNotes(currentUserId, this.value);
                }
            }, 1000); // Guardar después de 1 segundo de inactividad
        });
    }
});
</script>

<?php endif; ?>
</body>
</html>