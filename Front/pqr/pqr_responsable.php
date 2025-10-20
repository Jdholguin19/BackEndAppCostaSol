<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear PQR para Cliente - CostaSol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style_pqr_nuevo.css" rel="stylesheet">
    <link href="../assets/css/style_main.css" rel="stylesheet">
    <style>
        .search-container {
            position: relative;
        }
        .pqr-form-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
            background-color: #fff;
        }
        .pqr-form-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ced4da;
            border-top: none;
            border-radius: 0 0 5px 5px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .search-result-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }
        .search-result-item:hover {
            background-color: #f8f9fa;
        }
        .search-result-item:last-child {
            border-bottom: none;
        }
        .search-result-name {
            font-weight: 500;
        }
        .search-result-info {
            font-size: 12px;
            color: #6c757d;
        }
        
        /* Mejoras para los selectores */
        .pqr-form-select {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 16px;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 14px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .pqr-form-select:hover {
            border-color: #007bff;
        }
        
        .pqr-form-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }
        
        .pqr-form-select option {
            padding: 12px;
            font-size: 15px;
        }
        
        .pqr-form-select option:hover {
            background-color: #f8f9fa;
        }
        
        /* Estilo para el estado disabled */
        .pqr-form-select:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body class="pqr-nuevo-page">

<div class="pqr-container">
  <div class="pqr-header">
    <h1 class="pqr-header-title">Crear PQR para Cliente</h1>
    <button class="pqr-back-btn" onclick="history.back()">
      <i class="bi bi-arrow-left"></i>
    </button>
  </div>

  <form id="frmPqr" enctype="multipart/form-data" class="pqr-form needs-validation" novalidate>
    <!-- cliente -->
    <label class="pqr-form-label">Cliente</label>
    <div class="search-container">
        <input type="text" id="userSearch" class="pqr-form-input" placeholder="Escriba el nombre del cliente..." autocomplete="off" required>
        <input type="hidden" id="userSel" name="id_usuario" required>
        <div id="userResults" class="search-results"></div>
    </div>

    <!-- propiedad -->
    <label class="pqr-form-label">Propiedad</label>
    <select id="propSel" name="id_propiedad" class="pqr-form-select" required>
        <option value="">Seleccione una propiedad...</option>
    </select>

    <!-- tipo -->
    <label class="pqr-form-label">Tipo de PQR</label>
    <select id="tipoSel" name="tipo_id" class="pqr-form-select" required>
        <option value="">Seleccione un tipo...</option>
    </select>

    <!-- descripción -->
    <label class="pqr-form-label">Descripción</label>
    <textarea id="txtDescripcion" name="descripcion" rows="4" class="pqr-form-textarea"
              placeholder="Describa detalladamente la petición, queja o reclamo..." required></textarea>

    <!-- adjunto -->
    <label class="pqr-form-label">Adjunto (opcional)</label>
    <div class="pqr-file-input-wrapper">
        <input type="file" id="fileArchivo" name="archivo" accept="image/*,application/pdf" class="pqr-file-input">
    </div>

    <div class="pqr-button-group">
      <button type="button" class="pqr-btn-cancel" onclick="history.back()">Cancelar</button>
      <button class="pqr-btn-submit" type="submit" id="btnOk">Crear PQR</button>
    </div>
  </form>
</div>

<?php 
$active_page = 'pqr';
include '../../api/bottom_nav.php';  
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Variables globales
        let selectedUserId = null;
        const pqrToken = localStorage.getItem('cs_token');
        let searchTimeout = null;

        // Elementos del DOM
        const userSearch = document.getElementById('userSearch');
        const userSel = document.getElementById('userSel');
        const userResults = document.getElementById('userResults');
        const propSel = document.getElementById('propSel');
        const tipoSel = document.getElementById('tipoSel');
        const txtDescripcion = document.getElementById('txtDescripcion');
        const fileArchivo = document.getElementById('fileArchivo');
        const btnOk = document.getElementById('btnOk');

        // Verificar token
        if (!pqrToken) {
            const container = document.querySelector('.pqr-container');
            if (container) {
                container.innerHTML = '<div class="alert alert-warning">Debes iniciar sesión para crear un PQR.</div>';
            }
        } else {
            // Cargar tipos de PQR
            fetch('../../api/pqr/tipo_pqr.php')
            .then(r => r.json())
            .then(d => {
                if (!d.ok) return;
                d.tipos.forEach(t => {
                    tipoSel.insertAdjacentHTML('beforeend',
                        `<option value="${t.id}">${t.nombre}</option>`);
                });
            });
        }

        // Función para buscar clientes
        function searchClients(query) {
            if (query.length < 2) {
                userResults.style.display = 'none';
                return;
            }

            fetch(`../../api/filtro_propiedad/buscar_clientes.php?q=${encodeURIComponent(query)}&limit=10`, {
                headers: { 'Authorization': `Bearer ${pqrToken}` }
            })
            .then(r => r.json())
            .then(d => {
                userResults.innerHTML = '';
                if (d.ok && d.clientes && d.clientes.length > 0) {
                    d.clientes.forEach(client => {
                        const div = document.createElement('div');
                        div.className = 'search-result-item';
                        div.innerHTML = `
                            <div class="search-result-name">${client.nombres} ${client.apellidos}</div>
                            <div class="search-result-info">Propiedades: ${client.total_propiedades}</div>
                        `;
                        div.addEventListener('click', () => {
                            selectClient(client);
                        });
                        userResults.appendChild(div);
                    });
                    userResults.style.display = 'block';
                } else {
                    userResults.style.display = 'none';
                }
            })
            .catch(() => {
                userResults.style.display = 'none';
            });
        }

        // Función para seleccionar cliente
        function selectClient(client) {
            selectedUserId = client.id;
            userSel.value = client.id;
            userSearch.value = `${client.nombres} ${client.apellidos}`;
            userSearch.dataset.selectedText = userSearch.value;
            userResults.style.display = 'none';

            // Limpiar propiedades anteriores
            propSel.innerHTML = '<option value="">Seleccione una propiedad...</option>';

            // Cargar propiedades del usuario seleccionado
            fetch(`../../api/obtener_propiedades.php?id_usuario=${selectedUserId}`, {
                headers: { 'Authorization': `Bearer ${pqrToken}` }
            })
            .then(r => r.json())
            .then(d => {
                if (!d.ok || !d.propiedades.length) {
                    alert('El cliente no tiene propiedades registradas');
                    return;
                }
                d.propiedades.forEach(p => {
                    propSel.insertAdjacentHTML('beforeend',
                        `<option value="${p.id}">${p.urbanizacion} — Mz ${p.manzana} / Villa ${p.solar}</option>`);
                });
            });
        }

        // Evento input en el campo de búsqueda
        userSearch.addEventListener('input', (e) => {
            const query = e.target.value.trim();

            // Limpiar selección anterior si el usuario está escribiendo
            if (query !== userSearch.dataset.selectedText) {
                selectedUserId = null;
                userSel.value = '';
                propSel.innerHTML = '<option value="">Seleccione una propiedad...</option>';
            }

            // Limpiar timeout anterior
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Establecer nuevo timeout para búsqueda
            searchTimeout = setTimeout(() => {
                searchClients(query);
            }, 300);
        });

        // Cerrar resultados al hacer click fuera
        document.addEventListener('click', (e) => {
            if (!userSearch.contains(e.target) && !userResults.contains(e.target)) {
                userResults.style.display = 'none';
            }
        });

        // Prevenir envío del formulario al presionar Enter en el campo de búsqueda
        userSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        // Evento submit del formulario
        document.getElementById('frmPqr').addEventListener('submit', e => {
            e.preventDefault();
            if (!e.target.checkValidity()) {
                e.target.classList.add('was-validated');
                return;
            }

            if (!selectedUserId) {
                alert('Por favor seleccione un cliente válido');
                userSearch.focus();
                return;
            }

            const fd = new FormData(e.target);

            btnOk.disabled = true;
            btnOk.textContent = 'Creando…';

            fetch('../../api/pqr/pqr_create.php', {
                method: 'POST',
                body: fd,
                headers: { 'Authorization': `Bearer ${pqrToken}` }
            })
            .then(r => r.json())
            .then(d => {
                if (d.ok) {
                    alert(`PQR creado exitosamente. Número: ${d.numero}`);
                    location.href = 'pqr.php';
                } else {
                    alert(d.msg || 'No se pudo crear el PQR');
                    btnOk.disabled = false;
                    btnOk.textContent = 'Crear PQR';
                }
            })
            .catch(() => {
                alert('Error del servidor');
                btnOk.disabled = false;
                btnOk.textContent = 'Crear PQR';
            });
        });
    </script>
</body>
</html>
