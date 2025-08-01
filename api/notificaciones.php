<?php
/*  GET /api/notificaciones.php
 *  Requiere token en header Authorization: Bearer <token>
 *  Muestra notificaciones de respuestas: Responsables asignados a CTGs/PQRs de Clientes (para Clientes),
 *  Clientes a CTGs/PQRs (para Responsables).
 *
 *  Respuesta:
 *      {
 *        ok:true,
 *        notificaciones: [
 *           { solicitud_id:1, tipo_solicitud:"CTG", mensaje:"...", usuario:"...", fecha_respuesta:"...", url_adjunto:"...", manzana: "...", villa: "..." },
 *           { solicitud_id:10, tipo_solicitud:"PQR", mensaje:"...", usuario:"...", fecha_respuesta:"...", url_adjunto:"...", manzana: "...", villa: "..." },
 *           ...
 *        ]
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
    $conditions_ctg = []; // Condiciones específicas para la parte CTG
    $conditions_pqr = []; // Condiciones específicas para la parte PQR
    $params = []; // Parámetros para bindeo

    if ($is_responsable) {
        // Responsable: Mostrar respuestas de clientes para CTGs/PQRs asignados a él.
        $conditions_ctg[] = 'rp.usuario_id IS NOT NULL'; // Respuesta de un cliente (para CTG)
        $conditions_ctg[] = 'p.responsable_id = :responsable_id'; // CTG asignado (para CTG)

        // Necesitamos condiciones equivalentes para PQR
        $conditions_pqr[] = 'rpq.usuario_id IS NOT NULL'; // Respuesta de un cliente (para PQR)
        $conditions_pqr[] = 'pq.responsable_id = :responsable_id_pqr'; // PQR asignado (for PQR)

        $params[':responsable_id'] = $authenticated_user['id'];
        $params[':responsable_id_pqr'] = $authenticated_user['id'];


    } else {
        // Cliente: SOLO mostrar respuestas de responsables para sus CTGs/PQRs
        // NO mostrar sus propias respuestas ni las de otros clientes

        // Para CTG:
        $conditions_ctg[] = 'rp.responsable_id IS NOT NULL'; // Solo respuestas de responsables (para CTG)
        $conditions_ctg[] = 'rp.responsable_id = p.responsable_id'; // Del responsable asignado al CTG (para CTG)
        $conditions_ctg[] = 'p.id_usuario = :user_id'; // CTG del cliente autenticado (para CTG)

        // Para PQR:
        $conditions_pqr[] = 'rpq.responsable_id IS NOT NULL'; // Solo respuestas de responsables (for PQR)
        $conditions_pqr[] = 'rpq.responsable_id = pq.responsable_id'; // Del responsable asignado al PQR (for PQR)
        $conditions_pqr[] = 'pq.id_usuario = :user_id_pqr'; // PQR del cliente autenticado (for PQR)

        $params[':user_id'] = $authenticated_user['id']; // ID del cliente para filtrar CTGs
        $params[':user_id_pqr'] = $authenticated_user['id']; // ID del cliente para filtrar PQRs
    }


    // Construir la consulta para notificaciones de CTG con sus condiciones
    $sql_ctg = "SELECT
                rp.ctg_id AS solicitud_id,
                'CTG' AS tipo_solicitud,
                rp.mensaje,
                COALESCE(u.nombres , resp.nombre) AS usuario,
                rp.fecha_respuesta,
                rp.url_adjunto,
                pr.manzana,
                pr.villa
            FROM respuesta_ctg rp
            LEFT JOIN usuario u ON rp.usuario_id = u.id
            LEFT JOIN responsable resp ON rp.responsable_id = resp.id
            JOIN ctg p ON rp.ctg_id = p.id
            JOIN propiedad pr ON p.id_propiedad = pr.id";

    if (!empty($conditions_ctg)) {
        $sql_ctg .= ' WHERE ' . implode(' AND ', $conditions_ctg);
    }

    // Construir la consulta para notificaciones de PQR con sus condiciones
    $sql_pqr = "SELECT
                rpq.pqr_id AS solicitud_id,
                'PQR' AS tipo_solicitud,
                rpq.mensaje,
                COALESCE(us.nombres , respn.nombre) AS usuario,
                rpq.fecha_respuesta,
                rpq.url_adjunto,
                prp.manzana,
                prp.villa
            FROM respuesta_pqr rpq
            LEFT JOIN usuario us ON rpq.usuario_id = us.id
            LEFT JOIN responsable respn ON rpq.responsable_id = respn.id
            JOIN pqr pq ON rpq.pqr_id = pq.id
            JOIN propiedad prp ON pq.id_propiedad = prp.id";

    if (!empty($conditions_pqr)) {
        $sql_pqr .= ' WHERE ' . implode(' AND ', $conditions_pqr);
    }


    // Combinar ambas consultas con UNION ALL y aplicar ORDER BY y LIMIT al resultado combinado
    $sql = "(" . $sql_ctg . ") UNION ALL (" . $sql_pqr . ") ORDER BY fecha_respuesta DESC LIMIT 20";


    $stmt = $db->prepare($sql);

    // Ejecutar con los parámetros
    $stmt->execute($params);

    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'notificaciones' => $notificaciones]);

} catch (Throwable $e) {
    error_log('notificaciones.php: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error interno']);
}