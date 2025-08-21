<?php
/*  POST /api/ctg_insert_form.php
 *  Body  (multipart/form-data)
 *      ctg_id         int
 *      mensaje        string
 *      archivo        (file | optional)
 *  Requires token in header Authorization: Bearer <token>
 *
 *  Respuesta:
 *      { ok:true }
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

// Reutilizar la conexión de la autenticación
$db = DB::getDB(); 

try{
    /* ---------- 1. validar ---------- */
    $ctgId     = (int)($_POST['ctg_id']     ?? 0);
    $mensaje   = trim($_POST['mensaje']   ?? '');

    // Validar que ctg_id y mensaje estén presentes
    if(!$ctgId || $mensaje === ''){
        http_response_code(400);
        exit(json_encode(['ok'=>false,'msg'=>'Datos incompletos (ctg_id o mensaje faltante)']));
    }

    /* ---------- 2. gestionar adjunto ---------- */
    $urlAdjunto = null;
    if(!empty($_FILES['archivo']['tmp_name'])){
        $uploadDir = __DIR__.'/../ImagenesPQR_respuestas/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Crear directorio si no existe
        }
         if (is_writable($uploadDir)) {
            $name = uniqid().'-'.basename($_FILES['archivo']['name']);
            $dest = $uploadDir.$name;
            if(move_uploaded_file($_FILES['archivo']['tmp_name'],$dest)){
                $urlAdjunto = "https://app.costasol.com.ec/ImagenesPQR_respuestas/$name";
            } else {
                 error_log('Error al mover archivo subido para CTG respuesta.');
            }
         } else {
             error_log('Directorio de subida no es escribible: '.$uploadDir);
         }

    }

    /* ---------- 3. Determinar ID del remitente y si es responsable ---------- */
    $remitente_usuario_id = null;
    $remitente_responsable_id = null;

    if ($is_responsable) {
        $remitente_responsable_id = $authenticated_user['id'];
    } else {
        $remitente_usuario_id = $authenticated_user['id'];
    }

    /* ---------- 4. insertar respuesta ---------- */
    $sql = 'INSERT INTO respuesta_ctg
            (ctg_id, usuario_id, responsable_id, mensaje, url_adjunto, fecha_respuesta)
            VALUES
            (:ctg_id, :usuario_id, :responsable_id, :mensaje, :url_adjunto, NOW())';

    $db->prepare($sql)->execute([
        ':ctg_id'=> $ctgId,
        ':usuario_id'=> $remitente_usuario_id,
        ':responsable_id'=> $remitente_responsable_id,
        ':mensaje'=> $mensaje,
        ':url_adjunto'=> $urlAdjunto
    ]);

    require_once __DIR__ . '/../helpers/notificaciones.php';

    // --- INICIO: Lógica para enviar Notificación Push ---
    if ($is_responsable) { // Si el que responde es un responsable, notificar al cliente
        $sql_get_user_id = 'SELECT id_usuario FROM ctg WHERE id = :ctg_id LIMIT 1';
        $stmt_get_user_id = $db->prepare($sql_get_user_id);
        $stmt_get_user_id->execute([':ctg_id' => $ctgId]);
        $cliente_id = $stmt_get_user_id->fetchColumn();

        if ($cliente_id) {
            $sql_get_player_id = 'SELECT onesignal_player_id FROM usuario WHERE id = :user_id LIMIT 1';
            $stmt_get_player_id = $db->prepare($sql_get_player_id);
            $stmt_get_player_id->execute([':user_id' => $cliente_id]);
            $oneSignalPlayerId = $stmt_get_player_id->fetchColumn();

            if ($oneSignalPlayerId) {
                $message_title = "Nueva respuesta a tu mensaje";
                $message_body = strlen($mensaje) > 100 ? substr($mensaje, 0, 97) . '...' : $mensaje;
                send_one_signal_notification($message_title, $message_body, $oneSignalPlayerId, ['ctg_id' => $ctgId]);
            }
        }
    } else { // Si el que responde es un cliente, notificar al responsable
        $sql_get_resp_id = 'SELECT responsable_id FROM ctg WHERE id = :ctg_id LIMIT 1';
        $stmt_get_resp_id = $db->prepare($sql_get_resp_id);
        $stmt_get_resp_id->execute([':ctg_id' => $ctgId]);
        $responsable_id = $stmt_get_resp_id->fetchColumn();

        if ($responsable_id) {
            $sql_get_player_id = 'SELECT onesignal_player_id FROM responsable WHERE id = :resp_id LIMIT 1';
            $stmt_get_player_id = $db->prepare($sql_get_player_id);
            $stmt_get_player_id->execute([':resp_id' => $responsable_id]);
            $oneSignalPlayerId = $stmt_get_player_id->fetchColumn();

            if ($oneSignalPlayerId) {
                $cliente_nombre = $authenticated_user['nombres'] ?? 'Cliente';
                $message_title = "Nueva respuesta de {$cliente_nombre}";
                $message_body = strlen($mensaje) > 100 ? substr($mensaje, 0, 97) . '...' : $mensaje;
                send_one_signal_notification($message_title, $message_body, $oneSignalPlayerId, ['ctg_id' => $ctgId]);
            }
        }
    }
    // --- FIN: Lógica para enviar Notificación Push ---


    echo json_encode(['ok'=>true]);

}catch(Throwable $e){
    error_log('ctg_insert_form: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
?>