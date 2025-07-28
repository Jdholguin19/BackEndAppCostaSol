<?php
// CONFIGURACIÓN
require_once __DIR__ . '/../config/db.php';
$db = DB::getDB();

// Tus credenciales de Azure AD
$tenantId = 'b9618ac6-2648-41ed-bb4f-03bcd94a7493';
$clientId = '6528f372-ed1b-40ef-950a-a950219c5f9e';
$clientSecret = 'mNA8Q~ayyNJNwbtKOMP-GGBTtl-dIwmai2uqxdgz';
$domain = 'constv-my.sharepoint.com';
$user = 'aburgos_thaliavictoria_com_ec';

function getAccessToken($tenantId, $clientId, $clientSecret) {
    $url = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
    $data = http_build_query([
        'client_id' => $clientId,
        'scope' => 'https://graph.microsoft.com/.default',
        'client_secret' => $clientSecret,
        'grant_type' => 'client_credentials'
    ]);
    $opts = ['http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $data
    ]];
    $res = file_get_contents($url, false, stream_context_create($opts));
    return json_decode($res, true)['access_token'] ?? die("❌ Error obteniendo token");
}

function getSiteId($accessToken, $domain, $user) {
    $url = "https://graph.microsoft.com/v1.0/sites/$domain:/personal/$user:";
    $opts = ['http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $accessToken"
    ]];
    $res = file_get_contents($url, false, stream_context_create($opts));
    return json_decode($res, true)['id'] ?? die("❌ Error obteniendo siteId");
}

function obtenerUrlTemporal($accessToken, $siteId, $driveItemId) {
    $url = "https://graph.microsoft.com/v1.0/sites/$siteId/drive/items/$driveItemId";
    $opts = ['http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $accessToken"
    ]];
    $res = file_get_contents($url, false, stream_context_create($opts));
    $data = json_decode($res, true);
    return $data['@microsoft.graph.downloadUrl'] ?? die("❌ No se pudo obtener la URL temporal de descarga");
}

// Leer el ID
$id = $_GET['id'] ?? null;

if (!$id) {
    die("❌ No se proporcionó ID de imagen.");
}

// Leer drive_item_id de la BD
$stmt = $db->prepare("SELECT drive_item_id FROM progreso_construccion WHERE id = :id");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if (!$row) {
    die("❌ No existe imagen para este ID.");
}

$driveItemId = $row['drive_item_id'] ?? '';
if (!$driveItemId) {
    die("❌ El registro no tiene drive_item_id.");
}

// Ahora obtener la imagen
$token = getAccessToken($tenantId, $clientId, $clientSecret);
$siteId = getSiteId($token, $domain, $user);
$urlImagen = obtenerUrlTemporal($token, $siteId, $driveItemId);

// Leer la imagen y devolverla
$opts = [
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $token"
    ]
];
$imgData = file_get_contents($urlImagen, false, stream_context_create($opts));

// Devolverla al navegador
header("Content-Type: image/jpeg"); // Cambia si soportas otros formatos
echo $imgData;
