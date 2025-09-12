<?php
require_once 'config.php';

// URL para obtener los datos de un REPORTE específico
$accountId = 'AcNcc9rydX9F';
$reportId = 'Todos_los_Items_2'; // ID del reporte que descubrimos
$url = KISSFLOW_API_HOST . '/flow/2/' . $accountId . '/report/' . $reportId;

$ch = curl_init($url);

// Combina el Access Key ID y el Secret
$auth_token = KISSFLOW_ACCESS_KEY_ID . ':' . KISSFLOW_ACCESS_KEY_SECRET;

// Configura la cabecera de autorización con el formato correcto
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Kissflow-Auth ' . $auth_token
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$error = curl_error($ch);

curl_close($ch);

if ($error) {
    echo "Error en cURL: " . $error;
} else {
    // Imprimir la respuesta JSON para análisis
    header('Content-Type: application/json');
    $data = json_decode($response);
    echo json_encode($data, JSON_PRETTY_PRINT);
}
?>