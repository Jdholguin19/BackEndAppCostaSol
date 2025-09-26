<?php
require_once __DIR__ . '/../config/db.php';

$db = DB::getDB();

echo "<h2>🧪 Prueba del Sistema de Filtros para Responsables</h2>";

// 1. Verificar responsables en la base de datos
echo "<h3>1. Responsables en la base de datos:</h3>";
$stmt = $db->query("SELECT id, nombre, correo, token FROM responsable WHERE estado = 1");
$responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($responsables)) {
    echo "<p style='color: red;'>❌ No se encontraron responsables activos</p>";
} else {
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
    foreach ($responsables as $responsable) {
        echo "<p><strong>ID:</strong> {$responsable['id']} | <strong>Nombre:</strong> {$responsable['nombre']} | <strong>Correo:</strong> {$responsable['correo']}</p>";
        if ($responsable['token']) {
            echo "<p style='color: green;'>✅ Tiene token activo</p>";
        } else {
            echo "<p style='color: red;'>❌ No tiene token</p>";
        }
    }
    echo "</div>";
}

// 2. Verificar propiedades en la base de datos
echo "<h3>2. Propiedades en la base de datos:</h3>";
$stmt = $db->query("SELECT COUNT(*) as total FROM propiedad WHERE estado = 1");
$total_propiedades = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

echo "<p><strong>Total de propiedades activas:</strong> $total_propiedades</p>";

if ($total_propiedades > 20) {
    echo "<p style='color: green;'>✅ Se activará el filtro (más de 20 propiedades)</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No se activará el filtro (menos de 20 propiedades)</p>";
}

// 3. Probar el API de obtener todas las propiedades
echo "<h3>3. Prueba del API obtener_todas_propiedades.php:</h3>";

if (!empty($responsables) && $responsables[0]['token']) {
    $token = $responsables[0]['token'];
    echo "<p>Probando con token del responsable: {$responsables[0]['nombre']}</p>";
    
    // Simular la llamada al API
    $url = "https://app.costasol.com.ec/api/filtro_propiedad/obtener_todas_propiedades.php";
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px;'>";
    echo "<p><strong>URL de prueba:</strong> <a href='$url' target='_blank'>$url</a></p>";
    echo "<p><strong>Headers requeridos:</strong></p>";
    echo "<pre>Authorization: Bearer $token</pre>";
    echo "</div>";
    
    // Intentar hacer la llamada
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h4>Respuesta del API:</h4>";
    echo "<p><strong>Código HTTP:</strong> $http_code</p>";
    
    if ($response) {
        $data = json_decode($response, true);
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
        echo json_encode($data, JSON_PRETTY_PRINT);
        echo "</pre>";
        
        if ($data && $data['ok']) {
            echo "<p style='color: green;'>✅ API funciona correctamente</p>";
            echo "<p><strong>Propiedades encontradas:</strong> {$data['total']}</p>";
            echo "<p><strong>Mostrar filtro:</strong> " . ($data['mostrar_filtro'] ? 'SÍ' : 'NO') . "</p>";
            echo "<p><strong>Es responsable:</strong> " . ($data['is_responsable'] ? 'SÍ' : 'NO') . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Error en la respuesta del API</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se pudo conectar al API</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ No hay responsables con token para probar</p>";
}

// 4. Verificar la estructura de las tablas
echo "<h3>4. Estructura de las tablas:</h3>";

echo "<h4>Tabla responsable:</h4>";
$stmt = $db->query("DESCRIBE responsable");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<ul>";
foreach ($columns as $column) {
    echo "<li><strong>{$column['Field']}:</strong> {$column['Type']}</li>";
}
echo "</ul>";

echo "<h4>Tabla usuario:</h4>";
$stmt = $db->query("DESCRIBE usuario");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<ul>";
foreach ($columns as $column) {
    echo "<li><strong>{$column['Field']}:</strong> {$column['Type']}</li>";
}
echo "</ul>";

echo "<h3>🎯 Instrucciones para probar:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px;'>";
echo "<ol>";
echo "<li>Inicia sesión como responsable en la aplicación</li>";
echo "<li>Abre la consola del navegador (F12)</li>";
echo "<li>Revisa los logs que aparecen al cargar menu_front.php</li>";
echo "<li>Si eres responsable con más de 20 propiedades, deberías ver el botón 'Filtrar Propiedades'</li>";
echo "<li>Si no aparece, revisa los logs en la consola para identificar el problema</li>";
echo "</ol>";
echo "</div>";
?>
