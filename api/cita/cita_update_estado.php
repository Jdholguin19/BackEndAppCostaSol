<?php
require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// --- Lógica de Autenticación por Token ---
$auth_id = null;
$is_responsable = false;

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    try {
        $db = DB::getDB();
        // Solo los responsables pueden cambiar el estado, así que solo verificamos esa tabla.
        $stmt_resp = $db->prepare('SELECT id FROM responsable WHERE token = :token');
        $stmt_resp->execute([':token' => $token]);
        $responsable = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($responsable) {
            $auth_id = $responsable['id'];
            $is_responsable = true;
        }
    } catch (Exception $e) {
        error_log('Error de validación de token: ' . $e->getMessage());
    }
}

if (!$is_responsable) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit();
}
// --- Fin Autenticación ---


// Obtener los datos del POST
$data = json_decode(file_get_contents('php://input'), true);
$cita_id = $data['cita_id'] ?? 0;
$new_status = $data['estado'] ?? '';

// Validar el estado
$allowed_statuses = ['PROGRAMADO', 'REALIZADO', 'CANCELADO'];
if (!$cita_id || !in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos.']);
    exit();
}

try {
    $db = DB::getDB();

    // 1. Verificar que el responsable esté asignado a esta cita y obtener el ID de evento de Outlook
    $stmt_verify = $db->prepare('SELECT responsable_id, outlook_event_id FROM agendamiento_visitas WHERE id = :cita_id');
    $stmt_verify->execute([':cita_id' => $cita_id]);
    $cita = $stmt_verify->fetch(PDO::FETCH_ASSOC);

    if (!$cita) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Cita no encontrada.']);
        exit();
    }

    if ($cita['responsable_id'] != $auth_id) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'mensaje' => 'No tiene permiso para modificar esta cita.']);
        exit();
    }

    // 2. Actualizar el estado en la base de datos local
    $stmt_update = $db->prepare('UPDATE agendamiento_visitas SET estado = :estado WHERE id = :cita_id');
    $stmt_update->execute([':estado' => $new_status, ':cita_id' => $cita_id]);

    if ($stmt_update->rowCount() > 0) {
        // 3. Si el nuevo estado es CANCELADO y hay un evento de Outlook, eliminarlo
        if ($new_status === 'CANCELADO' && !empty($cita['outlook_event_id'])) {
            require_once __DIR__ . '/../helpers/outlook_sync_helper.php';
            eliminarEventoEnOutlook($cita['outlook_event_id'], (int)$cita['responsable_id'], $cita_id);
        }

        echo json_encode(['ok' => true, 'mensaje' => 'Estado de la cita actualizado.']);
    } else {
        echo json_encode(['ok' => true, 'mensaje' => 'El estado de la cita no ha cambiado.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error al actualizar el estado de la cita: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor.']);
}
?>