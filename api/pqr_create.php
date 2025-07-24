<?php
/*  POST /api/pqr_create.php
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
require_once __DIR__.'/../config/db.php';
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

if (!$authenticated_user || $is_responsable) {
    // Solo permitir la creación de PQRs a usuarios regulares, no a responsables
    http_response_code(403); // Prohibido
    exit(json_encode(['ok' => false, 'mensaje' => 'No autorizado para crear PQRs']));
}

// Si llegamos aquí, es un usuario regular autenticado.
$authenticated_user_id = $authenticated_user['id'];

// --- Fin Lógica de Autenticación --- //

try{
    /* ---------- 1. validar ---------- */
    // Ya no obtenemos id_usuario del POST; usamos el del usuario autenticado
    // $uid   = (int)($_POST['id_usuario']   ?? 0);
    $pid   = (int)($_POST['id_propiedad'] ?? 0);
    $tipo  = (int)($_POST['tipo_id']      ?? 0);
    $sub   = (int)($_POST['subtipo_id']   ?? 0);
    $desc  = trim($_POST['descripcion']   ?? '');

    // Validar que los campos requeridos (excluyendo id_usuario) estén presentes
    if(!$pid||!$tipo||!$sub||$desc===''){
        http_response_code(400);
        exit(json_encode(['ok'=>false,'msg'>'Datos incompletos (id_propiedad, tipo_id, subtipo_id, o descripcion faltante)']));
    }

    /* ---------- 2. gestionar adjunto ---------- */
    $urlProblema = null;
    if(!empty($_FILES['archivo']['tmp_name'])){
         $uploadDir = __DIR__.'/../ImagenesPQR_problema/';
         if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Crear directorio si no existe
        }
        if (is_writable($uploadDir)) {
            $name = uniqid().'-'.basename($_FILES['archivo']['name']);
            $dest = $uploadDir.$name;
            if(move_uploaded_file($_FILES['archivo']['tmp_name'],$dest)){
                // Asegúrate de que esta URL es correcta para acceso público
                $urlProblema = "https://app.costasol.com.ec/ImagenesPQR_problema/$name";
            } else {
                error_log('Error al mover archivo subido para PQR problema.');
            }
        } else {
             error_log('Directorio de subida de problema no es escribible: '.$uploadDir);
        }
    }

    /* ---------- 3. obtener responsable aleatorio ---------- */
    $respId = $db->query("SELECT id FROM responsable WHERE estado=1 ORDER BY RAND() LIMIT 1")
                 ->fetchColumn();

    /* ---------- 4. crear numero_solicitud ---------- */
    $num = $db->query("SELECT LPAD(IFNULL(MAX(id),0)+1,5,'0') FROM pqr")->fetchColumn();
    $numero = 'SAC'.$num;                               // ej: SAC00007

    /* ---------- 5. insertar ---------- */
    $sql = 'INSERT INTO pqr
            (numero_solicitud,id_usuario,id_propiedad,tipo_id,subtipo_id,estado_id,
             descripcion,urgencia_id,url_problema,responsable_id,fecha_compromiso)
            VALUES
            (:num,:uid,:pid,:tipo,:sub,1,          /* 1 = Ingresado */
             :des,2,:url,:resp,DATE_ADD(NOW(),INTERVAL 5 DAY))';
    $db->prepare($sql)->execute([
        ':num'=>$numero,
        ':uid'=>$authenticated_user_id, // <-- Usamos el ID del usuario autenticado
        ':pid'=>$pid,
        ':tipo'=>$tipo,
        ':sub'=>$sub,
        ':des'=>$desc,
        ':url'=>$urlProblema,
        ':resp'=>$respId
    ]);

    echo json_encode(['ok'=>true,'id'=>$db->lastInsertId(),'numero'=>$numero]);

}catch(Throwable $e){
    error_log('pqr_create: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
