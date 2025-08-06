<?php /* Front/garantias.php */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Garantías | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">

<style>
/* Specific styles for garantias */
.garantias-header {
    background: #ffffff;
    padding: 1rem 1.5rem 0.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.garantias-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2d5a3d;
    margin: 0;
    text-align: center;
}

.garantias-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0.5rem 0 0 0;
    text-align: center;
}

.back-button {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #2d5a3d;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.back-button:hover {
    background-color: #f3f4f6;
}

.garantias-container {
    padding: 1rem 1.5rem;
    padding-bottom: 5rem; /* Space for bottom navigation */
}

.garantia-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #f0f0f0;
    transition: all 0.2s ease;
}

.garantia-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.garantia-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.garantia-icon {
    width: 48px;
    height: 48px;
    background: #2d5a3d;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.garantia-info {
    flex: 1;
    margin-left: 1rem;
}

.garantia-categoria {
    font-size: 1rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 0.25rem 0;
}

.garantia-elemento {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.garantia-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f3f4f6;
}

.garantia-detail {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.garantia-detail-icon {
    width: 20px;
    color: #2d5a3d;
    font-size: 0.875rem;
}

.garantia-detail-text {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.garantia-detail-value {
    font-weight: 600;
    color: #2d5a3d;
}

.garantia-duracion {
    background: #f0f9ff;
    color: #0369a1;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.garantia-responsable {
    background: #fef3c7;
    color: #92400e;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Loading states */
.spinner-border {
    color: #2d5a3d;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .garantia-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .garantia-info {
        margin-left: 0;
    }
}
</style>
</head>
<body>

<!-- Header Section -->
<div class="garantias-header">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <div>
      <h1 class="garantias-title">Garantías</h1>
      <p class="garantias-subtitle">Información detallada de garantías</p>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="garantias-container">
  <div id="garantias-list">
    <div class="text-center py-5" id="spinner">
      <div class="spinner-border"></div>
    </div>
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

<script>
// Verificar autenticación
const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
if (!u.id) location.href = 'login_front.php';

const API_GARANTIAS = '../api/garantias.php';

// Función para obtener icono según categoría
function getIconForCategory(categoria) {
    const categoryIcons = {
        'Estructura': 'bi-building',
        'Instalaciones': 'bi-lightning',
        'Acabados': 'bi-palette',
        'Sistema Eléctrico': 'bi-lightning-charge',
        'Sistema Hidráulico': 'bi-droplet',
        'Pintura': 'bi-brush',
        'Carpintería': 'bi-hammer',
        'Pisos': 'bi-grid-3x3',
        'Techos': 'bi-house',
        'Jardines': 'bi-flower1',
        'Cerrajería': 'bi-key',
        'default': 'bi-shield-check'
    };
    
    for (const [key, icon] of Object.entries(categoryIcons)) {
        if (categoria.toLowerCase().includes(key.toLowerCase())) {
            return icon;
        }
    }
    return categoryIcons.default;
}

// Función para crear tarjeta de garantía
function createGarantiaCard(garantia) {
    const icon = getIconForCategory(garantia.categoria);
    
    return `
        <div class="garantia-card">
            <div class="garantia-header">
                <div class="garantia-icon">
                    <i class="bi ${icon}"></i>
                </div>
                <div class="garantia-info">
                    <h3 class="garantia-categoria">${garantia.categoria}</h3>
                    <p class="garantia-elemento">${garantia.elemento}</p>
                </div>
            </div>
            <div class="garantia-details">
                <div class="garantia-detail">
                    <i class="bi bi-clock garantia-detail-icon"></i>
                    <p class="garantia-detail-text">
                        Duración: <span class="garantia-detail-value">${garantia.duracion}</span>
                    </p>
                </div>
                <div class="garantia-detail">
                    <i class="bi bi-person garantia-detail-icon"></i>
                    <p class="garantia-detail-text">
                        Responsable: <span class="garantia-detail-value">${garantia.responsable}</span>
                    </p>
                </div>
            </div>
        </div>
    `;
}

// Cargar garantías
async function loadGarantias() {
    try {
        const token = localStorage.getItem('cs_token');
        if (!token) {
            throw new Error('No hay token de autenticación');
        }

        const response = await fetch(API_GARANTIAS, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (!data.ok) {
            throw new Error(data.error || 'Error al cargar garantías');
        }

        const garantiasList = document.getElementById('garantias-list');
        document.getElementById('spinner').remove();
        
        if (data.garantias.length === 0) {
            garantiasList.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-info-circle text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No hay garantías disponibles</p>
                </div>
            `;
            return;
        }

        data.garantias.forEach(garantia => {
            garantiasList.insertAdjacentHTML('beforeend', createGarantiaCard(garantia));
        });

    } catch (error) {
        console.error('Error al cargar garantías:', error);
        document.getElementById('spinner').innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <p class="text-danger mt-3">Error al cargar las garantías</p>
                <p class="text-muted small">${error.message}</p>
            </div>
        `;
    }
}

// Cargar garantías al cargar la página
document.addEventListener('DOMContentLoaded', loadGarantias);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
