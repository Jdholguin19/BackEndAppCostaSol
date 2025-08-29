<?php
declare(strict_types=1);

require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// --- Validación de Token y Rol ---
$uid = 0;
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["ok" => false, "mensaje" => "Token no proporcionado"]);
    exit;
}

list(, $token) = explode(' ', $authHeader);

try {
    $conn = DB::getDB();

    // Intentar encontrar el token en la tabla de responsables primero
    $stmt_resp = $conn->prepare("SELECT id FROM responsable WHERE token = :token LIMIT 1");
    $stmt_resp->execute([':token' => $token]);
    $responsable = $stmt_resp->fetch(PDO::FETCH_ASSOC);

    if ($responsable) {
        // Si es un responsable, se confía en el id_usuario que viene del POST
        $uid = (int)($_POST['id_usuario'] ?? 0);
    } else {
        // Si no es un responsable, buscar en la tabla de usuarios
        $stmt_user = $conn->prepare("SELECT id FROM usuario WHERE token = :token LIMIT 1");
        $stmt_user->execute([':token' => $token]);
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Si es un usuario normal, se usa su propio ID para seguridad
            $uid = (int)$user['id'];
        } else {
            // Si el token no pertenece a nadie, no está autorizado
            http_response_code(401);
            echo json_encode(["ok" => false, "mensaje" => "Token inválido"]);
            exit;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "mensaje" => "Error de autenticación: " . $e->getMessage()]);
    exit;
}
// --- Fin Validación ---

$propiedad = (int)($_POST['id_propiedad']??0);
$proposito = (int)($_POST['proposito_id']??0);
$fecha = $_POST['fecha']??'';
$hora  = $_POST['hora']??'';
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : null;
$duracion_especial = (int)($_POST['duracion'] ?? 0); // Nueva duración opcional


if(!$uid||!$propiedad||!$proposito||
   !preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha)||
   !preg_match('/^\d{2}:\d{2}$/',$hora))
{ http_response_code(400); exit(json_encode(['ok'=>false])); }

try{
$db=DB::getDB(); $db->beginTransaction();

$dia = (int)date('w', strtotime($fecha));   // 0 (domingo) - 6 (sábado)
if ($dia == 0) { // Sunday
    $dia = 7; // Map Sunday to 7, assuming no availability for Sunday in DB
}

// --- INICIO: Determinar duración ANTES de seleccionar responsable ---
$duracion_a_guardar = $duracion_especial;
if ($duracion_a_guardar <= 0) {
    // Si no vino una duración especial, usar un valor por defecto para la verificación
    $duracion_a_guardar = 60; // Valor por defecto para verificación de solapamiento
}

/* responsable disponible con menos carga ese día */
$resp=$db->prepare("
 SELECT r.id,COUNT(v.id) n
 FROM   responsable r
 JOIN   responsable_disponibilidad d ON d.responsable_id=r.id
       AND d.activo=1 AND d.dia_semana= :dia_calculated
       AND :f BETWEEN d.fecha_vigencia_desde
                 AND IFNULL(d.fecha_vigencia_hasta,'2999-12-31')
       AND TIME(:h) BETWEEN d.hora_inicio AND d.hora_fin
 LEFT   JOIN agendamiento_visitas v ON v.responsable_id=r.id
       AND v.fecha_reunion=:f 
       AND v.estado<>'CANCELADO'
       AND (
           -- Verificar si la nueva cita se solapa con citas existentes
           (TIME(:h) >= v.hora_reunion AND TIME(:h) < ADDTIME(v.hora_reunion, SEC_TO_TIME(COALESCE(v.duracion_minutos, 60) * 60)))
           OR
           (v.hora_reunion >= TIME(:h) AND v.hora_reunion < ADDTIME(TIME(:h), SEC_TO_TIME(:duracion * 60)))
       )
 WHERE r.id != 3
 GROUP  BY r.id
 ORDER  BY n ASC, RAND() ASC
 LIMIT 1");
$resp->execute([':f'=>$fecha,':h'=>$hora, ':dia_calculated'=>$dia, ':duracion'=>$duracion_a_guardar]);
$respId=$resp->fetchColumn();
if(!$respId) throw new Exception('Sin responsable');

// --- FINALIZAR: Determinar duración final para guardar ---
if ($duracion_especial <= 0) {
    // Si no vino una duración especial, buscar la por defecto del responsable seleccionado
    $stmt_intervalo = $db->prepare("
        SELECT intervalo_minutos FROM responsable_disponibilidad 
        WHERE responsable_id = :resp_id AND dia_semana = :dia 
        AND :f BETWEEN fecha_vigencia_desde AND IFNULL(fecha_vigencia_hasta, '2999-12-31')
        LIMIT 1
    ");
    $stmt_intervalo->execute([':resp_id' => $respId, ':dia' => $dia, ':f' => $fecha]);
    $duracion_a_guardar = $stmt_intervalo->fetchColumn();
    if (!$duracion_a_guardar) {
        $duracion_a_guardar = 30; // Fallback al default de la tabla si algo falla
    }
}

/* inserta */
$ins=$db->prepare("
 INSERT INTO agendamiento_visitas
   (id_usuario,responsable_id,proposito_id,id_propiedad,
    fecha_reunion,hora_reunion,estado,observaciones, duracion_minutos)
 VALUES(:u,:r,:p,:prop,:f,:h,'PROGRAMADO',:obs, :duracion)");
$ins->execute([
 ':u'=>$uid,':r'=>$respId,':p'=>$proposito,':prop'=>$propiedad,
 ':f'=>$fecha,':h'=>$hora,':obs'=>$observaciones, ':duracion'=>$duracion_a_guardar]);

// --- INICIO: Lógica para enviar Notificación Push ---
require_once __DIR__ . '/../helpers/notificaciones.php';

// Obtener el ID de la cita recién creada
$lastInsertId = $db->lastInsertId();

// Obtener el Player ID del responsable
$sql_get_player_id = 'SELECT onesignal_player_id FROM responsable WHERE id = :resp_id LIMIT 1';
$stmt_get_player_id = $db->prepare($sql_get_player_id);
$stmt_get_player_id->execute([':resp_id' => $respId]);
$oneSignalPlayerId = $stmt_get_player_id->fetchColumn();

// Obtener el nombre del propósito para el cuerpo de la notificación
$sql_proposito_nombre_push = 'SELECT proposito FROM proposito_agendamiento WHERE id = :proposito_id LIMIT 1';
$stmt_proposito_nombre_push = $db->prepare($sql_proposito_nombre_push);
$stmt_proposito_nombre_push->execute([':proposito_id' => $proposito]);
$nombreProposito = $stmt_proposito_nombre_push->fetchColumn();

if ($oneSignalPlayerId) {
    $message_title = "Tienes una nueva cita";
    $message_body = "Tipo: " . ($nombreProposito ?: 'No especificado');
    send_one_signal_notification($message_title, $message_body, $oneSignalPlayerId, ['cita_id' => $lastInsertId]);
}
// --- FIN: Lógica para enviar Notificación Push ---

// --- INICIO: Lógica de envío de correo a responsable para citas ---
require_once __DIR__ . '/../../correos/EnviarCorreoNotificacionResponsable.php';

// Obtener correo del responsable
$sql_resp_email = 'SELECT correo FROM responsable WHERE id = :resp_id LIMIT 1';
$stmt_resp_email = $db->prepare($sql_resp_email);
$stmt_resp_email->execute([':resp_id' => $respId]);
$correoResponsable = $stmt_resp_email->fetchColumn();

// Obtener nombre del cliente
$sql_cliente_nombre = 'SELECT nombres, apellidos FROM usuario WHERE id = :user_id LIMIT 1';
$stmt_cliente_nombre = $db->prepare($sql_cliente_nombre);
$stmt_cliente_nombre->execute([':user_id' => $uid]);
$cliente_data = $stmt_cliente_nombre->fetch(PDO::FETCH_ASSOC);
$nombreCliente = trim($cliente_data['nombres'] . ' ' . $cliente_data['apellidos']);

// Obtener nombre del propósito de la cita
$sql_proposito_nombre = 'SELECT proposito FROM proposito_agendamiento WHERE id = :proposito_id LIMIT 1';
$stmt_proposito_nombre = $db->prepare($sql_proposito_nombre);
$stmt_proposito_nombre->execute([':proposito_id' => $proposito]);
$tipoTicket = $stmt_proposito_nombre->fetchColumn(); // Usamos tipoTicket para el propósito

// Obtener nombre de la propiedad (Manzana, Villa)
$sql_propiedad_nombre = 'SELECT CONCAT("Manzana ", manzana, ", Villa ", villa) AS nombre_propiedad FROM propiedad WHERE id = :prop_id LIMIT 1';
$stmt_propiedad_nombre = $db->prepare($sql_propiedad_nombre);
$stmt_propiedad_nombre->execute([':prop_id' => $propiedad]);
$nombrePropiedad = $stmt_propiedad_nombre->fetchColumn();

// Enviar correo si se obtuvo el correo del responsable
if ($correoResponsable) {
    enviarNotificacionResponsable(
        $correoResponsable,
        $nombreCliente,
        "Cita", // Tipo de solicitud
        $tipoTicket, // Propósito de la cita
        $nombrePropiedad,
        $fecha,
        $hora
    );
} else {
    error_log("No se pudo obtener el correo del responsable con ID: " . $respId);
}
// --- FIN: Lógica de envío de correo a responsable para citas ---

$db->commit();
echo json_encode(['ok'=>true]);
}catch(PDOException $e){
 $db->rollBack();
 if($e->errorInfo[1]==1062)
      echo json_encode(['ok'=>false,'msg'=>'Horario ya reservado']);
 else{ http_response_code(500); echo json_encode(['ok'=>false,'msg'=>'Error interno: ' . $e->getMessage()]); }
}
