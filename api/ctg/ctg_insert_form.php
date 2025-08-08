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

    // --- INICIO: Lógica para enviar Notificación Push al Cliente ---
    if ($is_responsable && $remitente_responsable_id) {
        // 1. Obtener el ID del cliente asociado a este CTG
        $sql_get_user_id = 'SELECT id_usuario FROM ctg WHERE id = :ctg_id LIMIT 1';
        $stmt_get_user_id = $db->prepare($sql_get_user_id);
        $stmt_get_user_id->execute([':ctg_id' => $ctgId]);
        $cliente_id = $stmt_get_user_id->fetchColumn();

        if ($cliente_id) {
            // 2. Obtener el onesignal_player_id y nombre del cliente
            $sql_get_player_id = 'SELECT onesignal_player_id, nombres FROM usuario WHERE id = :user_id LIMIT 1';
            $stmt_get_player_id = $db->prepare($sql_get_player_id);
            $stmt_get_player_id->execute([':user_id' => $cliente_id]);
            $cliente_info = $stmt_get_player_id->fetch(PDO::FETCH_ASSOC);

            $oneSignalPlayerId = $cliente_info['onesignal_player_id'] ?? null;
            $cliente_nombre = $cliente_info['nombres'] ?? 'Cliente';


            if ($oneSignalPlayerId) {
                // 3. Obtener información adicional del CTG para la notificación
                $sql_get_ctg_info = 'SELECT numero_solicitud, pr.manzana, pr.villa
                                     FROM ctg p
                                     JOIN propiedad pr ON p.id_propiedad = pr.id
                                     WHERE p.id = :ctg_id LIMIT 1';
                $stmt_get_ctg_info = $db->prepare($sql_get_ctg_info);
                $stmt_get_ctg_info->execute([':ctg_id' => $ctgId]);
                $ctg_info = $stmt_get_ctg_info->fetch(PDO::FETCH_ASSOC);

                $ctg_numero = $ctg_info['numero_solicitud'] ?? 'N/A';
                $manzana = $ctg_info['manzana'] ?? 'N/A';
                $villa = $ctg_info['villa'] ?? 'N/A';

                // Construir el mensaje de la notificación
                $message_title = "Nueva respuesta a tu mensaje";
                $notification_message = strlen($mensaje) > 100 ? substr($mensaje, 0, 97) . '...' : $mensaje;
                $message_body = "Mz {$manzana} Villa {$villa}.";


                // --- Código para enviar a la API de OneSignal ---
                $oneSignalAppId = 'e77613c2-51f8-431d-9892-8b2463ecc817'; // Tu App ID
                $oneSignalApiKey = 'os_v2_app_453bhqsr7bbr3gesrmsgh3gic66q3hsf24becvfqkh44mrzwgvmwtm3k4p47sydyynham5mmlkc4qyigv27jxoage7n3omod5plhxmi'; // Tu REST API Key

                $fields = [
                    'app_id' => $oneSignalAppId,
                    'include_player_ids' => [$oneSignalPlayerId],
                    'headings' => ['en' => $message_title, 'es' => $message_title],
                    'contents' => ['en' => $message_body, 'es' => $message_body],
                    'data' => ['ctg_id' => $ctgId],
                    'ttl' => 5,       // Notificación expira después de 24 horas si no se entrega (86400 segundos)
                    'expire_in' => 5  // Notificación se borra 24 horas después de ser leída (86400 segundos)

                ];

                $fields = json_encode($fields);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Authorization: Basic ' . $oneSignalApiKey
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Considerar TRUE en producción

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $responseData = json_decode($response, true);

                if ($httpCode === 200 && isset($responseData['id'])) {
                     error_log("Notificación OneSignal enviada correctamente al Player ID " . $oneSignalPlayerId . " (CTG ID: " . $ctgId . "). OneSignal ID: " . $responseData['id']);
                } else {
                     error_log("Error al enviar notificación OneSignal al Player ID " . $oneSignalPlayerId . " (CTG ID: " . $ctgId . "). HTTP Code: " . $httpCode . ". Response: " . $response);
                }

                // --- Fin Código para enviar a la API de OneSignal ---

            } else {
                 error_log("Cliente con ID " . $cliente_id . " para CTG " . $ctgId . " no tiene onesignal_player_id registrado.");
            }
        } else {
            error_log("CTG con ID " . $ctgId . " no tiene un id_usuario asociado para enviar notificación.");
        }
    }
    // --- FIN: Lógica para enviar Notificación Push al Cliente ---


    // --- INICIO: Lógica para enviar Notificación Push al Responsable ---
    if (!$is_responsable && $remitente_usuario_id) { // Verificar si el remitente es un usuario regular
        // 1. Obtener el ID del responsable asociado a este CTG
        $sql_get_responsable_id = 'SELECT responsable_id FROM ctg WHERE id = :ctg_id LIMIT 1';
        $stmt_get_responsable_id = $db->prepare($sql_get_responsable_id);
        $stmt_get_responsable_id->execute([':ctg_id' => $ctgId]);
        $responsable_id_ctg = $stmt_get_responsable_id->fetchColumn();

        if ($responsable_id_ctg) {
            // 2. Obtener el onesignal_player_id y nombre del responsable
            $sql_get_resp_player_id = 'SELECT onesignal_player_id, nombre FROM responsable WHERE id = :responsable_id LIMIT 1';
            $stmt_get_resp_player_id = $db->prepare($sql_get_resp_player_id);
            $stmt_get_resp_player_id->execute([':responsable_id' => $responsable_id_ctg]);
            $responsable_info = $stmt_get_resp_player_id->fetch(PDO::FETCH_ASSOC);

            $oneSignalRespPlayerId = $responsable_info['onesignal_player_id'] ?? null;
            $responsable_nombre_notif = $responsable_info['nombre'] ?? 'Responsable';


            if ($oneSignalRespPlayerId) {
                // 3. Obtener información adicional del CTG y del cliente para la notificación
                $sql_get_ctg_user_info = 'SELECT p.numero_solicitud, pr.manzana, pr.villa, u.nombres, u.apellidos
                                          FROM ctg p
                                          JOIN propiedad pr ON p.id_propiedad = pr.id
                                          JOIN usuario u ON p.id_usuario = u.id
                                          WHERE p.id = :ctg_id LIMIT 1';
                $stmt_get_ctg_user_info = $db->prepare($sql_get_ctg_user_info);
                $stmt_get_ctg_user_info->execute([':ctg_id' => $ctgId]);
                $ctg_user_info = $stmt_get_ctg_user_info->fetch(PDO::FETCH_ASSOC);

                $ctg_numero = $ctg_user_info['numero_solicitud'] ?? 'N/A';
                $manzana = $ctg_user_info['manzana'] ?? 'N/A';
                $villa = $ctg_user_info['villa'] ?? 'N/A';
                $cliente_nombre_completo = trim(($ctg_user_info['nombres'] ?? '') . ' ' . ($ctg_user_info['apellidos'] ?? 'Cliente'));

                // Construir el mensaje de la notificación para el responsable
                $message_title_resp = "Nueva respuesta de {$cliente_nombre_completo}";
                 $notification_message_resp = strlen($mensaje) > 100 ? substr($mensaje, 0, 97) . '...' : $mensaje;
                $message_body_resp = "Mz {$manzana} Villa {$villa}.";


                // --- Código para enviar a la API de OneSignal ---
                $oneSignalAppId = 'e77613c2-51f8-431d-9892-8b2463ecc817'; // Tu App ID
                $oneSignalApiKey = 'os_v2_app_453bhqsr7bbr3gesrmsgh3gic66q3hsf24becvfqkh44mrzwgvmwtm3k4p47sydyynham5mmlkc4qyigv27jxoage7n3omod5plhxmi'; // Tu REST API Key

                $fields_resp = [
                    'app_id' => $oneSignalAppId,
                    'include_player_ids' => [$oneSignalRespPlayerId],
                    'headings' => ['en' => $message_title_resp, 'es' => $message_title_resp],
                    'contents' => ['en' => $message_body_resp, 'es' => $message_body_resp],
                    'data' => ['ctg_id' => $ctgId], // Puedes incluir datos adicionales para manejar en el frontend del responsable
                    'ttl' => 86400,       // Notificación expira después de 24 horas si no se entrega (86400 segundos)
                    'expire_in' => 86400  // Notificación se borra 24 horas después de ser leída (86400 segundos)
                    
                ];

                $fields_resp = json_encode($fields_resp);

                $ch_resp = curl_init();
                curl_setopt($ch_resp, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
                curl_setopt($ch_resp, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Authorization: Basic ' . $oneSignalApiKey
                ));
                curl_setopt($ch_resp, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch_resp, CURLOPT_HEADER, FALSE);
                curl_setopt($ch_resp, CURLOPT_POST, TRUE);
                curl_setopt($ch_resp, CURLOPT_POSTFIELDS, $fields_resp);
                curl_setopt($ch_resp, CURLOPT_SSL_VERIFYPEER, FALSE); // Considerar TRUE en producción

                $response_resp = curl_exec($ch_resp);
                $httpCode_resp = curl_getinfo($ch_resp, CURLINFO_HTTP_CODE);
                curl_close($ch_resp);

                $responseData_resp = json_decode($response_resp, true);

                 if ($httpCode_resp === 200 && isset($responseData_resp['id'])) {
                     error_log("Notificación OneSignal enviada correctamente al Responsable Player ID " . $oneSignalRespPlayerId . " (CTG ID: " . $ctgId . "). OneSignal ID: " . $responseData_resp['id']);
                } else {
                     error_log("Error al enviar notificación OneSignal al Responsable Player ID " . $oneSignalRespPlayerId . " (CTG ID: " . $ctgId . "). HTTP Code: " . $httpCode_resp . ". Response: " . $response_resp);
                }
                // --- Fin Código para enviar a la API de OneSignal ---

            } else {
                 error_log("Responsable con ID " . $responsable_id_ctg . " para CTG " . $ctgId . " no tiene onesignal_player_id registrado.");
            }
        } else {
            error_log("CTG con ID " . $ctgId . " no tiene un responsable_id asociado para enviar notificación.");
        }
    }
    // --- FIN: Lógica para enviar Notificación Push al Responsable ---


    echo json_encode(['ok'=>true]);

}catch(Throwable $e){
    error_log('ctg_insert_form: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
?>