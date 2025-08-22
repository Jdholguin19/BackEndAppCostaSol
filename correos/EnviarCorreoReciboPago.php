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

function sendEmail($accessToken, $fromEmail, $toEmail, $subject, $body, $attachmentPath = null) {
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
            ],
            "ccRecipients" => [
                [
                    "emailAddress" => [
                        "address" => "pagos@thaliavictoria.com.ec"
                    ]
                ]
            ]
        ]
    ];

    if ($attachmentPath && file_exists($attachmentPath)) {
        $fileContent = base64_encode(file_get_contents($attachmentPath));
        $emailData['message']['attachments'] = [
            [
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'name' => basename($attachmentPath),
                'contentBytes' => $fileContent
            ]
        ];
    }

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

function retrySendEmail($accessToken, $fromEmail, $toEmail, $subject, $body, $attachmentPath = null, $retries = 3) {
    $attempt = 0;
    while ($attempt < $retries) {
        $response = sendEmail($accessToken, $fromEmail, $toEmail, $subject, $body, $attachmentPath);
        if (isset($response['success'])) {
            return $response;
        }
        $attempt++;
        sleep(2); // Espera 2 segundos antes de reintentar
    }
    return $response; // Devuelve el último error si todos los intentos fallan
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mail']) && isset($_POST['cedula']) && isset($_FILES['archivo'])) {
        $mail = $_POST['mail'];
        $cedula = $_POST['cedula'];
        $archivo = $_FILES['archivo'];

        require_once "NumerosTelefonos.php";
        $resultado = buscarCedula($cedula);

        // Verifica que el archivo haya sido subido correctamente
        if ($archivo['error'] == 0) {
            // Verifica que el archivo sea JPG, PNG o PDF
            $fileType = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            if ($fileType != "jpg" && $fileType != "png" && $fileType != "pdf") {
                echo "Solo se permiten archivos JPG, PNG y PDF.";
                exit;
            }

            // Guarda el archivo temporalmente en el servidor
            $temp_path = $archivo['tmp_name'];
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_path = $upload_dir . basename($archivo['name']);
            if (move_uploaded_file($temp_path, $file_path)) {
                // Envía el correo con el archivo adjunto
                $subject = "Recibo de Pago";
                $body = "
                    <p>Estimado, {$resultado['nombres']} {$resultado['apellidos']}. </p>
                    <p>Excelente día, adjunto recibo correspondiente al pago realizado.</p>
                    <p>Los valores en estos recibos, están sujetos a validación con el Fideicomiso.</p>
                    <p>Te recordamos nuestra política de privacidad para proteger tus datos personales <a href='https://costasol.com.ec/politica-de-proteccion-de-datos/'>Política de Protección de Datos</a></p>
                    <p>Este correo es generado automáticamente. No es necesario responder.</p>
                    <p>Atentamente, CostaSol</p>
                    <img src='https://bot.costasol.com.ec/FirmaThali.png' alt='Firma' width='300' height='100' />
                ";

                $tenantId = 'b9618ac6-2648-41ed-bb4f-03bcd94a7493'; // Id. de directorio (inquilino)
                $clientId = 'd8f17735-bbd4-4fc4-a193-fd9b645880be'; // Id. de aplicación (cliente)
                $clientSecret = 'e7X8Q~OtM4X~LZ.i5wHvIIukGHK8Tb4yq3Xkpbat'; // Secreto del cliente (Valor)
                $fromEmail = 'jtrejo@thaliavictoria.com.ec'; // Reemplazar con el correo electrónico del buzón desde el cual se enviará el correo

                $tokenResponse = getAccessToken($tenantId, $clientId, $clientSecret);
                if (isset($tokenResponse['error'])) {
                    echo "Error al obtener el token de acceso: " . $tokenResponse['error'];
                } else {
                    $accessToken = $tokenResponse['access_token'];
                    $emailResponse = retrySendEmail($accessToken, $fromEmail, $mail, $subject, $body, $file_path);
                    if (isset($emailResponse['error'])) {
                        echo "Error al enviar el correo electrónico: " . $emailResponse['error'];
                    } else {
                        // Redirigir a SubidaRecibosPagosBOT.php después de enviar el correo
                        header("Location: SubidaRecibosPagosBOT.php");
                        exit();
                    }
                }
            } else {
                echo "Error al subir el archivo.";
            }
        } else {
            echo "Error al subir el archivo: " . $archivo['error'];
        }
    }
}
?>
