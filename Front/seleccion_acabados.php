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
<link href="//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.css" type="text/css" rel="stylesheet" />
<style>
    /* Nuevos estilos para la Etapa 4 y el Modal */
    #step4 .summary-table {
        text-align: left;
        max-width: 400px;
        margin: 20px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    #step4 .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    #step4 .summary-row:last-child {
        border-bottom: none;
    }
    #step4 .summary-label {
        color: #6c757d;
    }
    #step4 .summary-value {
        font-weight: 600;
        color: #343a40;
    }
    #step4 .summary-total {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d5a3d;
    }
    .packages-section {
        margin-top: 40px;
    }
    .packages-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .packages-scroll-container {
        display: flex;
        overflow-x: auto;
        padding-bottom: 15px;
        gap: 15px;
    }
    .package-card {
        flex: 0 0 150px;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .package-card:hover {
        border-color: #2d5a3d;
        transform: translateY(-3px);
    }
    .package-card.added {
        border-color: #2d5a3d;
        background-color: #eaf3ed;
    }
    .package-card img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
    }
    .package-name {
        font-weight: 600;
        margin-top: 10px;
        font-size: 0.9rem;
    }
    .package-action {
        font-size: 0.8rem;
        color: #2d5a3d;
        font-weight: 600;
    }

    /* Modal Styles */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1050;
    }
    .modal-content {
        background-color: white;
        padding: 25px;
        border-radius: 15px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    .modal-title {
        font-size: 1.5rem;
        font-weight: 600;
    }
    .modal-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d5a3d;
    }
    .modal-body .carousel-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 10px;
    }
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
</style>
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

    <!-- Step Indicator -->
    <div class="step-indicator">
        <div id="indicator-1" class="step-number">1</div>
        <div class="step-line"></div>
        <div id="indicator-2" class="step-number">2</div>
        <div class="step-line"></div>
        <div id="indicator-3" class="step-number">3</div>
        <div class="step-line"></div>
        <div id="indicator-4" class="step-number">4</div>
        <div class="step-line"></div>
        <div id="indicator-5" class="step-number">5</div>
    </div>

    <!-- Step 1: Kit Selection -->
    <div id="step1">
        <div class="acabados-header-text">
            <h2 class="main-title">Configura Tu Cocina Ideal</h2>
            <p class="subtitle">Elige el tipo de cocina que mejor se adapte a tus necesidades</p>
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
        <div id="colors-container" class="kits-container">
            <!-- Color cards will be injected here -->
        </div>
        <div class="action-buttons-step">
            <button id="btn-back-to-step1" class="btn-back"><i class="bi bi-arrow-left"></i> Volver</button>
        </div>
    </div>

    <!-- Step 3: Component Gallery -->
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
            <button id="btn-go-to-step4" class="btn-confirm">Siguiente <i class="bi bi-arrow-right"></i></button>
        </div>
    </div>

    <!-- Step 4: Pre-Order Summary -->
    <div id="step4" style="display: none;">
        <div class="acabados-header-text">
            <h2 class="main-title">Confirma tu Selección</h2>
        </div>
        <div id="summary-container" class="summary-table">
            <!-- Summary will be injected here -->
        </div>
        
        <div class="packages-section">
            <h3 class="packages-title">Paquetes Adicionales</h3>
            <div id="packages-container" class="packages-scroll-container">
                <!-- Additional packages will be injected here -->
            </div>
        </div>

        <div class="action-buttons-step">
            <button id="btn-back-to-step3" class="btn-back"><i class="bi bi-arrow-left"></i> Volver</button>
            <button id="btn-go-to-step5" class="btn-confirm">Siguiente <i class="bi bi-arrow-right"></i></button>
        </div>
    </div>

    <!-- Step 5: Final Summary -->
    <div id="step5" style="display: none;">
        <div class="acabados-header-text">
            <h2 class="main-title">Resumen de tu Selección</h2>
        </div>
        <div id="final-summary-container" class="summary-table">
            <!-- Final summary will be injected here -->
        </div>
        <div class="action-buttons-step">
            <button id="btn-back-to-step4" class="btn-back"><i class="bi bi-arrow-left"></i> Volver</button>
            <button id="btn-pre-order-final" class="btn-confirm">Pre-Ordenar</button>
        </div>
    </div>

</div>

<!-- Package Detail Modal -->
<div id="package-modal" class="modal-backdrop">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h3 id="modal-title" class="modal-title"></h3>
                <p id="modal-price" class="modal-price"></p>
            </div>
            <button type="button" class="btn-close" id="modal-close-btn"></button>
        </div>
        <div class="modal-body">
            <div id="modal-carousel" class="carousel slide">
              <div id="modal-carousel-inner" class="carousel-inner">
                <!-- Carousel items will be injected here -->
              </div>
              <button class="carousel-control-prev" type="button" data-bs-target="#modal-carousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#modal-carousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>
            <p id="modal-description" class="mt-3"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="modal-close-footer-btn">Cerrar</button>
            <button type="button" class="btn btn-primary" id="modal-add-btn">Añadir</button>
        </div>
    </div>
</div>


<?php 
$active_page = 'inicio';
include '../api/bottom_nav.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="//code.jquery.com/jquery-latest.js"></script>
<script src="//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- REFS --- //
    const u = JSON.parse(localStorage.getItem('cs_usuario') || '{}');
    const token = localStorage.getItem('cs_token');
    const urlParams = new URLSearchParams(window.location.search);
    const propiedadId = urlParams.get('propiedad_id');
    const storageKey = `acabados_draft_${propiedadId}`;

    if (!u.id || !token || !propiedadId) {
        document.querySelector('.acabados-container').innerHTML = '<h2>Error: No se ha especificado una propiedad.</h2>';
        return;
    }

    // --- Step Refs ---
    const steps = [
        document.getElementById('step1'),
        document.getElementById('step2'),
        document.getElementById('step3'),
        document.getElementById('step4'),
        document.getElementById('step5')
    ];
    const indicators = [
        document.getElementById('indicator-1'),
        document.getElementById('indicator-2'),
        document.getElementById('indicator-3'),
        document.getElementById('indicator-4'),
        document.getElementById('indicator-5')
    ];

    // --- Element Refs ---
    const kitsContainer = document.getElementById('kits-container');
    const colorsContainer = document.getElementById('colors-container');
    const galleryContainer = document.getElementById('gallery-container');
    const summaryContainer = document.getElementById('summary-container');
    const packagesContainer = document.getElementById('packages-container');
    const finalSummaryContainer = document.getElementById('final-summary-container');
    
    const subtitleStep2 = document.getElementById('subtitle-step2');
    const titleStep3 = document.getElementById('title-step3');

    // --- Button Refs ---
    const btnBackToStep1 = document.getElementById('btn-back-to-step1');
    const btnBackToStep2 = document.getElementById('btn-back-to-step2');
    const btnGoToStep4 = document.getElementById('btn-go-to-step4');
    const btnBackToStep3 = document.getElementById('btn-back-to-step3');
    const btnGoToStep5 = document.getElementById('btn-go-to-step5');
    const btnBackToStep4 = document.getElementById('btn-back-to-step4');
    const btnPreOrderFinal = document.getElementById('btn-pre-order-final');

    // --- Modal Refs ---
    const modal = document.getElementById('package-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalPrice = document.getElementById('modal-price');
    const modalCarouselInner = document.getElementById('modal-carousel-inner');
    const modalDescription = document.getElementById('modal-description');
    const modalAddBtn = document.getElementById('modal-add-btn');
    
    // --- State Management ---
    let currentStep = 1;
    let allKits = [];
    let allPackages = [];
    let selection = {
        kit: null,
        color: null,
        addedPackages: new Map()
    };

    // --- State Persistence --- //
    function saveState() {
        const state = {
            currentStep: currentStep,
            selection: {
                ...selection,
                addedPackages: Array.from(selection.addedPackages.entries()) // Convert Map to Array for JSON
            }
        };
        localStorage.setItem(storageKey, JSON.stringify(state));
    }

    function loadState() {
        const savedState = localStorage.getItem(storageKey);
        if (savedState) {
            const parsedState = JSON.parse(savedState);
            currentStep = parsedState.currentStep || 1;
            selection = {
                ...parsedState.selection,
                addedPackages: new Map(parsedState.selection.addedPackages || []) // Convert Array back to Map
            };
            return true;
        }
        return false;
    }

    function clearState() {
        localStorage.removeItem(storageKey);
    }

    // --- NAVIGATION LOGIC --- //
    function goToStep(stepNumber) {
        currentStep = stepNumber;
        steps.forEach((step, index) => {
            step.style.display = (index + 1 === stepNumber) ? 'block' : 'none';
        });
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index + 1 === stepNumber);
        });
        if (stepNumber === 4) {
            renderStep4();
        }
        if (stepNumber === 5) {
            renderStep5();
        }
        saveState();
    }

    // --- DATA FETCHING & RENDERING --- //
    function createKitCard(kit) {
        kitsContainer.innerHTML = ''; // Clear the container first
        const card = document.createElement('div');
        card.className = 'selection-card';
        card.innerHTML = `
            <div class="card-image-wrapper">
                <img src="${kit.url_imagen_principal}" alt="${kit.nombre}">
                <div class="card-overlay"><h3 class="card-title">${kit.nombre}</h3></div>
            </div>
            <p class="card-description">${kit.descripcion}</p>
        `;
        card.addEventListener('click', () => {
            selection.kit = kit;
            renderColorOptions(kit);
            goToStep(2);
        });
        kitsContainer.appendChild(card);
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
                card.innerHTML = `
                    <img src="${opcion.url_imagen_opcion}" alt="${opcion.nombre_opcion}">
                    <div class="card-overlay"><h3 class="card-title">${opcion.nombre_opcion}</h3></div>
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
        if (!selection.kit || !selection.color) return; // Safety check
        titleStep3.textContent = `Tu Selección: ${selection.kit.nombre} ${selection.color.nombre_opcion}`;
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

    function renderStep4() {
        const kitCost = parseFloat(selection.kit.costo) || 0;
        let total = kitCost;
        
        let summaryHTML = `
            <div class="summary-row">
                <span class="summary-label">Tienes el modelo</span>
                <span class="summary-value">${selection.kit.nombre}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Color</span>
                <span class="summary-value">${selection.color.nombre_opcion}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Costo Kit</span>
                <span class="summary-value">${kitCost > 0 ? '$ ' + kitCost.toFixed(2) : 'Incluido'}</span>
            </div>
        `;
        
        selection.addedPackages.forEach(pkg => {
            const pkgPrice = parseFloat(pkg.precio) || 0;
            total += pkgPrice;
            summaryHTML += `
                <div class="summary-row">
                    <span class="summary-label">${pkg.nombre}</span>
                    <span class="summary-value">$ ${pkgPrice.toFixed(2)}</span>
                </div>
            `;
        });

        summaryHTML += `
            <div class="summary-row">
                <span class="summary-label summary-total">Total:</span>
                <span class="summary-value summary-total">$ ${total.toFixed(2)}</span>
            </div>
        `;
        summaryContainer.innerHTML = summaryHTML;

        packagesContainer.innerHTML = '';
        const isFullKitSelected = selection.kit.id == 2;
        
        const switchableKit = isFullKitSelected ? allKits.find(k => k.id == 1) : allKits.find(k => k.id == 2);
        if(switchableKit) {
            const card = document.createElement('div');
            card.className = 'package-card';
            card.innerHTML = `
                <img src="${switchableKit.url_imagen_principal}" alt="${switchableKit.nombre}">
                <p class="package-name">${switchableKit.nombre}</p>
                <span class="package-action">Cambiar a este kit</span>
            `;
            card.addEventListener('click', () => {
                selection.kit = switchableKit;
                selection.addedPackages.clear(); // Clear packages when switching main kit
                renderColorOptions(selection.kit);
                goToStep(2);
            });
            packagesContainer.appendChild(card);
        }

        allPackages.forEach(pkg => {
            const card = document.createElement('div');
            card.className = 'package-card';
            if(selection.addedPackages.has(pkg.id)) {
                card.classList.add('added');
            }
            card.innerHTML = `
                <img src="${(pkg.fotos && pkg.fotos.length > 0) ? pkg.fotos[0] : 'https://placehold.co/300x200'}" alt="${pkg.nombre}">
                <p class="package-name">${pkg.nombre}</p>
                <span class="package-action">Ver más</span>
            `;
            card.addEventListener('click', () => renderPackageModal(pkg));
            packagesContainer.appendChild(card);
        });
        saveState();
    }

    function renderStep5() {
        const kitCost = parseFloat(selection.kit.costo) || 0;
        let total = kitCost;

        let summaryHTML = `
            <div class="summary-row">
                <span class="summary-label">Tienes el modelo</span>
                <span class="summary-value">${selection.kit.nombre}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Color</span>
                <span class="summary-value">${selection.color.nombre_opcion}</span>
            </div>
            <div class="summary-visual-row">
                <a href="${selection.color.url_imagen_opcion}" data-featherlight="image">
                    <img src="${selection.color.url_imagen_opcion}" class="summary-main-image" alt="${selection.color.nombre_opcion}" onerror="this.style.display='none'">
                </a>
            </div>
        `;

        selection.addedPackages.forEach(pkg => {
            const pkgPrice = parseFloat(pkg.precio) || 0;
            total += pkgPrice;
            summaryHTML += `
                <div class="summary-row">
                    <span class="summary-label">${pkg.nombre}</span>
                    <span class="summary-value">$ ${pkgPrice.toFixed(2)}</span>
                </div>
            `;
            if (pkg.fotos && pkg.fotos.length > 0) {
                summaryHTML += `<div class="summary-visual-row">`;
                summaryHTML += `<div class="summary-package-gallery">`;
                pkg.fotos.forEach(foto => {
                    summaryHTML += `
                        <a href="${foto}" data-featherlight="image">
                            <img src="${foto}" class="summary-gallery-image" alt="Foto de ${pkg.nombre}" onerror="this.style.display='none'">
                        </a>
                    `;
                });
                summaryHTML += `</div></div>`;
            }
        });

        summaryHTML += `
            <div class="summary-row summary-total-row">
                <span class="summary-label summary-total">Total:</span>
                <span class="summary-value summary-total">$ ${total.toFixed(2)}</span>
            </div>
        `;
        finalSummaryContainer.innerHTML = summaryHTML;
    }

    function renderPackageModal(pkg) {
        modalTitle.textContent = pkg.nombre;
        modalPrice.textContent = `$ ${parseFloat(pkg.precio).toFixed(2)}`;
        modalDescription.textContent = pkg.descripcion;

        modalCarouselInner.innerHTML = '';
        (pkg.fotos && pkg.fotos.length > 0 ? pkg.fotos : ['https://placehold.co/600x400']).forEach((foto, index) => {
            const item = document.createElement('div');
            item.className = `carousel-item ${index === 0 ? 'active' : ''}`;
            item.innerHTML = `<img src="${foto}" class="d-block w-100" alt="Foto de ${pkg.nombre}">`;
            modalCarouselInner.appendChild(item);
        });

        if (selection.addedPackages.has(pkg.id)) {
            modalAddBtn.textContent = 'Quitar';
            modalAddBtn.classList.remove('btn-primary');
            modalAddBtn.classList.add('btn-danger');
        } else {
            modalAddBtn.textContent = 'Añadir';
            modalAddBtn.classList.remove('btn-danger');
            modalAddBtn.classList.add('btn-primary');
        }
        
        modalAddBtn.onclick = () => {
            if (selection.addedPackages.has(pkg.id)) {
                selection.addedPackages.delete(pkg.id);
            } else {
                selection.addedPackages.set(pkg.id, pkg);
            }
            closeModal();
            renderStep4();
        };

        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }
    
    // --- EVENT LISTENERS --- //
    btnBackToStep1.addEventListener('click', () => goToStep(1));
    btnBackToStep2.addEventListener('click', () => goToStep(2));
    btnGoToStep4.addEventListener('click', () => goToStep(4));
    btnBackToStep3.addEventListener('click', () => goToStep(3));
    btnGoToStep5.addEventListener('click', () => goToStep(5));
    btnBackToStep4.addEventListener('click', () => goToStep(4));
    
    [document.getElementById('modal-close-btn'), document.getElementById('modal-close-footer-btn')].forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    btnPreOrderFinal.addEventListener('click', () => {
        if (!selection.kit || !selection.color) {
            alert('Error: No se ha completado la selección principal.');
            return;
        }

        const finalSelection = {
            propiedad_id: parseInt(propiedadId),
            kit_id: selection.kit.id,
            color: selection.color.color_nombre,
            paquetes_adicionales: Array.from(selection.addedPackages.keys())
        };

        btnPreOrderFinal.disabled = true;
        btnPreOrderFinal.textContent = 'Guardando…';
        btnBackToStep4.disabled = true;

        fetch('../api/guardar_seleccion_acabados.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(finalSelection)
        })
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                clearState(); // Clear draft on success

                // Hide the button container
                btnPreOrderFinal.parentElement.style.display = 'none';

                // Display a success message on the page
                const successMessage = document.createElement('div');
                successMessage.className = 'alert alert-success text-center mt-4';
                successMessage.innerHTML = '<strong>¡Éxito!</strong> Tu selección ha sido guardada.';
                finalSummaryContainer.insertAdjacentElement('afterend', successMessage);
            } else {
                alert(`Error al guardar: ${data.mensaje || 'Error desconocido.'}`);
                btnPreOrderFinal.disabled = false;
                btnPreOrderFinal.textContent = 'Pre-Ordenar';
                btnBackToStep4.disabled = false;
            }
        })
        .catch(err => {
            console.error('Error al guardar la selección:', err);
            alert('Hubo un error de conexión al intentar guardar tu selección.');
            btnPreOrderFinal.disabled = false;
            btnPreOrderFinal.textContent = 'Pre-Ordenar';
            btnBackToStep4.disabled = false;
        });
    });

    // --- INITIAL LOAD --- //
    function initialLoad() {
        // Primero, verificar si ya existe una selección guardada en el servidor
        fetch(`../api/acabado_seleccion_guardada.php?propiedad_id=${propiedadId}`, { headers: { 'Authorization': `Bearer ${token}` } })
        .then(res => res.json())
        .then(serverData => {
            if (serverData.ok && serverData.seleccionGuardada) {
                // Si hay una selección guardada, mostrar el resumen final y bloquear
                selection.kit = serverData.data.kit;
                selection.color = serverData.data.color;
                selection.addedPackages = new Map(serverData.data.packages.map(p => [p.id, p]));

                renderStep5();
                goToStep(5);

                // Ocultar botones y mostrar mensaje de estado final
                const actionButtons = document.querySelector('#step5 .action-buttons-step');
                if(actionButtons) actionButtons.style.display = 'none';
                
                const finalMessage = document.createElement('div');
                finalMessage.className = 'alert alert-info text-center mt-4';
                finalMessage.textContent = 'Dentro de poco te contactaremos para confirmar tu selección y continuar con el proceso. Si tienes alguna pregunta, no dudes en contactarnos. ¡Gracias!';
                finalSummaryContainer.insertAdjacentElement('afterend', finalMessage);

            } else {
                // Si no hay selección guardada, proceder con el flujo normal
                startSelectionFlow();
            }
        })
        .catch(err => {
            console.error("Error checking saved selection:", err);
            document.querySelector('.acabados-container').innerHTML = '<h2>Error al verificar el estado de su selección.</h2>';
        });
    }

    function startSelectionFlow() {
        kitsContainer.innerHTML = `<div class="spinner-border"></div>`;
        const kitsPromise = fetch(`../api/acabados_kits_disponibles.php?propiedad_id=${propiedadId}`, { headers: { 'Authorization': `Bearer ${token}` } }).then(res => res.json());
        const packagesPromise = fetch(`../api/paquetes_adicionales.php`, { headers: { 'Authorization': `Bearer ${token}` } }).then(res => res.json());

        Promise.all([kitsPromise, packagesPromise])
        .then(([kitsData, packagesData]) => {
            kitsContainer.innerHTML = ''; // Clear loading spinner
            if (!kitsData.ok || kitsData.kits.length === 0) {
                document.querySelector('.acabados-container').innerHTML = '<h2>No hay acabados disponibles para esta propiedad.</h2>';
                return;
            }
            allKits = kitsData.kits;
            
            if(packagesData.ok) {
                allPackages = packagesData.paquetes;
            }

            const hasSavedState = loadState();

            if (hasSavedState && selection.kit) {
                // A valid state was loaded, re-render everything needed
                createKitCard(selection.kit); 
                if (selection.color) {
                    renderColorOptions(selection.kit);
                    renderGallery();
                }
                goToStep(currentStep);
            } else {
                // Start fresh
                clearState(); // Clear any potentially corrupt state
                const standardKit = allKits.find(kit => kit.id == 1);
                if (standardKit) {
                    selection.kit = standardKit;
                    createKitCard(standardKit);
                } else {
                     kitsContainer.innerHTML = '<p>El kit estándar no está disponible.</p>';
                }
                goToStep(1);
            }
        })
        .catch(err => {
            console.error("Error on initial load:", err);
            document.querySelector('.acabados-container').innerHTML = '<h2>Error al cargar los datos.</h2>';
        });
    }

    initialLoad();
});
</script>

</body>
</html>