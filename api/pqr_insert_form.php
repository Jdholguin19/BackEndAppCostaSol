<?php
/*  POST /api/pqr_insert_form.php
 *  Body  (multipart/form-data)
 *      pqr_id         int
 *      mensaje        string
 *      archivo        (file | optional)
 *  Requires token in header Authorization: Bearer <token>
 *
 *  Respuesta:
 *      { ok:true }
 */
require_once __DIR__.'/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// Clave de la API REST de OneSignal (Reemplazar con tu clave real)
const ONESIGNAL_REST_API_KEY = 'os_v2_app_453bhqsr7bbr3gesrmsgh3gic66q3hsf24becvfqkh44mrzwgvmwtm3k4p47sydyynham5mmlkc4qyigv27jxoage7n3omod5plhxmi';
const ONESIGNAL_APP_ID = 'e77613c2-51f8-431d-9892-8b2463ecc817'; // Tu App ID de OneSignal

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
    $pqrId     = (int)($_POST['pqr_id']     ?? 0);
    $mensaje   = trim($_POST['mensaje']   ?? '');

    // Validar que pqr_id y mensaje estén presentes
    if(!$pqrId || $mensaje === ''){
        http_response_code(400);
        exit(json_encode(['ok'=>false,'msg'=>'Datos incompletos (pqr_id o mensaje faltante)']));
    }

    /* ---------- 2. gestionar adjunto ---------- */
    $urlAdjunto = null;
    if(!empty($_FILES['archivo']['tmp_name'])){
        // Verificar si el directorio de destino existe y tiene permisos de escritura
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
                 error_log('Error al mover archivo subido para PQR respuesta.');
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
    $sql = 'INSERT INTO respuesta_pqr
            (pqr_id, usuario_id, responsable_id, mensaje, url_adjunto, fecha_respuesta)
            VALUES
            (:pqr_id, :usuario_id, :responsable_id, :mensaje, :url_adjunto, NOW())';

    $db->prepare($sql)->execute([
        ':pqr_id'=> $pqrId,
        ':usuario_id'=> $remitente_usuario_id,
        ':responsable_id'=> $remitente_responsable_id,
        ':mensaje'=> $mensaje,
        ':url_adjunto'=> $urlAdjunto
    ]);

    // --- Lógica para enviar notificación si la respuesta es de un responsable ---
    if ($is_responsable) {
        // Obtener el ID del cliente asociado a este PQR
        $sql_cliente_pqr = 'SELECT id_usuario FROM pqr WHERE id = :pqr_id LIMIT 1';
        $stmt_cliente_pqr = $db->prepare($sql_cliente_pqr);
        $stmt_cliente_pqr->execute([':pqr_id' => $pqrId]);
        $cliente_id = $stmt_cliente_pqr->fetchColumn();

        if ($cliente_id) {
            // Obtener el OneSignal Player ID del cliente
            $sql_player_id = 'SELECT onesignal_player_id FROM usuario WHERE id = :cliente_id LIMIT 1';
            $stmt_player_id = $db->prepare($sql_player_id);
            $stmt_player_id->execute([':cliente_id' => $cliente_id]);
            $player_id = $stmt_player_id->fetchColumn();

            if ($player_id) {
                // Preparar y enviar la notificación a OneSignal
                $heading = 'Nueva respuesta en tu PQR';
                $content = 'El responsable ha respondido a tu PQR.';
                $url = 'https://app.costasol.com.ec/Front/pqr_detalle.php?id=' . $pqrId; // URL para redirigir

                $fields = [
                    'app_id' => ONESIGNAL_APP_ID,
                    'include_player_ids' => [$player_id],
                    'headings' => ['en' => $heading, 'es' => $heading], // Puedes añadir otros idiomas
                    'contents' => ['en' => $content, 'es' => $content],
                    'url' => $url
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8',
                                                       'Authorization: Basic ' . ONESIGNAL_REST_API_KEY]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

                $response = curl_exec($ch);
                curl_close($ch);

                // Opcional: loggear la respuesta de OneSignal para depuración
                // error_log('OneSignal Response: ' . $response);
            } else {
                // Opcional: loggear que no se encontró el Player ID para el cliente
                 error_log('No se encontró OneSignal Player ID para el cliente: ' . $cliente_id);
            }
        } else {
            // Opcional: loggear que no se encontró el cliente para el PQR
             error_log('No se encontró cliente para el PQR ID: ' . $pqrId);
        }
    }
    // --- Fin Lógica de notificación --- //

    echo json_encode(['ok'=>true]);

}catch(Throwable $e){
    error_log('pqr_insert_form: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
?>