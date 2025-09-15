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
require_once __DIR__ . '/../helpers/audit_helper.php'; // Incluir el helper de auditoría
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
    $db->beginTransaction();

    // 1. Verificar que la cita exista y se pueda cancelar, y obtener datos de Outlook
    $sqlCheck = "SELECT outlook_event_id, responsable_id, id_usuario FROM agendamiento_visitas WHERE id = :id_cita AND estado = 'PROGRAMADO'";
    $stCheck = $db->prepare($sqlCheck);
    $stCheck->execute([':id_cita' => $idCita]);
    $cita = $stCheck->fetch(PDO::FETCH_ASSOC);

    // 2. Validar permisos: la cita debe existir y el usuario debe ser el dueño o un admin.
    if (!$cita || (!$is_admin_responsible && $cita['id_usuario'] != $idUsuario)) {
        $db->rollBack();
        http_response_code(403); // Forbidden
        echo json_encode(['ok' => false, 'message' => 'La cita no existe, no le pertenece o no puede ser cancelada en su estado actual.']);
        exit();
    }

    // 3. Si la cita está vinculada a Outlook, intentar eliminar el evento de allá primero.
    if (!empty($cita['outlook_event_id'])) {
        require_once __DIR__ . '/../helpers/outlook_sync_helper.php';
        eliminarEventoEnOutlook($cita['outlook_event_id'], (int)$cita['responsable_id'], $idCita);
        // No detenemos el flujo si la eliminación en Outlook falla, el error ya se registró en el log.
    }

    // 4. Actualizar el estado de la cita local a 'CANCELADO'
    $sqlUpdate = "UPDATE agendamiento_visitas SET estado = 'CANCELADO' WHERE id = :id_cita";
    $stUpdate = $db->prepare($sqlUpdate);
    $stUpdate->execute([':id_cita' => $idCita]);

    if ($stUpdate->rowCount() > 0) {
        $db->commit();
        $auditor_id = $is_admin_responsible ? $cita['responsable_id'] : $idUsuario;
        $auditor_type = $is_admin_responsible ? 'responsable' : 'usuario';
        log_audit_action($db, 'CANCEL_CITA', $auditor_id, $auditor_type, 'agendamiento_visitas', $idCita, ['reason' => 'Cita cancelada por el usuario/responsable']); // Log de auditoría
        echo json_encode(['ok' => true]);
    } else {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo cancelar la cita.']);
    }

} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('cita_cancelar: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Error interno del servidor.']);
}
?>