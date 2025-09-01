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

    <!-- Step 1: Kit Selection -->
    <div id="step1">
        <div class="acabados-header-text">
            <h2 class="main-title">Configura Tu Cocina Ideal</h2>
            <p class="subtitle">Elige el tipo de cocina que mejor se adapte a tus necesidades</p>
        </div>
        <div class="step-indicator">
            <div class="step-number active">1</div>
            <div class="step-line"></div>
            <div class="step-number">2</div>
        </div>
        <div id="kits-container" class="kits-container">
            <!-- Kit cards will be injected here -->
        </div>
    </div>

    <!-- Step 2: Color Selection -->
    <div id="step2" style="display: none;">
        <div class="acabados-header-text">
            <h2 class="main-title">Configura Tu Cocina Ideal</h2>
            <p id="subtitle-step2" class="subtitle"></p>
        </div>
        <div class="step-indicator">
            <div class="step-number">1</div>
            <div class="step-line"></div>
            <div class="step-number active">2</div>
        </div>
        <div id="colors-container" class="kits-container">
            <!-- Color cards will be injected here -->
        </div>
        <div class="action-buttons-step">
            <button id="btn-back-to-step1" class="btn-back"><i class="bi bi-arrow-left"></i> Volver</button>
        </div>
    </div>

    <!-- Step 3: Component Gallery & Confirmation -->
    <div id="step3" style="display: none;">
        <div class="acabados-header-text">
            <h2 id="title-step3" class="main-title"></h2>
            <p class="subtitle">Estos son los acabados para tu selección</p>
        </div>
        <div id="gallery-container" class="gallery-container">
            <!-- Component images will be injected here -->
        </div>
        <div class="action-buttons-step">
            <button id="btn-back-to-step2" class="btn-back"><i class="bi bi-arrow-left"></i> Volver</button>
            <button id="btn-confirm" class="btn-confirm">Confirmar Selección</button>
        </div>
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
    const urlParams = new URLSearchParams(window.location.search);
    const propiedadId = urlParams.get('propiedad_id');

    if (!u.id || !token || !propiedadId) {
        document.querySelector('.acabados-container').innerHTML = '<h2>Error: No se ha especificado una propiedad.</h2>';
        return;
    }

    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');

    const kitsContainer = document.getElementById('kits-container');
    const colorsContainer = document.getElementById('colors-container');
    const galleryContainer = document.getElementById('gallery-container');

    const subtitleStep2 = document.getElementById('subtitle-step2');
    const titleStep3 = document.getElementById('title-step3');

    const btnBackToStep1 = document.getElementById('btn-back-to-step1');
    const btnBackToStep2 = document.getElementById('btn-back-to-step2');
    const btnConfirm = document.getElementById('btn-confirm');

    let selection = {
        kit: null, // {id, nombre, ...}
        color: null // {color_nombre, ...}
    };

    // --- NAVIGATION LOGIC --- //
    function goToStep(stepNumber) {
        step1.style.display = (stepNumber === 1) ? 'block' : 'none';
        step2.style.display = (stepNumber === 2) ? 'block' : 'none';
        step3.style.display = (stepNumber === 3) ? 'block' : 'none';
    }

    // --- DATA FETCHING & RENDERING --- //
    function renderKits(kits) {
        kitsContainer.innerHTML = '';
        const standardKit = kits.find(kit => kit.id == 1); // Find kit with ID 1

        if (standardKit) {
            const kit = standardKit;
            const card = document.createElement('div');
            card.className = 'selection-card';
            card.dataset.kitId = kit.id;
            card.innerHTML = `
                <div class="card-image-wrapper">
                    <img src="${kit.url_imagen_principal}" alt="${kit.nombre}">
                    <div class="card-overlay">
                        <h3 class="card-title">${kit.nombre}</h3>
                    </div>
                </div>
                <p class="card-description">${kit.descripcion}</p>
            `;
            card.addEventListener('click', () => {
                selection.kit = kit;
                renderColorOptions(kit);
                goToStep(2);
            });
            kitsContainer.appendChild(card);
        } else {
            // Optional: handle case where kit 1 is not found
            kitsContainer.innerHTML = '<p>El kit estándar no está disponible.</p>';
        }
    }

    function renderColorOptions(kit) {
        subtitleStep2.textContent = `Selecciona el estilo para tu ${kit.nombre.toLowerCase()}`;
        colorsContainer.innerHTML = `<div class="spinner-border"></div>`;
        fetch(`../api/kit_opciones_color.php?kit_id=${kit.id}`, { headers: { 'Authorization': `Bearer ${token}` } })
        .then(res => res.json()).then(data => {
            colorsContainer.innerHTML = '';
            if (!data.ok) return;
            data.opciones.forEach(opcion => {
                const card = document.createElement('div');
                card.className = 'selection-card';
                card.dataset.colorNombre = opcion.color_nombre;
                card.innerHTML = `
                    <img src="${opcion.url_imagen_opcion}" alt="${opcion.nombre_opcion}">
                    <div class="card-overlay">
                        <h3 class="card-title">${opcion.nombre_opcion}</h3>
                    </div>
                `;
                card.addEventListener('click', () => {
                    selection.color = opcion;
                    renderGallery();
                    goToStep(3);
                });
                colorsContainer.appendChild(card);
            });
        });
    }

    function renderGallery() {
        titleStep3.textContent = `Tu Selección: ${selection.kit.nombre} ${selection.color.color_nombre}`;
        galleryContainer.innerHTML = `<div class="spinner-border"></div>`;
        fetch(`../api/acabados_imagenes.php?acabado_kit_id=${selection.kit.id}&color=${selection.color.color_nombre}`, { headers: { 'Authorization': `Bearer ${token}` } })
        .then(res => res.json()).then(data => {
            galleryContainer.innerHTML = '';
            if (!data.ok || data.imagenes.length === 0) {
                galleryContainer.innerHTML = '<p>No hay imágenes de detalle disponibles.</p>';
                return;
            }
            data.imagenes.forEach(img => {
                const galCard = document.createElement('div');
                galCard.className = 'gallery-card';
                galCard.innerHTML = `
                    <img src="${img.url_imagen}" alt="${img.componente}">
                    <div class="gallery-card-title">${img.componente}</div>
                `;
                galleryContainer.appendChild(galCard);
            });
        });
    }

    // --- EVENT LISTENERS --- //
    btnBackToStep1.addEventListener('click', () => goToStep(1));
    btnBackToStep2.addEventListener('click', () => goToStep(2));
    btnConfirm.addEventListener('click', () => {
        if (!selection.kit || !selection.color) {
            alert('Por favor, complete su selección.');
            return;
        }
        btnConfirm.disabled = true;
        btnConfirm.textContent = 'Guardando…';
        fetch('../api/guardar_seleccion_acabados.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify({
                propiedad_id: parseInt(propiedadId),
                kit_id: selection.kit.id,
                color: selection.color.color_nombre
            })
        })
        .then(res => res.json()).then(data => {
            alert(data.mensaje || 'Error');
            if(data.ok) { goToStep(1); /* O redirigir */ }
        })
        .catch(err => alert('Error de conexión.'))
        .finally(() => {
            btnConfirm.disabled = false;
            btnConfirm.textContent = 'Confirmar Selección';
        });
    });

    // --- INITIAL LOAD --- //
    kitsContainer.innerHTML = `<div class="spinner-border"></div>`;
    fetch(`../api/acabados_kits_disponibles.php?propiedad_id=${propiedadId}`, { headers: { 'Authorization': `Bearer ${token}` } })
    .then(res => res.json()).then(data => {
        if (!data.ok || data.kits.length === 0) {
            document.querySelector('.acabados-container').innerHTML = '<h2>No hay acabados disponibles para esta propiedad.</h2>';
            return;
        }
        renderKits(data.kits);
    });

    goToStep(1);
});
</script>

</body>
</html>