<?php
/**
 * POST /api/cita/cita_cancelar.php
 * ▸ Cancela una cita específica.
 *   Parámetros:
 *     id_cita: ID de la cita a cancelar.
 *     id_usuario: ID del usuario que intenta cancelar la cita (para validación de seguridad).
 *   Respuesta:
 *     { ok: true } o { ok: false, message: "..." }
 */
require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$idCita = (int)($_POST['id_cita'] ?? 0);
$idUsuario = (int)($_POST['id_usuario'] ?? 0);

$is_admin_responsible = isset($_POST['is_admin_responsible']) && $_POST['is_admin_responsible'] === 'true';

if (!$idCita || (!$idUsuario && !$is_admin_responsible)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Parámetros incompletos.']);
    exit();
}

try {
    $db = DB::getDB();

    $sqlCheck = "SELECT id FROM agendamiento_visitas WHERE id = :id_cita AND estado = 'PROGRAMADO'";
    $paramsCheck = [':id_cita' => $idCita];

    if (!$is_admin_responsible) {
        $sqlCheck .= " AND id_usuario = :id_usuario";
        $paramsCheck[':id_usuario'] = $idUsuario;
    }

    $stCheck = $db->prepare($sqlCheck);
    $stCheck->execute($paramsCheck);

    if ($stCheck->rowCount() === 0) {
        http_response_code(403); // Forbidden
        echo json_encode(['ok' => false, 'message' => 'La cita no existe, no le pertenece o no puede ser cancelada en su estado actual.']);
        exit();
    }

    // Actualizar el estado de la cita a 'CANCELADO'
    $sqlUpdate = "UPDATE agendamiento_visitas SET estado = 'CANCELADO' WHERE id = :id_cita";
    $stUpdate = $db->prepare($sqlUpdate);
    $stUpdate->execute([':id_cita' => $idCita]);

    if ($stUpdate->rowCount() > 0) {
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo cancelar la cita.']);
    }

} catch (Throwable $e) {
    error_log('cita_cancelar: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Error interno del servidor.']);
}
?>