<?php

require_once __DIR__ . '/EmailHelper.php';

function enviarCorreoClienteCTG($correoCliente, $nombreCliente, $tipoContingencia, $nombrePropiedad, $numero_solicitud) {
    $tenantId = 'b9618ac6-2648-41ed-bb4f-03bcd94a7493'; // Id. de directorio (inquilino)
    $clientId = 'd8f17735-bbd4-4fc4-a193-fd9b645880be'; // Id. de aplicación (cliente)
    $clientSecret = 'e7X8Q~OtM4X~LZ.i5wHvIIukGHK8Tb4yq3Xkpbat'; // Secreto del cliente (Valor)
    $fromEmail = 'sistemas@thaliavictoria.com.ec'; // Remitente para notificaciones a clientes

    $subject = "Tu contingencia ha sido reportada - CostaSol";
    $body = "
        <p>Estimado " . htmlspecialchars($nombreCliente) . ",</p>
        <p>Tu reporte de contingencia ha sido recibido exitosamente por nuestro equipo.</p>
        <p>Detalles de tu reporte:</p>
        <ul>
            <li><strong>Número de Reporte:</strong> " . htmlspecialchars($numero_solicitud) . "</li>
            <li><strong>Tipo de Contingencia:</strong> " . htmlspecialchars($tipoContingencia) . "</li>
            <li><strong>Propiedad:</strong> " . htmlspecialchars($nombrePropiedad) . "</li>
        </ul>
        <p>Nuestro equipo revisará tu contingencia y se pondrá en contacto contigo pronto. Puedes hacer seguimiento a tu reporte en <a href='https://app.costasol.com.ec'>nuestra aplicación</a>.</p>
        <br>
        <p>Atentamente,</p>
        <p><strong>CostaSol - Tu constructora de confianza</strong></p>
        <img src='https://bot.costasol.com.ec/FirmaThali.png' alt='Firma' width='300' height='100' />
    ";

    $tokenResponse = getAccessToken($tenantId, $clientId, $clientSecret);
    if (isset($tokenResponse['error'])) {
        error_log("Error al obtener el token de acceso para notificación de CTG a cliente: " . $tokenResponse['error']);
        return false;
    } else {
        $accessToken = $tokenResponse['access_token'];
        $emailResponse = sendEmail($accessToken, $fromEmail, $correoCliente, $subject, $body);
        if (isset($emailResponse['error'])) {
            error_log("Error al enviar el correo de CTG a cliente: " . $emailResponse['error']);
            return false;
        } else {
            error_log("Correo de CTG enviado correctamente a " . $correoCliente);
            return true;
        }
    }
}

function enviarCorreoClientePQR($correoCliente, $nombreCliente, $tipoPQR, $nombrePropiedad, $numero_solicitud) {
    $tenantId = 'b9618ac6-2648-41ed-bb4f-03bcd94a7493'; // Id. de directorio (inquilino)
    $clientId = 'd8f17735-bbd4-4fc4-a193-fd9b645880be'; // Id. de aplicación (cliente)
    $clientSecret = 'e7X8Q~OtM4X~LZ.i5wHvIIukGHK8Tb4yq3Xkpbat'; // Secreto del cliente (Valor)
    $fromEmail = 'sistemas@thaliavictoria.com.ec'; // Remitente para notificaciones a clientes

    $subject = "Tu PQR ha sido recibida - CostaSol";
    $body = "
        <p>Estimado " . htmlspecialchars($nombreCliente) . ",</p>
        <p>Tu PQR (Petición, Queja o Reclamo) ha sido recibida exitosamente por nuestro equipo.</p>
        <p>Detalles de tu PQR:</p>
        <ul>
            <li><strong>Número de Reporte:</strong> " . htmlspecialchars($numero_solicitud) . "</li>
            <li><strong>Tipo:</strong> " . htmlspecialchars($tipoPQR) . "</li>
            <li><strong>Propiedad:</strong> " . htmlspecialchars($nombrePropiedad) . "</li>
        </ul>
        <p>Nuestro equipo revisará tu solicitud y se pondrá en contacto contigo en los próximos días. Puedes hacer seguimiento en <a href='https://app.costasol.com.ec'>nuestra aplicación</a>.</p>
        <br>
        <p>Atentamente,</p>
        <p><strong>CostaSol - Tu constructora de confianza</strong></p>
        <img src='https://bot.costasol.com.ec/FirmaThali.png' alt='Firma' width='300' height='100' />
    ";

    $tokenResponse = getAccessToken($tenantId, $clientId, $clientSecret);
    if (isset($tokenResponse['error'])) {
        error_log("Error al obtener el token de acceso para notificación de PQR a cliente: " . $tokenResponse['error']);
        return false;
    } else {
        $accessToken = $tokenResponse['access_token'];
        $emailResponse = sendEmail($accessToken, $fromEmail, $correoCliente, $subject, $body);
        if (isset($emailResponse['error'])) {
            error_log("Error al enviar el correo de PQR a cliente: " . $emailResponse['error']);
            return false;
        } else {
            error_log("Correo de PQR enviado correctamente a " . $correoCliente);
            return true;
        }
    }
}

?>
