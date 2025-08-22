<?php

function getAccessToken($tenantId, $clientId, $clientSecret) {
    $url = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
    $headers = ['Content-Type: application/x-www-form-urlencoded'];
    $data = [
        'grant_type' => 'client_credentials',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'scope' => 'https://graph.microsoft.com/.default'
    ];

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => http_build_query($data)
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        $error = error_get_last();
        return ['error' => 'Connection error while getting access token: ' . $error['message']];
    }

    $responseData = json_decode($response, true);
    if (isset($responseData['access_token'])) {
        return ['access_token' => $responseData['access_token']];
    } else {
        return ['error' => 'Error in response: ' . json_encode($responseData)];
    }
}

function sendEmail($accessToken, $fromEmail, $toEmail, $subject, $body) {
    $url = "https://graph.microsoft.com/v1.0/users/$fromEmail/sendMail";
    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ];

    $emailData = [
        "message" => [
            "subject" => $subject,
            "body" => [
                "contentType" => "HTML",
                "content" => $body
            ],
            "toRecipients" => [
                [
                    "emailAddress" => [
                        "address" => $toEmail
                    ]
                ]
            ]
        ]
    ];

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => json_encode($emailData),
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        $error = error_get_last();
        return ['error' => 'Connection error while sending email: ' . $error['message']];
    }

    $responseData = json_decode($response, true);
    if (isset($responseData['error'])) {
        return ['error' => 'Error in response: ' . json_encode($responseData)];
    }

    return ['success' => 'Email sent successfully'];
}

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

?>