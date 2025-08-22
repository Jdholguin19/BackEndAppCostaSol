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

function EnviarCorreo($clave_temporal, $toEmail, $telefonoCliente){
    $tenantId = 'b9618ac6-2648-41ed-bb4f-03bcd94a7493'; // Id. de directorio (inquilino)
    $clientId = 'd8f17735-bbd4-4fc4-a193-fd9b645880be'; // Id. de aplicación (cliente)
    $clientSecret = 'e7X8Q~OtM4X~LZ.i5wHvIIukGHK8Tb4yq3Xkpbat'; // Secreto del cliente (Valor)
    $fromEmail = 'sistemas@thaliavictoria.com.ec'; // Reemplazar con el correo electrónico del buzón desde el cual se enviará el correo
    $fecha_hora_actual = date('Y-m-d H:i:s');

    $subject = 'Clave Temporal para Autorización de Chatbot';
    $body = 
    "Estimado usuario,<br><br>
    Le informamos que su clave temporal es: $clave_temporal. Esta clave fue generada automáticamente el $fecha_hora_actual.<br>
    Por favor, ingrese esta clave en nuestro chatbot para autorizar el uso de nuestros servicios al número de teléfono +$telefonoCliente.<br>
    Al hacerlo, usted acepta que su número sea registrado y habilitado para interactuar con el chatbot.<br><br>
    Te recordamos nuestra política de privacidad para proteger tus datos personales. <a href='https://costasol.com.ec/politica-de-proteccion-de-datos/'>Política de Protección de Datos</a><br><br>
    Este correo es generado automáticamente. No es necesario responder.<br><br>
    Atentamente, CostaSol<br>
    <img src='https://bot.costasol.com.ec/FirmaThali.png' alt='Firma' width='300' height='100' />";
    

    $tokenResponse = getAccessToken($tenantId, $clientId, $clientSecret);
    if (isset($tokenResponse['error'])) {
        echo "Error al obtener el token de acceso: " . $tokenResponse['error'];
    } else {
        $accessToken = $tokenResponse['access_token'];
        $emailResponse = sendEmail($accessToken, $fromEmail, $toEmail, $subject, $body);
        if (isset($emailResponse['error'])) {
            echo "Error al enviar el correo electrónico: " . $emailResponse['error'];
        } else {
            echo "Correo electrónico enviado correctamente.";
        }
    }
}
?>
