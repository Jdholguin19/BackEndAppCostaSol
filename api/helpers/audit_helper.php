<?php
// api/helpers/audit_helper.php

/**
 * Registra un evento en el log de auditoría.
 *
 * @param PDO $conn La conexión a la base de datos.
 * @param string $action La acción realizada.
 * @param int|null $user_id El ID del usuario/responsable.
 * @param string $user_type El tipo de usuario ('usuario', 'responsable', 'sistema').
 * @param string|null $target_resource El recurso afectado.
 * @param int|null $target_id El ID del recurso afectado.
 * @param array|null $details Detalles adicionales en un array asociativo.
 */
function log_audit_action($conn, $action, $user_id, $user_type, $target_resource = null, $target_id = null, $details = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $details_json = $details ? json_encode($details) : null;

    $sql = "INSERT INTO audit_log (user_id, user_type, ip_address, action, target_resource, target_id, details)
            VALUES (:user_id, :user_type, :ip_address, :action, :target_resource, :target_id, :details)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':user_type' => $user_type,
        ':ip_address' => $ip_address,
        ':action' => $action,
        ':target_resource' => $target_resource,
        ':target_id' => $target_id,
        ':details' => $details_json
    ]);
}
?>