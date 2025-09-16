<?php
// create_project.php - Crea un nuevo proyecto

require_once '../config/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $projectName = isset($data['name']) ? trim($data['name']) : '';

    if (empty($projectName)) {
        $response['message'] = 'El nombre del proyecto no puede estar vacío.';
        http_response_code(400);
    } else {
        // Verificar si el proyecto ya existe
        $stmt_check = $conn->prepare("SELECT id FROM gantt_projects WHERE name = ?");
        $stmt_check->bind_param("s", $projectName);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $response['message'] = 'Ya existe un proyecto con ese nombre.';
            http_response_code(409); // Conflict
        } else {
            $stmt = $conn->prepare("INSERT INTO gantt_projects (name) VALUES (?)");
            $stmt->bind_param("s", $projectName);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Proyecto creado exitosamente.';
                $response['id'] = $stmt->insert_id;
                $response['name'] = $projectName;
                http_response_code(201); // Created
            } else {
                $response['message'] = 'Error al crear el proyecto: ' . $stmt->error;
                error_log("Error al crear proyecto: " . $stmt->error);
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