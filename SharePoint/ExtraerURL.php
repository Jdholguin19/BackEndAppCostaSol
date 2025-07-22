<?php
require_once __DIR__ . '/../config/db.php';

$tenantId = 'b9618ac6-2648-41ed-bb4f-03bcd94a7493';
$clientId = '6528f372-ed1b-40ef-950a-a950219c5f9e';
$clientSecret = 'mNA8Q~ayyNJNwbtKOMP-GGBTtl-dIwmai2uqxdgz';

$domain = 'constv-my.sharepoint.com';
$user = 'aburgos_thaliavictoria_com_ec';
$rootPath = '- FEDATARIO/FOTOS DE INSPECCIONES';

$totalImagenes = 0;
$db = DB::getDB();

function getAccessToken($tenantId, $clientId, $clientSecret) {
    $url = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
    $data = http_build_query([
        'client_id' => $clientId,
        'scope' => 'https://graph.microsoft.com/.default',
        'client_secret' => $clientSecret,
        'grant_type' => 'client_credentials'
    ]);
    $opts = ['http' => ['method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded', 'content' => $data]];
    $res = file_get_contents($url, false, stream_context_create($opts));
    return json_decode($res, true)['access_token'] ?? die("❌ Error obteniendo token");
}

function getSiteId($accessToken, $domain, $user) {
    $url = "https://graph.microsoft.com/v1.0/sites/$domain:/personal/$user:";
    $opts = ['http' => ['method' => 'GET', 'header' => "Authorization: Bearer $accessToken"]];
    $res = file_get_contents($url, false, stream_context_create($opts));
    return json_decode($res, true)['id'] ?? die("❌ Error obteniendo siteId");
}

function detectarEtapa($ruta) {
    if (strpos($ruta, 'Visita 1') !== false) return 1;
    if (strpos($ruta, 'Visita 2') !== false) return 2;
    if (strpos($ruta, 'Visita 3') !== false) return 3;
    if (strpos($ruta, 'Visita 4') !== false) return 4;
    return null;
}

function extraerMzVilla($ruta) {
    $partes = explode('/', $ruta);
    foreach ($partes as $i => $parte) {
        if (preg_match('/^\d{4}$/', $parte) && isset($partes[$i + 1])) {
            $sub = $partes[$i + 1];
            if (preg_match('/^\d{4}-(.+)$/', $sub, $matches)) {
                return [$parte, $matches[1]];
            }
        }
    }
    return [null, null];
}

function guardarEnBD($db, $data) {
    $stmt = $db->prepare("INSERT INTO progreso_construccion (
        id_etapa, mz, villa, ruta_descarga_sharepoint, ruta_visualizacion_sharepoint, drive_item_id,
        fecha_creado_sharepoint, usuario_creador, fecha_modificado_sharepoint,
        usuario_modificado_sharepoint, url_imagen
    ) VALUES (
        :id_etapa, :mz, :villa, :ruta_descarga, :ruta_visual, :drive_item_id, :fecha_creado,
        :usuario_creador, :fecha_modificado, :usuario_modificado, :url
    )");

    $stmt->execute([
        ':id_etapa' => $data['id_etapa'],
        ':mz' => $data['mz'],
        ':villa' => $data['villa'],
        ':ruta_descarga' => $data['ruta_descarga'],
        ':ruta_visual' => $data['ruta_visual'],
        ':drive_item_id'      => $data['drive_item_id'], 
        ':fecha_creado' => $data['fecha_creado'],
        ':usuario_creador' => $data['usuario_creador'],
        ':fecha_modificado' => $data['fecha_modificado'],
        ':usuario_modificado' => $data['usuario_modificado'],
        ':url' => $data['url_imagen']
    ]);
}

function listarContenido($accessToken, $siteId, $ruta, $nivel = 0, &$contador = 0, $rutaCompleta = "") {
    global $db;
    $encodedPath = rawurlencode("/$ruta");
    $url = "https://graph.microsoft.com/v1.0/sites/$siteId/drive/root:$encodedPath:/children";
    $opts = ['http' => ['method' => 'GET', 'header' => "Authorization: Bearer $accessToken"]];
    $res = file_get_contents($url, false, stream_context_create($opts));
    $items = json_decode($res, true)['value'] ?? [];

    foreach ($items as $item) {
        $nombre = $item['name'];
        $pathActual = $rutaCompleta . '/' . $nombre;

        if (isset($item['folder'])) {
            echo "<div><strong>📁 $pathActual</strong></div>";
            listarContenido($accessToken, $siteId, "$ruta/$nombre", $nivel + 1, $contador, $pathActual);
        } elseif (isset($item['@microsoft.graph.downloadUrl'])) {
            $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $contador++;
                $urlDescarga = $item['@microsoft.graph.downloadUrl'];
                $urlSharePoint = $item['webUrl'] ?? '';

                $creado = date("Y-m-d H:i", strtotime($item['createdDateTime'] ?? ''));
                $modificado = date("Y-m-d H:i", strtotime($item['lastModifiedDateTime'] ?? ''));

                $creadoPor = $item['createdBy']['user']['displayName'] ?? 'Desconocido';
                $creadoEmail = $item['createdBy']['user']['email'] ?? '';
                $modificadoPor = $item['lastModifiedBy']['user']['displayName'] ?? 'Desconocido';

                list($mz, $villa) = extraerMzVilla($pathActual);
                $etapa = detectarEtapa($pathActual);

                echo "<div style='margin-bottom: 15px;'>
                    🖼️ <strong>$pathActual</strong><br>
                    🔗 <a href='$urlDescarga' target='_blank'>[Descargar]</a> |
                    👁️ <a href='$urlSharePoint' target='_blank'>[Ver en SharePoint]</a><br>
                    🕒 Creado: $creado | Por: $creadoPor" .
                    ($creadoEmail ? " &lt;$creadoEmail&gt;" : "") . "<br>
                    📝 Modificado: $modificado | Por: $modificadoPor
                </div>";

                guardarEnBD($db, [
                    'id_etapa' => $etapa,
                    'mz' => $mz,
                    'villa' => $villa,
                    'ruta_descarga' => $urlDescarga,
                    'ruta_visual' => $urlSharePoint,
                    'drive_item_id' => $item['id'],
                    'fecha_creado' => date("Y-m-d H:i:s", strtotime($item['createdDateTime'] ?? '')),
                    'usuario_creador' => $creadoPor,
                    'fecha_modificado' => date("Y-m-d H:i:s", strtotime($item['lastModifiedDateTime'] ?? '')),
                    'usuario_modificado' => $modificadoPor,
                    'url_imagen' => $pathActual
                ]);
            }
        }
    }
}

$token = getAccessToken($tenantId, $clientId, $clientSecret);
$siteId = getSiteId($token, $domain, $user);

echo "<h2>📂 Explorando: $rootPath</h2>";
listarContenido($token, $siteId, $rootPath, 0, $totalImagenes, $rootPath);

echo "<hr><h3>🧮 Total de imágenes encontradas: $totalImagenes</h3>";
