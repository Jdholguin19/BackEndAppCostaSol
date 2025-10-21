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
    <button class="admin-button" id="adminButton" style="display: none;" onclick="location.href='admin/admin_garantias.php'">
      <i class="bi bi-gear"></i> Administrar
    </button>
  </div>
</div>

<!-- Main Content -->
<div class="garantias-container">
  <!-- Información importante -->

 <div class="info-card-warning">
  <div class="flex-container">
    <div class="text-content">
        <h4><i class="bi bi-exclamation-triangle-fill info-icon-warning"></i> Información importante</h4>
        <p>Las garantías son válidas siempre y cuando se haya realizado el mantenimiento recomendado y el uso adecuado de los elementos, de acuerdo al <a href="../api/mcm.php" target="_blank">manual de uso y mantenimiento</a>.</p>
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

<?php 
$active_page = 'inicio';
include '../api/bottom_nav.php'; 
?>

<script>
// Verificar autenticación
const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
if (!u.id) location.href = 'login_front.php';

// Mostrar botón de administración solo para responsables
if (u.is_responsable) {
    document.getElementById('adminButton').style.display = 'block';
}

const API_GARANTIAS = '../api/garantias.php';


// Función para crear tarjeta de garantía
function createGarantiaCard(garantia) {
    const cardClass = garantia.activa ? '' : 'garantia-expirada';
    const statusIcon = garantia.activa
        ? '<span class="status-badge status-active">Activa</span>'
        : '<span class="status-badge status-expired">Expirada</span>';

    const descripcion = garantia.descripcion && garantia.descripcion !== garantia.categoria
        ? `<p class="garantia-description">${garantia.descripcion}</p>`
        : '';

    return `
        <div class="garantia-card ${cardClass}">
            <div class="garantia-header">
                <div class="garantia-info">
                    <h3 class="garantia-categoria">${garantia.categoria}</h3>
                    ${descripcion}
                </div>
                ${statusIcon}
            </div>
            <div class="garantia-details">
                <div class="garantia-detail">
                    <i class="bi bi-clock garantia-detail-icon"></i>
                    <p class="garantia-detail-text">
                        Duración: <span class="garantia-detail-value">${garantia.duracion}</span>
                    </p>
                </div>
                <div class="garantia-detail">
                    <i class="bi bi-calendar-check garantia-detail-icon"></i>
                    <p class="garantia-detail-text">
                        Vigencia hasta: <span class="garantia-detail-value">${garantia.vigencia}</span>
                    </p>
                </div>
            </div>
        </div>
    `;
}

// Función para clasificar garantías por duración
function clasificarGarantias(garantias) {
    const por_duracion = {
        '12_meses': [],      // 1 año
        '6_meses': [],       // 6 meses
        '3_meses': [],       // 3 meses
        'otras': [],         // Otras duraciones
        'entrega': []        // Válidas hasta la entrega
    };

    garantias.forEach(garantia => {
        if (garantia.tipo_garantia === 'entrega') {
            por_duracion.entrega.push(garantia);
        } else {
            const meses = extraerMeses(garantia.duracion);
            
            if (meses === 12) {
                por_duracion['12_meses'].push(garantia);
            } else if (meses === 6) {
                por_duracion['6_meses'].push(garantia);
            } else if (meses === 3) {
                por_duracion['3_meses'].push(garantia);
            } else {
                por_duracion['otras'].push(garantia);
            }
        }
    });

    return por_duracion;
}

// Función para extraer meses de la duración
function extraerMeses(duracion) {
    // Extraer el número de meses de textos como "1 año", "6 meses", etc.
    const match = duracion.match(/(\d+)\s+mes/i);
    if (match) {
        return parseInt(match[1]);
    }
    
    const matchAno = duracion.match(/(\d+)\s+año/i);
    if (matchAno) {
        return parseInt(matchAno[1]) * 12;
    }
    
    return 0;
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
            // Si no hay garantías, no mostrar nada
            garantiasList.innerHTML = '';
            return;
        }

        // Clasificar garantías
        const clasificadas = clasificarGarantias(data.garantias);

        // Verificar si hay al menos una categoría con garantías
        const hayGarantias = Object.values(clasificadas).some(grupo => grupo.length > 0);

        // Limpiar contenedor
        garantiasList.innerHTML = '';

        if (!hayGarantias) {
            // Si no hay garantías en ninguna categoría, no mostrar nada
            return;
        }

        // Mostrar garantías de 1 año
        if (clasificadas['12_meses'].length > 0) {
            garantiasList.insertAdjacentHTML('beforeend', '<h4 style="margin-top: 20px; margin-bottom: 15px; color: #2d5a3d; font-weight: 600;">Garantías válidas por 1 año</h4>');
            clasificadas['12_meses'].forEach(garantia => {
                garantiasList.insertAdjacentHTML('beforeend', createGarantiaCard(garantia));
            });
        }

        // Mostrar garantías de 6 meses
        if (clasificadas['6_meses'].length > 0) {
            garantiasList.insertAdjacentHTML('beforeend', '<h4 style="margin-top: 20px; margin-bottom: 15px; color: #2d5a3d; font-weight: 600;">Garantías válidas por 6 meses</h4>');
            clasificadas['6_meses'].forEach(garantia => {
                garantiasList.insertAdjacentHTML('beforeend', createGarantiaCard(garantia));
            });
        }

        // Mostrar garantías de 3 meses
        if (clasificadas['3_meses'].length > 0) {
            garantiasList.insertAdjacentHTML('beforeend', '<h4 style="margin-top: 20px; margin-bottom: 15px; color: #2d5a3d; font-weight: 600;">Garantías válidas por 3 meses</h4>');
            clasificadas['3_meses'].forEach(garantia => {
                garantiasList.insertAdjacentHTML('beforeend', createGarantiaCard(garantia));
            });
        }

        // Mostrar otras duraciones
        if (clasificadas['otras'].length > 0) {
            garantiasList.insertAdjacentHTML('beforeend', '<h4 style="margin-top: 20px; margin-bottom: 15px; color: #2d5a3d; font-weight: 600;">Otras garantías</h4>');
            clasificadas['otras'].forEach(garantia => {
                garantiasList.insertAdjacentHTML('beforeend', createGarantiaCard(garantia));
            });
        }

        // Mostrar garantías válidas hasta la entrega (al final)
        if (clasificadas['entrega'].length > 0) {
            garantiasList.insertAdjacentHTML('beforeend', '<h4 style="margin-top: 30px; margin-bottom: 15px; color: #2d5a3d; font-weight: 600; border-top: 2px solid #e5e7eb; padding-top: 20px;">Garantías válidas hasta la entrega</h4>');
            clasificadas['entrega'].forEach(garantia => {
                garantiasList.insertAdjacentHTML('beforeend', createGarantiaCard(garantia));
            });
        }

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