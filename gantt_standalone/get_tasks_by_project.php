<?php
// get_tasks_by_project.php - Obtiene las tareas de un proyecto específico

require_once './config/db.php'; // Incluye la configuración de la base de datos

header('Content-Type: application/json');

$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

if ($projectId === 0) {
    echo json_encode([]); // No hay proyecto seleccionado
    exit;
}

$tasks = [];

// Obtener tareas para el proyecto seleccionado
$stmt_tasks = $conn->prepare("SELECT id, text FROM gantt_tasks WHERE project_id = ? ORDER BY sortorder ASC");
$stmt_tasks->bind_param("i", $projectId);
$stmt_tasks->execute();
$result_tasks = $stmt_tasks->get_result();

if ($result_tasks) {
    while ($row = $result_tasks->fetch_assoc()) {
        $tasks[] = $row;
    }
} else {
    error_log("Error al obtener tareas por proyecto: " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener tareas']);
}
$stmt_tasks->close();

echo json_encode($tasks);

$conn->close();
?>