<?php
// Incluir el archivo de conexión a la base de datos
require_once __DIR__ . '/../config/db.php';

// Manejo del formulario para añadir nuevas tareas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_tarea'])) {
    $fase_id = $_POST['fase_id'];
    $titulo_tarea = $_POST['titulo_tarea'];
    $descripcion_tarea = $_POST['descripcion_tarea'];

    // Validar y sanitizar entradas
    $fase_id = filter_var($fase_id, FILTER_VALIDATE_INT);
    $titulo_tarea = filter_var($titulo_tarea, FILTER_SANITIZE_STRING);
    $descripcion_tarea = filter_var($descripcion_tarea, FILTER_SANITIZE_STRING);

    if ($fase_id && !empty($titulo_tarea) && !empty($descripcion_tarea)) {
        try {
            $stmt = $conn->prepare("INSERT INTO cronograma_tareas (fase_id, titulo, descripcion) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $fase_id, $titulo_tarea, $descripcion_tarea);
            $stmt->execute();

            // Redirigir para evitar reenvío del formulario
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            // Manejo de errores de base de datos
            error_log("Error al insertar tarea: " . $e->getMessage());
            // Opcional: mostrar un mensaje de error al usuario
        }
    }
}

// Leer los datos de la base de datos para mostrar en la página
$fases = [];
$all_dates = [];

try {
    // 1. Obtener todas las fases
    $result_fases = $conn->query("SELECT * FROM cronograma_fases ORDER BY fecha_inicio");
    $fases_db = $result_fases->fetch_all(MYSQLI_ASSOC);

    // 2. Obtener todas las tareas
    $result_tareas = $conn->query("SELECT * FROM cronograma_tareas");
    $tareas_db = $result_tareas->fetch_all(MYSQLI_ASSOC);

    // 3. Agrupar tareas por fase_id
    $tareas_agrupadas = [];
    foreach ($tareas_db as $tarea) {
        $tareas_agrupadas[$tarea['fase_id']][] = $tarea;
    }

    // 4. Combinar fases y tareas en la estructura de datos final
    foreach ($fases_db as $fase) {
        $fase['tareas'] = isset($tareas_agrupadas[$fase['id']]) ? $tareas_agrupadas[$fase['id']] : [];
        $fases[] = $fase;
        $all_dates[] = $fase['fecha_inicio'];
        $all_dates[] = $fase['fecha_fin'];
    }

} catch (Exception $e) {
    error_log("Error al leer datos del cronograma: " . $e->getMessage());
    die("Error al conectar con la base de datos. Por favor, revise los logs.");
}


// Calcular fechas para el Gantt
if (!empty($all_dates)) {
    $start_date = new DateTime(min($all_dates));
    $end_date = new DateTime('2025-10-31'); // Fixed end date for the Gantt chart
    $total_days = $end_date->diff($start_date)->days;
} else {
    // Valores por defecto si no hay datos
    $start_date = new DateTime();
    $end_date = new DateTime('+3 months');
    $total_days = 90;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronograma de Desarrollo</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f7f6; color: #333; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        h1, h2, h3 { text-align: center; color: #2d5a3d; font-family: 'Playfair Display', serif; }
        .title-header h1 { margin: 0; font-size: 2.2em; }
        .title-header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 40px;
            gap: 25px; /* Space between title and button */
        }
        h2 { margin-top: 50px; border-bottom: 2px solid #e9ecef; padding-bottom: 10px; }

        /* Gantt Chart Styles */
        .gantt-container { overflow-x: auto; padding: 20px 0; border: 1px solid #e9ecef; border-radius: 8px; }
        .gantt-chart { position: relative; display: grid; border-radius: 5px; min-width: 900px; }
        .gantt-row { display: contents; }
        .gantt-phase-label {
            background-color: #f8f9fa;
            padding: 15px;
            font-weight: bold;
            text-align: right;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .gantt-bar-container { padding: 15px 10px; border-bottom: 1px solid #e9ecef; position: relative; }
        .gantt-bar {
            position: absolute;
            height: 28px;
            background-color: #2d5a3d;
            border-radius: 5px;
            transition: filter 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            min-width: 5px;
        }
        .gantt-bar:hover { filter: brightness(1.2); }
        .gantt-bar-label {
            position: absolute;
            color: #343a40;
            font-size: 12px;
            font-weight: 600;
            line-height: 28px; /* match bar height */
            white-space: nowrap;
            pointer-events: none; /* To allow hover on the bar underneath */
        }

        .gantt-row-header {
            display: contents;
            font-size: 12px;
            color: #6c757d;
        }
        .gantt-timeline-markers {
            position: relative;
            border-bottom: 1px solid #e9ecef;
            height: 40px; /* Give header some height */
        }
        .gantt-marker {
            position: absolute;
            top: 0;
            height: 100%;
            border-left: 1px dashed #ced4da;
            padding-left: 5px;
        }
        .gantt-marker span {
            position: absolute;
            top: 5px;
            transform: translateX(-50%);
            margin-left: 1px;
        }

        /* Timeline Styles */
        .timeline { position: relative; padding: 20px 0; }
        .timeline::before { content: ''; position: absolute; top: 0; left: 20px; height: 100%; width: 4px; background: #e9ecef; border-radius: 2px; }
        .timeline-item { margin-bottom: 40px; position: relative; padding-left: 60px; }
        .timeline-item::before { content: ''; position: absolute; left: 11px; top: 5px; width: 20px; height: 20px; border-radius: 50%; background-color: #2d5a3d; border: 4px solid #f4f7f6; z-index: 1; }
        .timeline-item-header { margin-bottom: 15px; }
        .timeline-item-header h3 { font-size: 1.5em; color: #2d5a3d; margin: 0 0 5px 0; text-align: left; }
        .timeline-item-header .date-range { font-weight: 600; color: #555; font-size: 0.9em; }
        .task { background-color: #f8f9fa; border-left: 4px solid #2d5a3d; padding: 15px; margin-bottom: 15px; border-radius: 0 5px 5px 0; }
        .task h4 { margin: 0 0 10px 0; color: #343a40; }
        .task p { margin: 0; font-size: 0.95em; }

        /* Form Styles */
        .form-container { background: #f8f9fa; padding: 25px; border-radius: 8px; margin-top: 30px; border: 1px solid #e9ecef; }
        .form-container h3 { margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box; }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .submit-btn { background-color: #2d5a3d; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s; }
        .submit-btn:hover { background-color: #1e3c28; }

        .btn-external-link {
            padding: 8px 20px; /* Smaller button */
            background-color: #2d5a3d;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 15px; /* Smaller font */
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            flex-shrink: 0;
            /* margin-left is removed, gap is used now */
        }
        .btn-external-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            background-color: #1e3c28;
        }

/* Popover Styles */
.popover-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.popover {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    max-width: 400px;
    width: 90%;
    padding: 20px;
    position: relative;
    animation: fadeIn 0.3s ease-out;
}
.popover-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}
.popover-header h4 {
    margin: 0;
    color: #2d5a3d;
    font-size: 1.3em;
}
.popover-close-btn {
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
    color: #666;
    transition: color 0.2s;
}
.popover-close-btn:hover {
    color: #333;
}
.popover-content {
    max-height: 400px; /* Increased height */
    overflow-y: auto;
    padding-right: 10px; /* Add some padding for the scrollbar */
    /* Removed temporary border */
}
.popover-content ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.popover-content ul li {
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px dashed #eee;
}
.popover-content ul li:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.popover-content ul li strong {
    color: #333;
    display: block;
    margin-bottom: 5px;
}
.popover-content ul li p {
    font-size: 0.9em;
    color: #555;
    margin: 0;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
        <div class="title-header">
        <h1>Cronograma de Desarrollo</h1>
        <a href="https://app.costasol.com.ec/" class="btn-external-link" target="_blank" rel="noopener noreferrer">Ir App CostaSol</a>
    </div>

    <!-- Gantt Chart -->
    <h2>Gantt de Tiempo</h2>
    <div class="gantt-container">
        <div class="gantt-chart" style="grid-template-columns: 320px 1fr;">

            <!-- Timeline Header -->
            <div class="gantt-row-header">
                <div class="gantt-phase-label"></div> <!-- Empty cell -->
                <div class="gantt-timeline-markers">
                    <?php
                    $period = new DatePeriod($start_date, new DateInterval('P1W'), $end_date);
                    foreach ($period as $week_start) {
                        $offset = $start_date->diff($week_start)->days;
                        if ($total_days > 0) {
                            $left = ($offset / $total_days) * 100;
                            if ($left <= 100) {
                                echo '<div class="gantt-marker" style="left: ' . $left . '%;">';
                                echo '<span>' . $week_start->format('d/m') . '</span>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <?php foreach ($fases as $index => $fase): ?>
                <div class="gantt-row">
                    <div class="gantt-phase-label" title="<?= htmlspecialchars($fase['titulo']) ?>"><?= htmlspecialchars($fase['titulo']) ?></div>
                    <div class="gantt-bar-container">
                        <?php
                        $fase_start = new DateTime($fase['fecha_inicio']);
                        $fase_end = new DateTime($fase['fecha_fin']);
                        $offset = $start_date->diff($fase_start)->days;
                        $duration = $fase_start->diff($fase_end)->days;
                        $left = ($offset / $total_days) * 100;
                        $width = ($duration / $total_days) * 100;

                        $bar_style = '';
                        if ($fase['titulo'] === 'Tareas pendientes' || $fase['titulo'] === 'Proximos proyectos') {
                            $bar_style = 'background-color: #ffc107;';
                        }
                        ?>
                        <div class="gantt-bar" style="left: <?= $left ?>%; width: <?= $width ?>%; <?= $bar_style ?>" title="<?= htmlspecialchars($fase['titulo']) . ' (' . $fase['fecha_inicio'] . ' - ' . $fase['fecha_fin'] . ')' ?>" data-phase-index="<?= $index ?>"></div>
                        <span class="gantt-bar-label" style="left: calc(<?= $left ?>% + <?= $width ?>% + 8px);"><?= (new DateTime($fase['fecha_inicio']))->format('d/m') . ' - ' . (new DateTime($fase['fecha_fin']))->format('d/m') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Timeline -->
    <h2>Línea de Tiempo Detallada</h2>
    <div class="timeline">
        <?php foreach ($fases as $fase): ?>
            <div class="timeline-item">
                <div class="timeline-item-header">
                    <h3><?= htmlspecialchars($fase['titulo']) ?></h3>
                    <div class="date-range"><?= htmlspecialchars($fase['fecha_inicio']) ?> – <?= htmlspecialchars($fase['fecha_fin']) ?></div>
                </div>
                <?php foreach ($fase['tareas'] as $tarea): ?>
                    <?php
                    $task_style = '';
                    if ($fase['titulo'] === 'Tareas pendientes' || $fase['titulo'] === 'Proximos proyectos') {
                        $task_style = 'style="background-color: #fffbe6; border-left-color: #ffc107;"';
                    }
                    ?>
                    <div class="task" <?= $task_style ?>>
                        <h4><?= htmlspecialchars($tarea['titulo']) ?></h4>
                        <p><?= htmlspecialchars($tarea['descripcion']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>



</div>

<script>
    const fasesData = <?= json_encode($fases, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>;
    console.log('Full fasesData:', fasesData); // Add this line for debugging

    document.addEventListener('DOMContentLoaded', () => {
        const ganttBars = document.querySelectorAll('.gantt-bar');

        ganttBars.forEach(bar => {
            bar.addEventListener('click', (event) => {
                console.log('Clicked element:', event.target); // Add this line
                const phaseIndex = parseInt(event.target.dataset.phaseIndex, 10); // Convert to integer
                const phase = fasesData[phaseIndex];

                console.log('Clicked phaseIndex:', phaseIndex);
                console.log('Retrieved phase:', phase);
                console.log('Phase tareas:', phase ? phase.tareas : 'N/A');

                if (!phase || !phase.tareas || phase.tareas.length === 0) {
                    alert('No hay tareas para esta fase. (Debug: Ver consola para más detalles)');
                    return;
                }

                // Create popover
                const popoverOverlay = document.createElement('div');
                popoverOverlay.classList.add('popover-overlay');

                const popover = document.createElement('div');
                popover.classList.add('popover');

                popover.innerHTML = `
                    <div class="popover-header">
                        <h4>Tareas de ${phase.titulo}</h4>
                        <button class="popover-close-btn">&times;</button>
                    </div>
                    <div class="popover-content">
                        <ul>
                            ${phase.tareas.map(tarea => `
                                <li>
                                    <strong>${tarea.titulo}</strong>
                                    <p>${tarea.descripcion || 'Sin descripción.'}</p>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `;

                popoverOverlay.appendChild(popover);
                document.body.appendChild(popoverOverlay);

                // Close popover logic
                const closePopover = () => {
                    popoverOverlay.remove();
                };

                popover.querySelector('.popover-close-btn').addEventListener('click', closePopover);
                popoverOverlay.addEventListener('click', (e) => {
                    if (e.target === popoverOverlay) {
                        closePopover();
                    }
                });
            });
        });
    });
</script>

</body>
</html>