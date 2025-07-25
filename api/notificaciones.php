<?php
/*  GET /api/notificaciones.php
 *  Requiere token en header Authorization: Bearer <token>
 *  Muestra notificaciones de respuestas: Responsables asignados a PQRs de Clientes (para Clientes),
 *  Clientes a PQRs (para Responsables).
 *
 *  Respuesta:
 *      {
 *        ok:true,
 *        notificaciones: [ { pqr_id:1, mensaje:"...", usuario:"...", fecha_respuesta:"...", url_adjunto:"..." }, ... ]
 *      }
 */

 require_once __DIR__.'/../config/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

$db = DB::getDB();

// --- Lógica de Autenticación --- //
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
list($tokenType, $token) = explode(' ', $authHeader, 2);

$authenticated_user = null;
$is_responsable = false;

if ($tokenType === 'Bearer' && $token) {
    // Buscar en tabla 'usuario'
    $sql_user = 'SELECT id, nombres, rol_id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($authenticated_user) {
        $is_responsable = false; // Usuarios regulares no son responsables
    } else {
        // Buscar en tabla 'responsable'
        $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
        $stmt_resp = $db->prepare($sql_resp);
        $stmt_resp->execute([':token' => $token]);
        $authenticated_user = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($authenticated_user) {
            $is_responsable = true; // Es un responsable
        }
    }
}

if (!$authenticated_user) {
    http_response_code(401); // No autorizado si no se autentica
    exit(json_encode(['ok' => false, 'mensaje' => 'No autenticado o token inválido']));
}

// --- Fin Lógica de Autenticación --- //


try {
    $sql = "SELECT
                rp.pqr_id,
                rp.mensaje,
                COALESCE(u.nombres , resp.nombre) AS usuario,
                rp.fecha_respuesta,
                rp.url_adjunto
            FROM respuesta_pqr rp
            LEFT JOIN usuario u ON rp.usuario_id = u.id
            LEFT JOIN responsable resp ON rp.responsable_id = resp.id";

    $conditions = [];
    $params = [];

    if ($is_responsable) {
        // Responsable: Mostrar respuestas de clientes (donde usuario_id IS NOT NULL)
        // y donde el PQR esté asignado a este responsable.
        $sql .= ' JOIN pqr p ON rp.pqr_id = p.id'; // Unir con tabla pqr
        $conditions[] = 'rp.usuario_id IS NOT NULL'; // Respuesta de un cliente
        $conditions[] = 'p.responsable_id = :responsable_id'; // PQR asignado a este responsable
        $params[':responsable_id'] = $authenticated_user['id'];

    } else {
        // Cliente: Mostrar respuestas de responsables asignados a sus PQRs.
        // Y EXCLUIR explícitamente respuestas donde usuario_id es su propio ID.
        $sql .= ' JOIN pqr p ON rp.pqr_id = p.id'; // Unir con tabla pqr
        $conditions[] = 'rp.responsable_id IS NOT NULL'; // Respuesta de un responsable
        $conditions[] = 'p.id_usuario = :user_id'; // PQR del cliente autenticado
        $conditions[] = 'rp.responsable_id = p.responsable_id'; // El responsable que responde es el responsable asignado al PQR
        // Excluir explícitamente las respuestas donde el usuario_id de la respuesta es el propio ID del cliente autenticado
        $conditions[] = 'rp.usuario_id IS NULL OR rp.usuario_id != :user_id_auth'; // <-- Ajuste en nombre de parámetro para claridad

        $params[':user_id'] = $authenticated_user['id']; // ID del cliente para filtrar PQRs
        $params[':user_id_auth'] = $authenticated_user['id']; // ID del cliente autenticado para excluir respuestas propias
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY rp.fecha_respuesta DESC LIMIT 20"; // Limita a las últimas 20 notificaciones

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'notificaciones' => $notificaciones]);

} catch (Throwable $e) {
    error_log('notificaciones.php: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error interno']);
}
?>