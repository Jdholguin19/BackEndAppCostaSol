<?php
/**
 *  GET /api/ctg_respuestas.php?ctg_id=12
 *  Requiere token en header Authorization: Bearer <token>
 *  → { ok:true, respuestas:[
 *        { id, mensaje, url_adjunto, fecha_respuesta,
 *          usuario_id, responsable_id, nombre, url_foto }
 *     ] }
 */
require_once __DIR__.'/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// --- Lógica de Autenticación --- //
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
list($tokenType, $token) = explode(' ', $authHeader, 2);

$authenticated_user = null;

if ($tokenType === 'Bearer' && $token) {
    $db = DB::getDB(); // Usar la conexión para autenticar
    // Buscar en tabla 'usuario'
    $sql_user = 'SELECT id, nombres, rol_id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$authenticated_user) {
        // Buscar en tabla 'responsable'
        $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
        $stmt_resp = $db->prepare($sql_resp);
        $stmt_resp->execute([':token' => $token]);
        $authenticated_user = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        // No necesitamos $is_responsable aquí si solo validamos que está logueado.
        // Si necesitaras lógica diferente para responsables al ver respuestas, obtendrías $is_responsable aquí.
    }
}

if (!$authenticated_user) {
    http_response_code(401); // No autorizado
    exit(json_encode(['ok' => false, 'mensaje' => 'No autenticado o token inválido']));
}

// --- Fin Lógica de Autenticación --- //

$ctgId = (int)($_GET['ctg_id'] ?? 0);
if(!$ctgId){
    http_response_code(400);
    exit(json_encode(['ok'=>false,'msg'=>'ctg_id requerido']));
}

try{
    $db = DB::getDB(); // Reutilizar la conexión de la autenticación
     $sql = 'SELECT  r.id,
                r.mensaje,
                r.url_adjunto,
                r.fecha_respuesta,
                r.usuario_id,
                r.responsable_id,
                COALESCE(u.nombres , resp.nombre)          AS nombre,
                COALESCE(u.url_foto_perfil , resp.url_foto_perfil) AS url_foto
        FROM    respuesta_ctg r
        LEFT JOIN usuario     u    ON u.id    = r.usuario_id
        LEFT JOIN responsable resp ON resp.id = r.responsable_id
        WHERE   r.ctg_id = :pid
        ORDER BY r.fecha_respuesta ASC';

    $st  = $db->prepare($sql);
    $st->execute([':pid'=>$ctgId]);
    echo json_encode(['ok'=>true,'respuestas'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
}catch(Throwable $e){
    error_log('ctg_respuestas: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
