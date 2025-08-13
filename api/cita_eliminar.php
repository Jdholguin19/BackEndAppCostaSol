<?php
/**
 * POST /api/cita_eliminar.php
 * ▸ Elimina una cita específica.
 *   Parámetros:
 *     id_cita: ID de la cita a eliminar.
 *     id_usuario: ID del usuario que intenta eliminar la cita (para validación de seguridad).
 *     is_admin_responsible: (Opcional) true si el usuario es el admin responsable.
 *   Respuesta:
 *     { ok: true } o { ok: false, message: "..." }
 */
require_once __DIR__.'/../config/db.php';
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

    // Verificar que la cita existe, está cancelada y pertenece al usuario o es un admin responsable
    $sqlCheck = "SELECT id FROM agendamiento_visitas WHERE id = :id_cita AND estado = 'CANCELADO'";
    $paramsCheck = [':id_cita' => $idCita];

    if (!$is_admin_responsible) {
        $sqlCheck .= " AND id_usuario = :id_usuario";
        $paramsCheck[':id_usuario'] = $idUsuario;
    }

    $stCheck = $db->prepare($sqlCheck);
    $stCheck->execute($paramsCheck);

    if ($stCheck->rowCount() === 0) {
        http_response_code(403); // Forbidden
        echo json_encode(['ok' => false, 'message' => 'La cita no existe, no le pertenece, no está cancelada o no tiene permisos para eliminarla.']);
        exit();
    }

    // Eliminar la cita
    $sqlDelete = "DELETE FROM agendamiento_visitas WHERE id = :id_cita";
    $stDelete = $db->prepare($sqlDelete);
    $stDelete->execute([':id_cita' => $idCita]);

    if ($stDelete->rowCount() > 0) {
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo eliminar la cita.']);
    }

} catch (Throwable $e) {
    error_log('cita_eliminar: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Error interno del servidor.']);
}
?>