<?php
// get_cross_project_links.php - Obtiene las dependencias entre proyectos para una tarea

require_once '../config/db.php'; // Incluye la configuración de la base de datos

header('Content-Type: application/json');

$taskId = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;
$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0; // Current project ID

if ($taskId === 0 || $projectId === 0) {
    echo json_encode([]);
    exit;
}

$links = [];

// Query to get links where the current task is either source or target
// We need to join with gantt_tasks and gantt_projects to get names
$stmt = $conn->prepare("
    SELECT
        gcl.id,
        gcl.source_task_id,
        gcl.source_project_id,
        gcl.target_task_id,
        gcl.target_project_id,
        gcl.type,
        st.text AS source_task_name,
        sp.name AS source_project_name,
        tt.text AS target_task_name,
        tp.name AS target_project_name
    FROM
        gantt_cross_project_links gcl
    JOIN
        gantt_tasks st ON gcl.source_task_id = st.id
    JOIN
        gantt_projects sp ON gcl.source_project_id = sp.id
    JOIN
        gantt_tasks tt ON gcl.target_task_id = tt.id
    JOIN
        gantt_projects tp ON gcl.target_project_id = tp.id
    WHERE
        (gcl.source_task_id = ? AND gcl.source_project_id = ?) OR
        (gcl.target_task_id = ? AND gcl.target_project_id = ?)
");
$stmt->bind_param("iiii", $taskId, $projectId, $taskId, $projectId);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $links[] = $row;
    }
} else {
    error_log("Error al obtener dependencias entre proyectos: " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener dependencias']);
}
$stmt->close();

echo json_encode($links);

$conn->close();
?>