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

// Intentar aumentar los límites para subidas grandes.
@ini_set('upload_max_filesize', '1024M');
@ini_set('post_max_size', '1024M');
@ini_set('memory_limit', '1280M');
@ini_set('max_execution_time', '3600');
@ini_set('max_input_time', '3600');

require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// --- Lógica de Autenticación --- //
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
list($tokenType, $token) = explode(' ', $authHeader, 2);

$authenticated_user = null;
$is_responsable = false;

if ($tokenType === 'Bearer' && $token) {
    $db = DB::getDB();
    $sql_user = 'SELECT id, nombres, rol_id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($authenticated_user) {
        $is_responsable = false;
    } else {
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
    http_response_code(401);
    exit(json_encode(['ok' => false, 'mensaje' => 'No autenticado o token inválido']));
}

// --- Fin Lógica de Autenticación --- //

$db = DB::getDB(); 

try{
    /* ---------- 1. validar ---------- */
    $ctgId     = (int)($_POST['ctg_id']     ?? 0);
    $mensaje   = trim($_POST['mensaje']   ?? '');

    if(!$ctgId || $mensaje === ''){
        http_response_code(400);
        exit(json_encode(['ok'=>false,'msg'=>'Datos incompletos (ctg_id o mensaje faltante)']));
    }

    /* ---------- 2. gestionar adjunto ---------- */
    $urlAdjunto = null;
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__.'/../../ImagenesCTG_respuestas/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (is_writable($uploadDir)) {
            // Lógica para usar nombre original y evitar colisiones
            $fileInfo = pathinfo(basename($_FILES['archivo']['name']));
            $extension = isset($fileInfo['extension']) ? '.' . strtolower($fileInfo['extension']) : '';
            $filename = preg_replace("/[^a-zA-Z0-9_-]/", "", $fileInfo['filename']);
            
            $name = $filename . $extension;
            $dest = $uploadDir . $name;
            
            // Evitar sobrescritura si el archivo ya existe
            $counter = 1;
            while (file_exists($dest)) {
                $name = $filename . '(' . $counter . ')' . $extension;
                $dest = $uploadDir . $name;
                $counter++;
            }

            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $dest)) {
                $urlAdjunto = "https://app.costasol.com.ec/ImagenesCTG_respuestas/" . rawurlencode($name);
            } else {
                error_log('Error al mover archivo subido para CTG respuesta.');
            }
        } else {
            error_log('Directorio de subida no es escribible: ' . $uploadDir);
        }
    } elseif (isset($_FILES['archivo']) && $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errorCode = $_FILES['archivo']['error'];
        $errorMessage = 'Error desconocido al subir el archivo.';
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage = 'El archivo excede el tamaño máximo permitido por el servidor.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage = 'El archivo se subió solo parcialmente.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMessage = 'Falta la carpeta temporal del servidor.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMessage = 'No se pudo escribir el archivo en el disco.';
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMessage = 'Una extensión de PHP detuvo la subida del archivo.';
                break;
        }
        http_response_code(400);
        exit(json_encode(['ok' => false, 'msg' => $errorMessage]));
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