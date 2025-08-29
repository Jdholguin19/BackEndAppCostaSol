<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Selección de Acabados | CostaSol</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style_main.css" rel="stylesheet">
<link href="assets/css/style_acabados.css" rel="stylesheet">

</head>
<body>

<!-- Header Section -->
<div class="header-acabados">
  <div style="position: relative; text-align: center;">
    <button class="back-button" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
    <span class="title">Selección de Acabados</span>
  </div>
</div>

<!-- Main Content -->
<div class="container mt-4 acabados-container">
  
  <div class="acabados-header-text">
    <h2 id="casa-modelo-titulo" class="casa-modelo"></h2>
    <p class="escoge-color">1. Escoge el modelo de tu cocina</p>
  </div>

  <div id="choice-container-modelo" class="choice-container"></div>

  <div class="acabados-header-text mt-4">
    <p class="escoge-color">2. Escoge el color de tus acabados</p>
  </div>

  <div id="choice-container-color" class="choice-container">
    <button class="btn-acabado claro" data-color="Claro">Claro</button>
    <button class="btn-acabado oscuro" data-color="Oscuro">Oscuro</button>
  </div>

  <div class="plan-container mt-5">
    <h3 id="plan-title" class="plan-title"></h3>
    <div class="plan-image-placeholder">
        <img id="plan-image" src="" alt="Plano de la cocina">
    </div>
  </div>

  <div class="mt-5">
      <button id="btnGuardar" class="btn btn-primary btn-lg">Guardar Selección</button>
  </div>

</div>

<?php 
$active_page = 'inicio';
include '../api/bottom_nav.php'; 
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- REFS --- //
    const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
    const token = localStorage.getItem('cs_token');
    if (!u.id || !token) {
        location.href = 'login_front.php';
        return;
    }

    const modeloTitulo = document.getElementById('casa-modelo-titulo');
    const planTitle = document.getElementById('plan-title');
    const planImage = document.getElementById('plan-image');
    const modeloContainer = document.getElementById('choice-container-modelo');
    const colorContainer = document.getElementById('choice-container-color');
    const btnGuardar = document.getElementById('btnGuardar');

    let planosData = [];
    let seleccion = {
        planoId: null,
        color: null
    };

    // --- LÓGICA DE SELECCIÓN --- //
    function selectModelo(plano) {
        if (!plano) return;
        
        seleccion.planoId = plano.id;
        modeloTitulo.textContent = `Cocina Modelo ${plano.nombre}`;
        planTitle.textContent = `Plano de Cocina ${plano.nombre}`;
        planImage.src = plano.url_plano;

        // Marcar como activo
        document.querySelectorAll('#choice-container-modelo .btn-acabado').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.id == plano.id);
        });
        checkGuardar();
    }

    function selectColor(color) {
        seleccion.color = color;
        document.querySelectorAll('#choice-container-color .btn-acabado').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.color === color);
        });
        checkGuardar();
    }

    function checkGuardar(){
        btnGuardar.disabled = !(seleccion.planoId && seleccion.color);
    }

    // --- EVENT LISTENERS --- //
    modeloContainer.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-acabado');
        if (!btn) return;
        const plano = planosData.find(p => p.id == btn.dataset.id);
        selectModelo(plano);
    });

    colorContainer.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-acabado');
        if (!btn) return;
        selectColor(btn.dataset.color);
    });

    btnGuardar.addEventListener('click', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const propiedadId = urlParams.get('propiedad_id');

        if (!propiedadId) {
            alert('Error: No se ha especificado una propiedad.');
            return;
        }

        btnGuardar.disabled = true;
        btnGuardar.textContent = 'Guardando…';

        fetch('../api/guardar_seleccion_acabados.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                propiedad_id: parseInt(propiedadId),
                plano_id: seleccion.planoId,
                color: seleccion.color
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                alert(data.mensaje || '¡Selección guardada con éxito!');
                // Opcional: redirigir a otra página, por ejemplo, el menú.
                // location.href = 'menu_front.php';
            } else {
                alert(`Error: ${data.mensaje || 'No se pudo guardar la selección.'}`);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión al guardar.');
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.textContent = 'Guardar Selección';
        });
    });

    // --- CARGA INICIAL --- //
    fetch('../api/planos_disponibles.php', {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.ok) {
            alert('No se pudieron cargar los modelos de cocina.');
            return;
        }
        planosData = data.planos;

        // Generar botones de modelo dinámicamente
        planosData.forEach(plano => {
            const button = document.createElement('button');
            button.className = 'btn-acabado modelo';
            button.textContent = plano.nombre;
            button.dataset.id = plano.id;
            modeloContainer.appendChild(button);
        });

        // Seleccionar "Standar" por defecto
        const planoDefault = planosData.find(p => p.nombre.toLowerCase() === 'standar');
        if (planoDefault) {
            selectModelo(planoDefault);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error de conexión al cargar los modelos.');
    });

    checkGuardar();
});
</script>

</body>
</html>