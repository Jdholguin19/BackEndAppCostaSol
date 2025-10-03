<?php
session_start();

// Obtener token desde POST (enviado por JavaScript)
$token = $_POST['token'] ?? null;

// Debug temporal
error_log("Token recibido en reporte general: " . ($token ? $token : 'NULL'));

if (!$token) {
    // Si no hay token, mostrar página de acceso denegado
    $showAccessDenied = true;
    error_log("No hay token en reporte general - acceso denegado");
} else {
    // Verificar si el usuario es responsable en la tabla responsable
    try {
        require_once __DIR__ . '/../config/db.php';
        $db = DB::getDB();
        
        $stmt = $db->prepare('SELECT id, nombre, correo FROM responsable WHERE token = ? AND estado = 1');
        $stmt->execute([$token]);
        $responsable = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("Consulta responsable en reporte general - Resultado: " . ($responsable ? 'ENCONTRADO' : 'NO ENCONTRADO'));
        if ($responsable) {
            error_log("Responsable encontrado - ID: " . $responsable['id'] . ", Nombre: " . $responsable['nombre']);
        }
        
        if (!$responsable) {
            $showAccessDenied = true;
            error_log("Responsable no encontrado en reporte general - acceso denegado");
        } else {
            $showAccessDenied = false;
            error_log("Responsable válido en reporte general - acceso permitido");
        }
    } catch (Exception $e) {
        $showAccessDenied = true;
        error_log("Error en consulta responsable reporte general: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte General del Sistema - CostaSol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style_report_general.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
</head>
<body>

<?php if ($showAccessDenied): ?>
<div class="access-denied">
  <i class="bi bi-shield-exclamation"></i>
  <h2>Acceso Denegado</h2>
  <p>No tienes permisos para acceder a esta página.<br>Solo los responsables pueden ver reportes generales del sistema.</p>
  <button class="btn btn-primary mt-3" onclick="window.close()">
    <i class="bi bi-x-circle"></i> Cerrar
  </button>
</div>

<?php else: ?>
    <div class="report-container">
        <!-- Header -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="report-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1><i class="bi bi-graph-up-arrow"></i> Reporte General del Sistema</h1>
                                <p class="mb-0">Análisis completo de todos los usuarios y actividades</p>
                            </div>
                            <div class="text-end">
                                <div class="report-date" id="fechaGeneracion">-</div>
                                <div class="report-period" id="periodoAnalisis">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div id="loadingContainer" class="text-center py-5">
                <div class="loading-spinner">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando datos del sistema...</p>
                </div>
            </div>

            <!-- Error Message -->
            <div id="errorContainer" class="error-message" style="display: none;"></div>

            <!-- Main Content -->
            <div id="mainContent" style="display: none;">
                
                <!-- Métricas Principales -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h3 class="section-title">
                            <i class="bi bi-speedometer2"></i>
                            Métricas Principales del Sistema
                        </h3>
                    </div>
                    
                    <!-- Total Usuarios -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="metric-card">
                            <div class="metric-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="metric-number" id="totalUsuarios">-</div>
                            <div class="metric-label">Total Usuarios</div>
                            <div class="metric-detail" id="usuariosDetalle">-</div>
                        </div>
                    </div>

                    <!-- Total Propiedades -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="metric-card">
                            <div class="metric-icon">
                                <i class="bi bi-house-door"></i>
                            </div>
                            <div class="metric-number" id="totalPropiedades">-</div>
                            <div class="metric-label">Total Propiedades</div>
                            <div class="metric-detail" id="propiedadesDetalle">-</div>
                        </div>
                    </div>

                    <!-- Total CTG -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="metric-card">
                            <div class="metric-icon">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div class="metric-number" id="totalCTG">-</div>
                            <div class="metric-label">Total CTG</div>
                            <div class="metric-detail" id="ctgDetalle">-</div>
                        </div>
                    </div>

                    <!-- Total PQR -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="metric-card">
                            <div class="metric-icon">
                                <i class="bi bi-question-circle"></i>
                            </div>
                            <div class="metric-number" id="totalPQR">-</div>
                            <div class="metric-label">Total PQR</div>
                            <div class="metric-detail" id="pqrDetalle">-</div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos Principales -->
                <div class="row mb-4">
                    <!-- Usuarios por Rol -->
                    <div class="col-lg-6 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-pie-chart"></i>
                                Distribución de Usuarios por Rol
                            </h5>
                            <canvas id="usuariosRolChart"></canvas>
                        </div>
                    </div>

                    <!-- Propiedades por Estado -->
                    <div class="col-lg-6 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-bar-chart"></i>
                                Propiedades por Estado
                            </h5>
                            <canvas id="propiedadesEstadoChart"></canvas>
                        </div>
                    </div>

                    <!-- CTG por Tipo -->
                    <div class="col-lg-6 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-stack"></i>
                                CTG por Tipo de Solicitud
                            </h5>
                            <canvas id="ctgTipoChart"></canvas>
                        </div>
                    </div>

                    <!-- PQR por Tipo -->
                    <div class="col-lg-6 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-stack"></i>
                                PQR por Tipo de Solicitud
                            </h5>
                            <canvas id="pqrTipoChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Análisis de Actividad -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h3 class="section-title">
                            <i class="bi bi-activity"></i>
                            Análisis de Actividad del Sistema
                        </h3>
                    </div>

                    <!-- Actividad Diaria -->
                    <div class="col-lg-8 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-graph-up"></i>
                                Actividad Diaria (Últimos 30 días)
                            </h5>
                            <canvas id="actividadDiariaChart"></canvas>
                        </div>
                    </div>

                    <!-- Actividad por Hora -->
                    <div class="col-lg-4 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-clock"></i>
                                Actividad por Hora del Día
                            </h5>
                            <canvas id="actividadHorariaChart"></canvas>
                        </div>
                    </div>

                    <!-- Usuarios Más Activos -->
                    <div class="col-lg-6 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-person-star"></i>
                                Usuarios Más Activos
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Tipo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usuariosActivosTable">
                                        <!-- Se llena dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Módulos Más Accedidos -->
                    <div class="col-lg-6 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-grid-3x3-gap"></i>
                                Módulos Más Accedidos
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Módulo</th>
                                            <th>Accesos</th>
                                            <th>Usuarios</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modulosAccesoTable">
                                        <!-- Se llena dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Métricas Financieras -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h3 class="section-title">
                            <i class="bi bi-currency-dollar"></i>
                            Análisis Financiero
                        </h3>
                    </div>

                    <!-- Paquetes Adicionales -->
                    <div class="col-lg-6 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-box-seam"></i>
                                Paquetes Adicionales Más Populares
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Paquete</th>
                                            <th>Selecciones</th>
                                            <th>Ingresos</th>
                                        </tr>
                                    </thead>
                                    <tbody id="paquetesPopularesTable">
                                        <!-- Se llena dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Kits de Acabados -->
                    <div class="col-lg-6 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-palette"></i>
                                Kits de Acabados Más Seleccionados
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Kit</th>
                                            <th>Selecciones</th>
                                            <th>Ingresos</th>
                                        </tr>
                                    </thead>
                                    <tbody id="kitsPopularesTable">
                                        <!-- Se llena dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Métricas de Rendimiento -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h3 class="section-title">
                            <i class="bi bi-speedometer"></i>
                            Métricas de Rendimiento
                        </h3>
                    </div>

                    <!-- Tiempos de Resolución -->
                    <div class="col-lg-6 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-stopwatch"></i>
                                Tiempos de Resolución
                            </h5>
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="metric-number" style="font-size: 1.5rem;" id="tiempoPromedioCTG">-</div>
                                    <div class="metric-label">Promedio CTG (hrs)</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="metric-number" style="font-size: 1.5rem;" id="tiempoPromedioPQR">-</div>
                                    <div class="metric-label">Promedio PQR (hrs)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas de Login -->
                    <div class="col-lg-6 mb-4">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="bi bi-person-check"></i>
                                Estadísticas de Acceso
                            </h5>
                            <div class="row text-center">
                                <div class="col-4 mb-3">
                                    <div class="metric-number" style="font-size: 1.5rem;" id="totalLogins">-</div>
                                    <div class="metric-label">Total Logins</div>
                                </div>
                                <div class="col-4 mb-3">
                                    <div class="metric-number" style="font-size: 1.5rem;" id="loginsExitosos">-</div>
                                    <div class="metric-label">Exitosos</div>
                                </div>
                                <div class="col-4 mb-3">
                                    <div class="metric-number" style="font-size: 1.5rem;" id="usuariosUnicos">-</div>
                                    <div class="metric-label">Usuarios Únicos</div>
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
                                    Actividad Reciente del Sistema
                                </h4>
                                <div id="actividadRecienteTable">
                                    <!-- Se llena dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php endif; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('Script cargado - Iniciando verificación de autenticación');

        // Verificar autenticación inmediatamente
        const token = localStorage.getItem('cs_token');

        console.log('Token encontrado:', token ? 'SÍ' : 'NO');

        if (!token) {
            console.log('No hay token, redirigiendo al login');
            window.location.href = 'login_front.php';
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
                loadReportData();
            })
            .catch(error => {
                console.error('Error en fetch:', error);
                showError('Error de autenticación');
            });
        }

        function showError(message) {
            document.getElementById('loadingContainer').style.display = 'none';
            document.getElementById('errorContainer').style.display = 'block';
            document.getElementById('errorContainer').textContent = message;
        }

        let reportData = null;

        // Colores para gráficos
        const chartColors = {
            primary: '#2d5a3d',
            secondary: '#4a7c59',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#3b82f6',
            light: '#f3f4f6',
            dark: '#1f2937'
        };

        const chartPalette = [
            chartColors.primary,
            chartColors.secondary,
            chartColors.success,
            chartColors.warning,
            chartColors.danger,
            chartColors.info,
            '#8b5cf6',
            '#ec4899',
            '#06b6d4',
            '#84cc16'
        ];

        // Cargar datos del reporte
        async function loadReportData() {
            const token = localStorage.getItem('cs_token');
            
            try {
                const response = await fetch('../api/general_report_data.php', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.ok) {
                    reportData = data;
                    populateReport();
                } else {
                    showError(data.mensaje || 'Error al cargar los datos del reporte');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Error de conexión al servidor');
            }
        }

        function populateReport() {
            document.getElementById('loadingContainer').style.display = 'none';
            document.getElementById('mainContent').style.display = 'block';

            // Fecha y período
            document.getElementById('fechaGeneracion').textContent = 
                `Generado: ${new Date(reportData.fecha_generacion).toLocaleString('es-ES')}`;
            document.getElementById('periodoAnalisis').textContent = 
                `Período: ${reportData.periodo_analisis}`;

            // Métricas principales
            populateMainMetrics();
            
            // Crear gráficos
            createCharts();
            
            // Poblar tablas
            populateTables();
        }

        function populateMainMetrics() {
            const metricas = reportData.metricas_generales;

            // Total usuarios
            const totalUsuarios = metricas.usuarios_por_rol.reduce((sum, rol) => sum + rol.total, 0);
            document.getElementById('totalUsuarios').textContent = totalUsuarios;
            document.getElementById('usuariosDetalle').innerHTML = 
                metricas.usuarios_por_rol.map(rol => `${rol.rol}: ${rol.total}`).join('<br>');

            // Total propiedades
            const totalPropiedades = metricas.propiedades_por_estado.reduce((sum, estado) => sum + estado.total, 0);
            document.getElementById('totalPropiedades').textContent = totalPropiedades;
            document.getElementById('propiedadesDetalle').innerHTML = 
                metricas.propiedades_por_estado.map(estado => `${estado.estado}: ${estado.total}`).join('<br>');

            // CTG
            document.getElementById('totalCTG').textContent = metricas.ctg.total_ctg;
            document.getElementById('ctgDetalle').innerHTML = 
                `Pendientes: ${metricas.ctg.pendientes}<br>Resueltas: ${metricas.ctg.resueltas}`;

            // PQR
            document.getElementById('totalPQR').textContent = metricas.pqr.total_pqr;
            document.getElementById('pqrDetalle').innerHTML = 
                `Pendientes: ${metricas.pqr.pendientes}<br>Resueltas: ${metricas.pqr.resueltas}`;

            // Tiempos de resolución
            const tiempoCTG = metricas.tiempos_resolucion.find(t => t.tipo === 'CTG');
            const tiempoPQR = metricas.tiempos_resolucion.find(t => t.tipo === 'PQR');
            
            document.getElementById('tiempoPromedioCTG').textContent = 
                tiempoCTG ? Math.round(tiempoCTG.tiempo_promedio_horas) : '-';
            document.getElementById('tiempoPromedioPQR').textContent = 
                tiempoPQR ? Math.round(tiempoPQR.tiempo_promedio_horas) : '-';

            // Login stats
            const login = metricas.login;
            document.getElementById('totalLogins').textContent = login.total_logins;
            document.getElementById('loginsExitosos').textContent = login.exitosos;
            document.getElementById('usuariosUnicos').textContent = login.usuarios_unicos;
        }

        function createCharts() {
            const metricas = reportData.metricas_generales;
            const actividad = reportData.analisis_actividad;

            // Usuarios por rol
            createPieChart('usuariosRolChart', metricas.usuarios_por_rol, 'rol', 'total');

            // Propiedades por estado
            createBarChart('propiedadesEstadoChart', metricas.propiedades_por_estado, 'estado', 'total');

            // CTG por tipo
            createBarChart('ctgTipoChart', metricas.ctg_por_tipo, 'tipo', 'total');

            // PQR por tipo
            createBarChart('pqrTipoChart', metricas.pqr_por_tipo, 'tipo', 'total');

            // Actividad diaria
            createLineChart('actividadDiariaChart', actividad.actividad_diaria, 'fecha', 'total_acciones');

            // Actividad horaria
            createBarChart('actividadHorariaChart', actividad.actividad_horaria, 'hora', 'total_acciones');
        }

        function createPieChart(canvasId, data, labelField, valueField) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.map(item => item[labelField]),
                    datasets: [{
                        data: data.map(item => item[valueField]),
                        backgroundColor: chartPalette.slice(0, data.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function createBarChart(canvasId, data, labelField, valueField) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => item[labelField]),
                    datasets: [{
                        label: 'Cantidad',
                        data: data.map(item => item[valueField]),
                        backgroundColor: chartColors.primary,
                        borderColor: chartColors.secondary,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function createLineChart(canvasId, data, labelField, valueField) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => new Date(item[labelField]).toLocaleDateString('es-ES')),
                    datasets: [{
                        label: 'Acciones',
                        data: data.map(item => item[valueField]),
                        borderColor: chartColors.primary,
                        backgroundColor: chartColors.primary + '20',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function populateTables() {
            const actividad = reportData.analisis_actividad;
            const financieras = reportData.metricas_financieras;

            // Usuarios más activos
            const usuariosActivosTable = document.getElementById('usuariosActivosTable');
            usuariosActivosTable.innerHTML = actividad.usuarios_mas_activos.map(usuario => `
                <tr>
                    <td>${usuario.nombre_usuario || 'N/A'}</td>
                    <td><span class="badge bg-${usuario.user_type === 'usuario' ? 'primary' : 'success'}">${usuario.user_type}</span></td>
                    <td>${usuario.total_acciones}</td>
                </tr>
            `).join('');

            // Módulos más accedidos
            const modulosAccesoTable = document.getElementById('modulosAccesoTable');
            modulosAccesoTable.innerHTML = actividad.modulos_mas_accedidos.map(modulo => `
                <tr>
                    <td>${modulo.action.replace('ACCESS_MODULE', '').replace('menu', '').trim()}</td>
                    <td>${modulo.total_accesos}</td>
                    <td>${modulo.usuarios_unicos}</td>
                </tr>
            `).join('');

            // Paquetes populares
            const paquetesPopularesTable = document.getElementById('paquetesPopularesTable');
            paquetesPopularesTable.innerHTML = financieras.paquetes_populares.map(paquete => `
                <tr>
                    <td>${paquete.nombre}</td>
                    <td>${paquete.total_selecciones}</td>
                    <td>$${paquete.ingresos_totales.toLocaleString()}</td>
                </tr>
            `).join('');

            // Kits populares
            const kitsPopularesTable = document.getElementById('kitsPopularesTable');
            kitsPopularesTable.innerHTML = financieras.kits_populares.map(kit => `
                <tr>
                    <td>${kit.nombre}</td>
                    <td>${kit.total_selecciones}</td>
                    <td>$${kit.ingresos_totales.toLocaleString()}</td>
                </tr>
            `).join('');

            // Actividad reciente
            const actividadRecienteTable = document.getElementById('actividadRecienteTable');
            actividadRecienteTable.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Acción</th>
                                <th>Total</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${actividad.actividad_reciente.slice(0, 20).map(act => `
                                <tr>
                                    <td>${act.action}</td>
                                    <td><span class="badge bg-primary">${act.total}</span></td>
                                    <td>${new Date(act.fecha).toLocaleDateString('es-ES')}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        function showError(message) {
            document.getElementById('loadingContainer').style.display = 'none';
            document.getElementById('errorContainer').style.display = 'block';
            document.getElementById('errorContainer').textContent = message;
        }

        // La inicialización se hace desde la autenticación
    </script>
</body>
</html>
