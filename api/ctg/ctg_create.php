<?php
/*  POST /api/ctg_create.php
 *  Body  (multipart/form-data)
 *      id_propiedad     int
 *      tipo_id          int
 *      subtipo_id       int
 *      descripcion      string
 *      archivo          (file | optional)
 *  Requires token in header Authorization: Bearer <token>
 *
 *  Respuesta:
 *      { ok:true, id:123, numero:'SAC00007' }
 */
require_once __DIR__.'/../../config/db.php';
require_once __DIR__ . '/../helpers/audit_helper.php'; // Incluir el helper de auditoría
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
    http_response_code(401);
    exit(json_encode(['ok' => false, 'mensaje' => 'Token inválido']));
}

// Determinar si es responsable o usuario regular
$user_id = null;
$creator_type = $is_responsable ? 'responsable' : 'usuario';
$creator_id = $authenticated_user['id'];

// Si es responsable, el id_usuario debe venir en el POST
if ($is_responsable) {
    $user_id = (int)($_POST['id_usuario'] ?? 0);
    if (!$user_id) {
        http_response_code(400);
        exit(json_encode(['ok' => false, 'mensaje' => 'ID de usuario requerido para responsables']));
    }
} else {
    // Si es usuario regular, usa su propio ID
    $user_id = $authenticated_user['id'];
}

// --- Fin Lógica de Autenticación --- //

try{
    /* ---------- 1. validar ---------- */
    $pid   = (int)($_POST['id_propiedad'] ?? 0);
    $tipo  = (int)($_POST['tipo_id']      ?? 0); // Este es ahora el ID de la contingencia de la tabla tipo_ctg
    $desc  = trim($_POST['descripcion']   ?? '');

    // Validar que los campos requeridos estén presentes (subtipo_id ya no es necesario)
    if(!$pid||!$tipo||$desc===''){
        http_response_code(400);
        exit(json_encode(['ok'=>false,'msg'=>'Datos incompletos (id_propiedad, tipo_id, o descripcion faltante)']));
    }

    /* ---------- 2. obtener urgencia_id (Lógica Simplificada) ---------- */
    // Como los subtipos se han eliminado, la urgencia se establece a 1 (BASICA) por defecto.
    // Esta lógica puede revisarse si la urgencia debe depender de los nuevos tipos de contingencia.
    $urgencia_id = 1; 


    /* ---------- 3. gestionar adjunto ---------- */
    $urlProblema = null;
    if(!empty($_FILES['archivo']['tmp_name'])){
         $uploadDir = __DIR__.'/../../ImagenesCTG_problema/';
         if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Crear directorio si no existe
        }
        if (is_writable($uploadDir)) {
            $name = uniqid().'-'.basename($_FILES['archivo']['name']);
            $dest = $uploadDir.$name;
            if(move_uploaded_file($_FILES['archivo']['tmp_name'],$dest)){
                // Asegúrate de que esta URL es correcta para acceso público
                $urlProblema = "https://app.costasol.com.ec/ImagenesCTG_problema/$name";
            } else {
                error_log('Error al mover archivo subido para CTG problema.');
            }
        } else {
             error_log('Directorio de subida de problema no es escribible: '.$uploadDir);
        }
    }

    /* ---------- 4. obtener responsable aleatorio ---------- */
    $respId = $db->query("SELECT id FROM responsable WHERE estado=1 AND id IN (1, 2) ORDER BY RAND() LIMIT 1")
                 ->fetchColumn();

    /* ---------- 5. crear numero_solicitud ---------- */
    $num = $db->query("SELECT LPAD(IFNULL(MAX(id),0)+1,5,'0') FROM ctg")->fetchColumn();
    $numero = 'SAC'.$num;                               // ej: SAC00007

    /* ---------- 6. insertar ---------- */
    $sql = 'INSERT INTO ctg
            (numero_solicitud,id_usuario,id_propiedad,tipo_id,subtipo_id,estado_id,
             descripcion,urgencia_id,url_problema,responsable_id,fecha_compromiso)
            VALUES
            (:num,:uid,:pid,:tipo,NULL,1,          /* subtipo_id es ahora NULL */
             :des,:urgencia_id,:url,:resp,DATE_ADD(NOW(),INTERVAL 5 DAY))';
    $db->prepare($sql)->execute([
        ':num'=>$numero,
        ':uid'=>$user_id,
        ':pid'=>$pid,
        ':tipo'=>$tipo,
        ':des'=>$desc,
        ':urgencia_id'=>$urgencia_id,
        ':url'=>$urlProblema,
        ':resp'=>$respId
    ]);

    $new_ctg_id = $db->lastInsertId();

    // --- INICIO: Lógica de envío de correo a responsable ---
    require_once __DIR__ . '/../../correos/EnviarCorreoNotificacionResponsable.php';

    // Obtener correo del responsable
    $sql_resp_email = 'SELECT correo FROM responsable WHERE id = :resp_id LIMIT 1';
    $stmt_resp_email = $db->prepare($sql_resp_email);
    $stmt_resp_email->execute([':resp_id' => $respId]);
    $correoResponsable = $stmt_resp_email->fetchColumn();

    // Obtener nombre del cliente
    $sql_cliente_nombre = 'SELECT nombres, apellidos FROM usuario WHERE id = :user_id LIMIT 1';
    $stmt_cliente_nombre = $db->prepare($sql_cliente_nombre);
    $stmt_cliente_nombre->execute([':user_id' => $user_id]);
    $cliente_data = $stmt_cliente_nombre->fetch(PDO::FETCH_ASSOC);
    $nombreCliente = trim($cliente_data['nombres'] . ' ' . $cliente_data['apellidos']);

    // Obtener nombre de la contingencia desde la tabla tipo_ctg
    $sql_tipo_ctg_nombre = 'SELECT nombre FROM tipo_ctg WHERE id = :tipo_id LIMIT 1';
    $stmt_tipo_ctg_nombre = $db->prepare($sql_tipo_ctg_nombre);
    $stmt_tipo_ctg_nombre->execute([':tipo_id' => $tipo]);
    $nombreContingencia = $stmt_tipo_ctg_nombre->fetchColumn();

    // Obtener nombre de la propiedad
    $sql_propiedad_nombre = 'SELECT CONCAT("Manzana ", manzana, ", Villa ", villa) AS nombre_propiedad FROM propiedad WHERE id = :prop_id LIMIT 1';
    $stmt_propiedad_nombre = $db->prepare($sql_propiedad_nombre);
    $stmt_propiedad_nombre->execute([':prop_id' => $pid]);
    $nombrePropiedad = $stmt_propiedad_nombre->fetchColumn();

    // Enviar correo si se obtuvo el correo del responsable
    if ($correoResponsable) {
        enviarNotificacionResponsable(
            $correoResponsable,
            $nombreCliente,
            "CTG", // Tipo de solicitud
            $nombreContingencia, // Usamos el nombre de la nueva contingencia
            $nombrePropiedad
        );
    } else {
        error_log("No se pudo obtener el correo del responsable con ID: " . $respId);
    }
    // --- FIN: Lógica de envío de correo a responsable ---

    // --- INICIO: Lógica de creación en Kiss Flow ---
    try {
        // Obtener datos adicionales del usuario para Kiss Flow (cédula, correo y teléfono)
        $sql_user_details = 'SELECT cedula, correo, telefono FROM usuario WHERE id = :user_id LIMIT 1';
        $stmt_user_details = $db->prepare($sql_user_details);
        $stmt_user_details->execute([':user_id' => $user_id]);
        $user_details = $stmt_user_details->fetch(PDO::FETCH_ASSOC);

        if ($user_details && !empty($user_details['cedula'])) {
            $kissflow_payload = [
                'cedula' => $user_details['cedula'],
                'nombre_cliente' => $nombreCliente,
                'email' => $user_details['correo'] ?? '',
                'telefono' => $user_details['telefono'] ?? '',
                'descripcion_dano' => $desc,
                'contingencia_nombre' => $nombreContingencia // <-- Dato clave para el handler
            ];

            $handler_url = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/kissflow_ctg/ctg_handler.php';
            
            $ch = curl_init($handler_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($kissflow_payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            // Descomentar si se usa SSL en un entorno de desarrollo sin certificado válido
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $handler_response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($handler_response === false) {
                error_log("Error al llamar a ctg_handler.php para el CTG $numero.");
            } else {
                // Registrar la respuesta del handler para depuración
                error_log("Respuesta de ctg_handler.php para CTG $numero (HTTP $http_code): " . $handler_response);
            }
        } else {
            error_log("No se pudo enviar a Kiss Flow para el CTG $numero: falta la cédula del usuario ID " . $user_id);
        }
    } catch (Throwable $ke) {
        error_log("Error durante la llamada a Kiss Flow para el CTG $numero: " . $ke->getMessage());
    }
    // --- FIN: Lógica de creación en Kiss Flow ---

    echo json_encode(['ok'=>true,'id'=>$new_ctg_id,'numero'=>$numero]);
    log_audit_action($db, 'CREATE_CTG', $creator_id, $creator_type, 'ctg', $new_ctg_id, ['numero_solicitud' => $numero, 'id_propiedad' => $pid, 'tipo_id' => $tipo, 'subtipo_id' => null, 'descripcion' => $desc, 'urgencia_id' => $urgencia_id, 'responsable_id' => $respId, 'created_for_user_id' => $user_id]);

}catch(Throwable $e){
    error_log('ctg_create: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno: ' . $e->getMessage()]);
}
