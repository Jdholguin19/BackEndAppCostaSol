<?php
/**
 *  GET /api/pqr/pqr_observaciones.php?pqr_id=12
 *  Requiere token en header Authorization: Bearer <token>
 *  Solo el responsable asignado puede ver las observaciones
 *  → { ok:true, observaciones: "texto de las observaciones" }
 */
require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// --- Lógica de Autenticación --- //
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
list($tokenType, $token) = explode(' ', $authHeader, 2);

$authenticated_user = null;
$is_responsable = false;

if ($tokenType === 'Bearer' && $token) {
    $db = DB::getDB(); // Usar la conexión para autenticar
    // Buscar en tabla 'usuario'
    $sql_user = 'SELECT id, nombres, rol_id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($authenticated_user) {
        $is_responsable = false;
    } else {
        // Buscar en tabla 'responsable'
        $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
        $stmt_resp = $db->prepare($sql_resp);
        $stmt_resp->execute([':token' => $token]);
        $authenticated_user = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($authenticated_user) {
            $is_responsable = true;
        }
    }
}

if (!$authenticated_user) {
    http_response_code(401); // No autorizado
    exit(json_encode(['ok' => false, 'mensaje' => 'No autenticado o token inválido']));
}

// --- Fin Lógica de Autenticación --- //

$pqrId = (int)($_GET['pqr_id'] ?? 0);
if(!$pqrId){
    http_response_code(400);
    exit(json_encode(['ok'=>false,'mensaje'=>'pqr_id requerido']));
}

try{
    $db = DB::getDB(); // Reutilizar la conexión de la autenticación
    
    // Verificar que el usuario es responsable y está asignado a este PQR
    if (!$is_responsable) {
        http_response_code(403); // Forbidden
        exit(json_encode(['ok' => false, 'mensaje' => 'Solo los responsables pueden ver las observaciones']));
    }
    
    // Verificar que el responsable está asignado a este PQR
    $sql_check = 'SELECT id, responsable_id, observaciones FROM pqr WHERE id = :pqr_id AND responsable_id = :responsable_id LIMIT 1';
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->execute([
        ':pqr_id' => $pqrId,
        ':responsable_id' => $authenticated_user['id']
    ]);
    
    $pqr = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$pqr) {
        http_response_code(403); // Forbidden
        exit(json_encode(['ok' => false, 'mensaje' => 'No tienes permisos para ver las observaciones de este PQR']));
    }
    
    echo json_encode([
        'ok' => true, 
        'observaciones' => $pqr['observaciones'] ?? ''
    ]);
    
}catch(Throwable $e){
    error_log('pqr_observaciones: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'mensaje'=>'Error interno']);
}
