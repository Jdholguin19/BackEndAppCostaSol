<?php

require_once __DIR__ . '/EmailHelper.php';

function enviarCorreoClienteCita($correoCliente, $nombreCliente, $tipoTicket, $nombrePropiedad, $fecha, $hora) {
    $tenantId = 'b9618ac6-2648-41ed-bb4f-03bcd94a7493'; // Id. de directorio (inquilino)
    $clientId = 'd8f17735-bbd4-4fc4-a193-fd9b645880be'; // Id. de aplicación (cliente)
    $clientSecret = 'e7X8Q~OtM4X~LZ.i5wHvIIukGHK8Tb4yq3Xkpbat'; // Secreto del cliente (Valor)
    $fromEmail = 'sistemas@thaliavictoria.com.ec'; // Remitente para notificaciones a clientes

    $subject = "Tu cita ha sido programada - CostaSol";
    $body = "
        <p>Estimado " . htmlspecialchars($nombreCliente) . ",</p>
        <p>Tu cita ha sido programada exitosamente.</p>
        <p>Detalles de tu cita:</p>
        <ul>
            <li><strong>Tipo:</strong> " . htmlspecialchars($tipoTicket) . "</li>
            <li><strong>Propiedad:</strong> " . htmlspecialchars($nombrePropiedad) . "</li>
            <li><strong>Fecha:</strong> " . htmlspecialchars($fecha) . "</li>
            <li><strong>Hora:</strong> " . htmlspecialchars($hora) . "</li>
        </ul>
        <p>Por favor, no olvides tu cita en la fecha y hora programadas. Si necesitas cambiar la hora o reprogramar, por favor contáctanos.</p>
        <p>Para más información, ingresa a <a href='https://app.costasol.com.ec'>nuestra aplicación</a>.</p>
        <br>
        <p>Atentamente,</p>
        <p><strong>CostaSol - Tu constructora de confianza</strong></p>
        <img src='https://bot.costasol.com.ec/FirmaThali.png' alt='Firma' width='300' height='100' />
    ";

    $tokenResponse = getAccessToken($tenantId, $clientId, $clientSecret);
    if (isset($tokenResponse['error'])) {
        error_log("Error al obtener el token de acceso para notificación de cita a cliente: " . $tokenResponse['error']);
        return false;
    } else {
        $accessToken = $tokenResponse['access_token'];
        $emailResponse = sendEmail($accessToken, $fromEmail, $correoCliente, $subject, $body);
        if (isset($emailResponse['error'])) {
            error_log("Error al enviar el correo de cita a cliente: " . $emailResponse['error']);
            return false;
        } else {
            error_log("Correo de cita enviado correctamente a " . $correoCliente);
            return true;
        }
    }
}

?>

