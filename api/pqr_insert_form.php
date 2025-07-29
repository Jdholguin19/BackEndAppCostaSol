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
    // Ya no obtenemos usuario_id del POST; lo obtenemos del usuario autenticado
    // $usuarioId = (int)($_POST['usuario_id'] ?? 0);
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
            // Corregido move_uploaded_uploaded_file a move_uploaded_file
            if(move_uploaded_file($_FILES['archivo']['tmp_name'],$dest)){
                // Asegúrate de que esta URL es correcta para acceso público
                $urlAdjunto = "https://app.costasol.com.ec/ImagenesPQR_respuestas/$name";
            } else {
                // Loggear error de subida de archivo
                 error_log('Error al mover archivo subido para PQR respuesta.');
                 // Puedes decidir si esto es un error fatal o solo un problema con el adjunto.
                 // Por ahora, permitiremos que la respuesta se inserte sin adjunto si falla la subida.
            }
         } else {
             error_log('Directorio de subida no es escribible: '.$uploadDir);
             // Manejar el error: quizás devolver un error al usuario o simplemente no guardar el adjunto.
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
        ':usuario_id'=> $remitente_usuario_id, // ID del usuario si es usuario regular
        ':responsable_id'=> $remitente_responsable_id, // ID del responsable si es responsable
        ':mensaje'=> $mensaje,
        ':url_adjunto'=> $urlAdjunto
    ]);

    echo json_encode(['ok'=>true]);

}catch(Throwable $e){
    error_log('pqr_insert_form: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
?>