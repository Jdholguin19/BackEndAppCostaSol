<?php
require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$uid = (int)($_POST['id_usuario']??0);
$propiedad = (int)($_POST['id_propiedad']??0);
$proposito = (int)($_POST['proposito_id']??0);
$fecha = $_POST['fecha']??'';
$hora  = $_POST['hora']??'';
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : null;

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
       AND v.fecha_reunion=:f AND v.hora_reunion = TIME(:h) AND v.estado<>'CANCELADO'
 WHERE r.id != 3
 GROUP  BY r.id
 ORDER  BY n ASC, RAND() ASC
 LIMIT 1");
$resp->execute([':f'=>$fecha,':h'=>$hora, ':dia_calculated'=>$dia]);
$respId=$resp->fetchColumn();
if(!$respId) throw new Exception('Sin responsable');

/* inserta */
$ins=$db->prepare("
 INSERT INTO agendamiento_visitas
   (id_usuario,responsable_id,proposito_id,id_propiedad,
    fecha_reunion,hora_reunion,estado,observaciones)
 VALUES(:u,:r,:p,:prop,:f,:h,'PROGRAMADO',:obs)");
$ins->execute([
 ':u'=>$uid,':r'=>$respId,':p'=>$proposito,':prop'=>$propiedad,
 ':f'=>$fecha,':h'=>$hora,':obs'=>$observaciones]);

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
