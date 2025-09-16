<?php
// data.php - Carga los datos de tareas y enlaces para DHTMLX Gantt

require_once '../config/db.php'; // Incluye la configuración de la base de datos

header('Content-Type: application/json');

$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

if ($projectId === 0) {
    echo json_encode(['data' => [], 'links' => []]); // No hay proyecto seleccionado
    exit;
}

$tasks = [];
$links = [];

// Obtener tareas para el proyecto seleccionado
$stmt_tasks = $conn->prepare("SELECT id, text, start_date, duration, progress, parent, sortorder, open, owners, color FROM gantt_tasks WHERE project_id = ? ORDER BY sortorder ASC");
$stmt_tasks->bind_param("i", $projectId);
$stmt_tasks->execute();
$result_tasks = $stmt_tasks->get_result();

if ($result_tasks) {
    while ($row = $result_tasks->fetch_assoc()) {
        // Formatear la fecha para DHTMLX Gantt
        $row['start_date'] = date('Y-m-d', strtotime($row['start_date']));
        $tasks[] = $row;
    }
} else {
    error_log("Error al obtener tareas: " . $conn->error);
}
$stmt_tasks->close();

// Obtener enlaces (asumimos que los enlaces son intra-proyecto por ahora)
// Los enlaces se refieren a IDs de tareas, que ya están filtradas por project_id
$task_ids = array_column($tasks, 'id');
if (!empty($task_ids)) {
    $placeholders = implode(',', array_fill(0, count($task_ids), '?'));
    $types = str_repeat('i', count($task_ids));

    $stmt_links = $conn->prepare("SELECT id, source, target, type FROM gantt_links WHERE source IN ($placeholders) AND target IN ($placeholders)");
    if ($stmt_links) { // Añadir esta verificación
        $stmt_links->bind_param($types . $types, ...array_merge($task_ids, $task_ids));
        $stmt_links->execute();
        $result_links = $stmt_links->get_result();

        if ($result_links) {
            while ($row = $result_links->fetch_assoc()) {
                $links[] = $row;
            }
        } else {
            error_log("Error al obtener enlaces: " . $conn->error);
        }
        $stmt_links->close();
    } else {
        error_log("Error al preparar la consulta de enlaces: " . $conn->error);
    }
}


$response = [
    'data' => $tasks,
    'links' => $links
];

echo json_encode($response);

$conn->close();
?>