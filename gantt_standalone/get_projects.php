<?php
// get_projects.php - Obtiene la lista de proyectos

require_once './config/db.php';

header('Content-Type: application/json');

$projects = [];
$result = $conn->query("SELECT id, name FROM gantt_projects ORDER BY name ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
} else {
    error_log("Error al obtener proyectos: " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener proyectos']);
    exit;
}

echo json_encode($projects);

$conn->close();
?>