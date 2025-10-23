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
      background: var(--chat-panel);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      border: 1px solid var(--chat-border);
      transition: all 0.2s ease;
      height: 100%;
    }

    .metric-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
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

    <!-- Métricas de Actividad -->
    <div class="row mb-4">
      <div class="col-12">
        <h3 class="section-title">
          <i class="bi bi-bar-chart-line"></i>
          Resumen de Actividad
        </h3>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card">
          <i class="bi bi-house metric-icon"></i>
          <div id="totalPropiedades" class="metric-number">-</div>
          <div class="metric-label">Propiedades</div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card">
          <i class="bi bi-tools metric-icon"></i>
          <div id="totalCTG" class="metric-number">-</div>
          <div class="metric-label">Solicitudes CTG</div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card">
          <i class="bi bi-chat-dots metric-icon"></i>
          <div id="totalPQR" class="metric-number">-</div>
          <div class="metric-label">Solicitudes PQR</div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card">
          <i class="bi bi-calendar-check metric-icon"></i>
          <div id="totalCitas" class="metric-number">-</div>
          <div class="metric-label">Citas</div>
        </div>
      </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="row">
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
    const fullName = `${data.usuario.nombre} ${data.usuario.apellidos || ''}`.trim();
    document.getElementById('userName').textContent = fullName;
    document.getElementById('userEmail').textContent = data.usuario.correo || '-';
    document.getElementById('userCedula').textContent = data.usuario.cedula || '-';
    document.getElementById('userTelefono').textContent = data.usuario.telefono || '-';

    // Avatar con iniciales
    const initials = getInitials(fullName);
    document.getElementById('userAvatar').textContent = initials;

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
                <div class="activity-title">${item.tipo_solicitud}</div>
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
                <div class="activity-title">${item.tipo}</div>
                <div class="activity-desc">Estado: ${item.estado}</div>
            </div>
        `).join('');
    } else {
        recentPQRContainer.innerHTML = '<p class="text-muted text-center">No hay actividad reciente</p>';
    }

    // Mostrar contenido y ocultar loading
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('profileContent').style.display = 'block';
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
</script>

<?php endif; ?>
</body>
</html>