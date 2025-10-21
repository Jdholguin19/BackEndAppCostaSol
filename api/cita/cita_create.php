<?php
declare(strict_types=1);

require_once __DIR__.'/../../config/db.php';
require_once __DIR__ . '/../helpers/audit_helper.php'; // Incluir el helper de auditoría
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

/* responsable disponible según propósito */
// Si el propósito es ID 4 (Consultas con crédito y cobranzas), asignar al responsable 4
// Para otros propósitos, asignar al responsable 2
if ($proposito == 4) {
    $respId = 4; // Asignar a responsable 4 para Consultas con crédito y cobranzas
} else {
    $respId = 2; // Asignar a responsable 2 para otros propósitos
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

// Obtener el nombre del propósito para el cuerpo de la notificación
$sql_proposito_nombre_push = 'SELECT proposito FROM proposito_agendamiento WHERE id = :proposito_id LIMIT 1';
$stmt_proposito_nombre_push = $db->prepare($sql_proposito_nombre_push);
$stmt_proposito_nombre_push->execute([':proposito_id' => $proposito]);
$nombreProposito = $stmt_proposito_nombre_push->fetchColumn();

// Notificar al responsable
$sql_get_resp_player_id = 'SELECT onesignal_player_id FROM responsable WHERE id = :resp_id LIMIT 1';
$stmt_get_resp_player_id = $db->prepare($sql_get_resp_player_id);
$stmt_get_resp_player_id->execute([':resp_id' => $respId]);
$respOneSignalPlayerId = $stmt_get_resp_player_id->fetchColumn();

if ($respOneSignalPlayerId) {
    $message_title = "Tienes una nueva cita";
    $message_body = "Tipo: " . ($nombreProposito ?: 'No especificado');
    send_one_signal_notification($message_title, $message_body, $respOneSignalPlayerId, ['cita_id' => $lastInsertId]);
}

// Si es Crédito y Finanzas (proposito_id = 4), notificar también al responsable ID 5
if ($proposito == 4) {
    $sql_get_resp5_player_id = 'SELECT onesignal_player_id FROM responsable WHERE id = 5 LIMIT 1';
    $stmt_get_resp5_player_id = $db->prepare($sql_get_resp5_player_id);
    $stmt_get_resp5_player_id->execute();
    $resp5OneSignalPlayerId = $stmt_get_resp5_player_id->fetchColumn();

    if ($resp5OneSignalPlayerId) {
        $message_title = "Tienes una nueva cita";
        $message_body = "Tipo: " . ($nombreProposito ?: 'No especificado');
        send_one_signal_notification($message_title, $message_body, $resp5OneSignalPlayerId, ['cita_id' => $lastInsertId]);
    }
}

// Notificar al cliente
$sql_get_client_player_id = 'SELECT onesignal_player_id FROM usuario WHERE id = :user_id LIMIT 1';
$stmt_get_client_player_id = $db->prepare($sql_get_client_player_id);
$stmt_get_client_player_id->execute([':user_id' => $uid]);
$clientOneSignalPlayerId = $stmt_get_client_player_id->fetchColumn();

if ($clientOneSignalPlayerId) {
    $message_title = "Se ha programado una nueva cita para ti";
    $message_body = "Tipo: " . ($nombreProposito ?: 'No especificado');
    send_one_signal_notification($message_title, $message_body, $clientOneSignalPlayerId, ['cita_id' => $lastInsertId]);
}
// --- FIN: Lógica para enviar Notificación Push ---

// --- INICIO: Lógica de envío de correo a responsable y cliente para citas ---
require_once __DIR__ . '/../../correos/EnviarCorreoNotificacionResponsable.php';
require_once __DIR__ . '/../../correos/EnviarCorreoClienteCita.php';

// Obtener correo del responsable
$sql_resp_email = 'SELECT correo FROM responsable WHERE id = :resp_id LIMIT 1';
$stmt_resp_email = $db->prepare($sql_resp_email);
$stmt_resp_email->execute([':resp_id' => $respId]);
$correoResponsable = $stmt_resp_email->fetchColumn();

// Obtener correo y nombre del cliente
$sql_cliente_data = 'SELECT nombres, apellidos, correo FROM usuario WHERE id = :user_id LIMIT 1';
$stmt_cliente_data = $db->prepare($sql_cliente_data);
$stmt_cliente_data->execute([':user_id' => $uid]);
$cliente_data = $stmt_cliente_data->fetch(PDO::FETCH_ASSOC);
$nombreCliente = trim($cliente_data['nombres'] . ' ' . $cliente_data['apellidos']);
$correoCliente = $cliente_data['correo'];

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

// Enviar correo al responsable si se obtuvo el correo
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

// Si es Crédito y Finanzas (proposito_id = 4), enviar también al responsable ID 5
if ($proposito == 4) {
    $sql_resp5_email = 'SELECT correo FROM responsable WHERE id = 5 LIMIT 1';
    $stmt_resp5_email = $db->prepare($sql_resp5_email);
    $stmt_resp5_email->execute();
    $correoResponsable5 = $stmt_resp5_email->fetchColumn();

    if ($correoResponsable5) {
        enviarNotificacionResponsable(
            $correoResponsable5,
            $nombreCliente,
            "Cita", // Tipo de solicitud
            $tipoTicket, // Propósito de la cita
            $nombrePropiedad,
            $fecha,
            $hora
        );
    } else {
        error_log("No se pudo obtener el correo del responsable con ID: 5");
    }
}

// Enviar correo al cliente si se obtuvo el correo
if ($correoCliente) {
    enviarCorreoClienteCita(
        $correoCliente,
        $nombreCliente,
        $tipoTicket,
        $nombrePropiedad,
        $fecha,
        $hora
    );
} else {
    error_log("No se pudo obtener el correo del cliente con ID: " . $uid);
}
// --- FIN: Lógica de envío de correo a responsable y cliente para citas ---


// --- INICIO: Sincronización con Outlook Calendar ---
// Se requiere el helper que maneja la lógica de Outlook.
require_once __DIR__ . '/../helpers/outlook_sync_helper.php';

// Después de que la cita se ha creado localmente, intentamos crearla en Outlook.
if ($lastInsertId) {
    $outlookEventId = crearEventoEnOutlook((int)$lastInsertId);
    if ($outlookEventId) {
        // Si la creación en Outlook fue exitosa, guardamos el ID del evento de Outlook
        // en nuestra cita local para poder actualizarla o eliminarla en el futuro.
        $stmtUpdateOutlookId = $db->prepare(
            "UPDATE agendamiento_visitas SET outlook_event_id = :outlook_id WHERE id = :cita_id"
        );
        $stmtUpdateOutlookId->execute([
            ':outlook_id' => $outlookEventId,
            ':cita_id' => $lastInsertId
        ]);
    }
    // Nota: Si la sincronización falla, la cita local YA está creada.
    // El fallo quedará registrado en la tabla de logs para revisión manual o reintentos.
}
// --- FIN: Sincronización con Outlook Calendar ---

    $db->commit();
    $is_responsable_creating_for_client = ($responsable !== false); // Check if $responsable was found in auth block
    log_audit_action($db, 'CREATE_CITA', $uid, ($is_responsable_creating_for_client ? 'responsable' : 'usuario'), 'agendamiento_visitas', $lastInsertId, ['id_propiedad' => $propiedad, 'proposito_id' => $proposito, 'fecha_reunion' => $fecha, 'hora_reunion' => $hora, 'duracion_minutos' => $duracion_a_guardar, 'responsable_id' => $respId]);
    echo json_encode(['ok'=>true]);
}catch(PDOException $e){
 $db->rollBack();
 if($e->errorInfo[1]==1062)
      echo json_encode(['ok'=>false,'msg'=>'Horario ya reservado']);
 else{ http_response_code(500); echo json_encode(['ok'=>false,'msg'=>'Error interno: ' . $e->getMessage()]); }
}
