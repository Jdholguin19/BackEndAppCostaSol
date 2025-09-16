<?php
// save_cross_project_link.php - Guarda una dependencia entre proyectos

require_once '../config/db.php'; // Incluye la configuración de la base de datos

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $source_task_id = isset($data['source_task_id']) ? (int)$data['source_task_id'] : 0;
    $source_project_id = isset($data['source_project_id']) ? (int)$data['source_project_id'] : 0;
    $target_task_id = isset($data['target_task_id']) ? (int)$data['target_task_id'] : 0;
    $target_project_id = isset($data['target_project_id']) ? (int)$data['target_project_id'] : 0;
    $type = isset($data['type']) ? htmlspecialchars($data['type']) : '';

    if ($source_task_id === 0 || $source_project_id === 0 || $target_task_id === 0 || $target_project_id === 0 || empty($type)) {
        $response['message'] = 'Faltan parámetros obligatorios.';
        http_response_code(400);
    } else {
        // Verificar si la dependencia ya existe
        $stmt_check = $conn->prepare("SELECT id FROM gantt_cross_project_links WHERE source_task_id = ? AND target_task_id = ? AND type = ?");
        $stmt_check->bind_param("iis", $source_task_id, $target_task_id, $type);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $response['message'] = 'Esta dependencia ya existe.';
            http_response_code(409); // Conflict
        } else {
            $stmt = $conn->prepare("INSERT INTO gantt_cross_project_links (source_task_id, source_project_id, target_task_id, target_project_id, type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiis", $source_task_id, $source_project_id, $target_task_id, $target_project_id, $type);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Dependencia guardada exitosamente.';
                $response['id'] = $stmt->insert_id;
                http_response_code(201); // Created
            } else {
                $response['message'] = 'Error al guardar dependencia: ' . $stmt->error;
                error_log("Error al guardar dependencia entre proyectos: " . $stmt->error);
                http_response_code(500);
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
    http_response_code(405); // Method Not Allowed
}

echo json_encode($response);

$conn->close();
?>