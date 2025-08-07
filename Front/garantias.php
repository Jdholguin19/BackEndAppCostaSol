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
<link href="assets/css/style_garantia.css" rel="stylesheet">
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
  <!-- Información importante -->

 <div class="info-card-warning">
  <div class="flex-container">
    <div class="text-content">
        <h4><i class="bi bi-exclamation-triangle-fill info-icon-warning"></i> Información importante</h4>
        <p>Las garantías son válidas siempre y cuando se haya realizado el mantenimiento recomendado y el uso adecuado de los elementos, de acuerdo al manual de uso y mantenimiento.</p>
    </div>
    </div>
  </div>


  <div id="garantias-list">
    <div class="text-center py-5" id="spinner">
      <div class="spinner-border"></div>
    </div>
  </div>

  <!-- Procedimiento de Reclamación -->
  <div class="info-card-procedure">
    <h4>Procedimiento de Reclamación</h4>
    <ol>
      <li>Identifique el problema y verifique si está cubierto por la garantía.</li>
      <li>Tome fotografías que evidencien el problema.</li>
      <li>Contacte al servicio al cliente a través de la sección PQR de esta aplicación.</li>
      <li>Espere la confirmación de recepción de su reclamo en un plazo de 24-48 horas.</li>
      <li>Un técnico agendará una visita para evaluar el problema y determinar si procede la garantía.</li>
    </ol>
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
