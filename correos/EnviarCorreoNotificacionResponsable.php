<?php

require_once __DIR__ . '/EmailHelper.php';

function enviarNotificacionResponsable($correoResponsable, $nombreCliente, $tipoSolicitud, $tipoTicket, $nombrePropiedad, $fecha = null, $hora = null) {
    $tenantId = 'b9618ac6-2648-41ed-bb4f-03bcd94a7493'; // Id. de directorio (inquilino)
    $clientId = 'd8f17735-bbd4-4fc4-a193-fd9b645880be'; // Id. de aplicación (cliente)
    $clientSecret = 'e7X8Q~OtM4X~LZ.i5wHvIIukGHK8Tb4yq3Xkpbat'; // Secreto del cliente (Valor)
    $fromEmail = 'sistemas@thaliavictoria.com.ec'; // Remitente para notificaciones a responsables

    $subject = "Nuevo " . $tipoSolicitud . " de " . $nombreCliente;
    $body = "
        <p>Estimado Responsable,</p>
        <p>Se ha creado un nuevo <strong>" . $tipoSolicitud . "</strong> de <strong>" . $nombreCliente . "</strong>.</p>
        <p>Detalles:</p>
        <ul>
            <li><strong>Tipo:</strong> " . $tipoTicket . "</li>
            <li><strong>Propiedad:</strong> " . $nombrePropiedad . "</li>";

    if ($fecha && $hora) {
        $body .= "
            <li><strong>Fecha:</strong> " . $fecha . "</li>
            <li><strong>Hora:</strong> " . $hora . "</li>";
    }

    $body .= "
        </ul>
        <p>Por favor, revise la aplicación para más detalles o precione <a href='https://app.costasol.com.ec'>aquí</a>.</p>
        <br>
        <p>Este correo es generado automáticamente. No es necesario responder.</p>
        <p>Atentamente, CostaSol</p>
        <img src='https://bot.costasol.com.ec/FirmaThali.png' alt='Firma' width='300' height='100' />
    ";

    $tokenResponse = getAccessToken($tenantId, $clientId, $clientSecret);
    if (isset($tokenResponse['error'])) {
        error_log("Error al obtener el token de acceso para notificación a responsable: " . $tokenResponse['error']);
        return false;
    } else {
        $accessToken = $tokenResponse['access_token'];
        $emailResponse = sendEmail($accessToken, $fromEmail, $correoResponsable, $subject, $body);
        if (isset($emailResponse['error'])) {
            error_log("Error al enviar el correo electrónico de notificación a responsable: " . $emailResponse['error']);
            return false;
        } else {
            error_log("Correo electrónico de notificación a responsable enviado correctamente a " . $correoResponsable);
            return true;
        }
    }
}

function enviarNotificacionAcabados($correoResponsable, $datosSeleccion) {
    $tenantId = 'b9618ac6-2648-41ed-bb4f-03bcd94a7493';
    $clientId = 'd8f17735-bbd4-4fc4-a193-fd9b645880be';
    $clientSecret = 'e7X8Q~OtM4X~LZ.i5wHvIIukGHK8Tb4yq3Xkpbat';
    $fromEmail = 'sistemas@thaliavictoria.com.ec';

    $subject = "Confirmación de Selección de Acabados para " . htmlspecialchars($datosSeleccion['nombreCliente']);
    
    $total = $datosSeleccion['kit']['costo'];

    $body = "
        <p>Estimado Responsable,</p>
        <p>El cliente <strong>" . htmlspecialchars($datosSeleccion['nombreCliente']) . "</strong> ha finalizado su selección de acabados para la propiedad <strong>" . htmlspecialchars($datosSeleccion['nombrePropiedad']) . "</strong>.</p>
        <p>A continuación el resumen de la selección:</p>
        <table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
            <tr style='background-color: #f2f2f2;'>
                <th>Concepto</th>
                <th>Detalle</th>
                <th>Costo</th>
            </tr>
            <tr>
                <td>Kit Principal</td>
                <td>" . htmlspecialchars($datosSeleccion['kit']['nombre']) . "</td>
                <td>" . ($datosSeleccion['kit']['costo'] > 0 ? '$' . number_format($datosSeleccion['kit']['costo'], 2) : 'Incluido') . "</td>
            </tr>
            <tr>
                <td>Color</td>
                <td>" . htmlspecialchars($datosSeleccion['color']['nombre']) . "</td>
                <td>Incluido</td>
            </tr>";

    if (!empty($datosSeleccion['paquetes'])) {
        $body .= "<tr style='background-color: #f2f2f2;'><td colspan='3' style='text-align: center;'><strong>Paquetes Adicionales</strong></td></tr>";
        foreach ($datosSeleccion['paquetes'] as $paquete) {
            $costoPaquete = (float)$paquete['precio'];
            $total += $costoPaquete;
            $body .= "
                <tr>
                    <td colspan='2'>" . htmlspecialchars($paquete['nombre']) . "</td>
                    <td>$" . number_format($costoPaquete, 2) . "</td>
                </tr>";
        }
    }

    $body .= "
            <tr style='background-color: #f2f2f2;'>
                <td colspan='2' style='text-align: right;'><strong>TOTAL</strong></td>
                <td><strong>$" . number_format($total, 2) . "</strong></td>
            </tr>
        </table>
        <p>Por favor, revise la aplicación para más detalles o presione <a href='https://app.costasol.com.ec'>aquí</a>.</p>
        <br>
        <p>Este correo es generado automáticamente. No es necesario responder.</p>
        <p>Atentamente, CostaSol</p>
        <img src='https://bot.costasol.com.ec/FirmaThali.png' alt='Firma' width='300' height='100' />
    ";

    $tokenResponse = getAccessToken($tenantId, $clientId, $clientSecret);
    if (isset($tokenResponse['error'])) {
        error_log("Error al obtener el token de acceso para notificación de acabados: " . $tokenResponse['error']);
        return false;
    } else {
        $accessToken = $tokenResponse['access_token'];
        $emailResponse = sendEmail($accessToken, $fromEmail, $correoResponsable, $subject, $body);
        if (isset($emailResponse['error'])) {
            error_log("Error al enviar el correo de notificación de acabados: " . $emailResponse['error']);
            return false;
        } else {
            error_log("Correo de notificación de acabados enviado correctamente a " . $correoResponsable);
            return true;
        }
    }
}

?>