<?php
require_once __DIR__ . '/../config/db.php';

$db = DB::getDB();

echo "<h2>🧪 Prueba Directa del API de Filtros</h2>";

// 1. Obtener un token de responsable
echo "<h3>1. Obteniendo token de responsable...</h3>";
$stmt = $db->query("SELECT id, nombre, token FROM responsable WHERE estado = 1 AND token IS NOT NULL LIMIT 1");
$responsable = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$responsable) {
    echo "<p style='color: red;'>❌ No se encontró responsable con token activo</p>";
    exit;
}

echo "<p>✅ Responsable encontrado: <strong>{$responsable['nombre']}</strong></p>";
echo "<p>Token: " . substr($responsable['token'], 0, 20) . "...</p>";

// 2. Probar la consulta SQL directamente
echo "<h3>2. Probando consulta SQL...</h3>";
$sql = 'SELECT p.id, p.manzana, p.villa, p.solar,
               u.nombres as cliente_nombre, u.apellidos as cliente_apellidos,
               CONCAT(u.nombres, " ", u.apellidos) as cliente_completo,
               t.nombre as tipo, urb.nombre as urbanizacion
        FROM propiedad p
        LEFT JOIN usuario u ON u.id = p.id_usuario
        JOIN tipo_propiedad t ON t.id = p.tipo_id
        JOIN urbanizacion urb ON urb.id = p.id_urbanizacion
        ORDER BY urb.nombre, p.manzana, p.villa
        LIMIT 5';

try {
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>✅ Consulta SQL exitosa</p>";
    echo "<p><strong>Propiedades encontradas:</strong> " . count($propiedades) . "</p>";
    
    if (!empty($propiedades)) {
        echo "<h4>Primeras 5 propiedades:</h4>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
        foreach ($propiedades as $p) {
            echo "<p><strong>ID:</strong> {$p['id']} | <strong>Tipo:</strong> {$p['tipo']} | <strong>Urbanización:</strong> {$p['urbanizacion']} | <strong>MZ:</strong> {$p['manzana']} | <strong>Villa:</strong> {$p['villa']} | <strong>Cliente:</strong> {$p['cliente_completo']}</p>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en consulta SQL: " . $e->getMessage() . "</p>";
    exit;
}

// 3. Simular el API completo
echo "<h3>3. Simulando el API completo...</h3>";

// Verificar token
$stmt_token = $db->prepare('SELECT u.id, u.nombres, u.apellidos, "usuario" as tipo_usuario 
                           FROM usuario u 
                           WHERE u.token = :token');
$stmt_token->execute([':token' => $responsable['token']]);
$usuario = $stmt_token->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    $stmt_responsable = $db->prepare('SELECT r.id, r.nombre as nombres, "" as apellidos, "responsable" as tipo_usuario 
                                     FROM responsable r 
                                     WHERE r.token = :token AND r.estado = 1');
    $stmt_responsable->execute([':token' => $responsable['token']]);
    $usuario = $stmt_responsable->fetch(PDO::FETCH_ASSOC);
}

if ($usuario) {
    echo "<p>✅ Autenticación exitosa</p>";
    echo "<p><strong>Tipo de usuario:</strong> {$usuario['tipo_usuario']}</p>";
    echo "<p><strong>Nombre:</strong> {$usuario['nombres']}</p>";
    
    // Obtener todas las propiedades
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear respuesta
    $propiedades_formateadas = array_map(function($p) {
        return [
            'id' => (int)$p['id'],
            'tipo' => $p['tipo'],
            'urbanizacion' => $p['urbanizacion'],
            'manzana' => $p['manzana'],
            'villa' => $p['villa'],
            'solar' => $p['solar'],
            'cliente_nombre' => $p['cliente_nombre'] ?? 'Sin asignar',
            'cliente_apellidos' => $p['cliente_apellidos'] ?? '',
            'cliente_completo' => $p['cliente_completo'] ?? 'Sin asignar',
            'display_name' => $p['tipo'] . ' ' . $p['urbanizacion'] . ' MZ ' . $p['manzana'] . ' V ' . $p['villa']
        ];
    }, $propiedades);
    
    // Determinar si mostrar filtro
    $is_responsable = ($usuario['tipo_usuario'] === 'responsable');
    $mostrar_filtro = $is_responsable && count($propiedades_formateadas) > 20;
    
    $respuesta = [
        'ok' => true,
        'propiedades' => $propiedades_formateadas,
        'total' => count($propiedades_formateadas),
        'mostrar_filtro' => $mostrar_filtro,
        'is_responsable' => $is_responsable,
        'tipo_usuario' => $usuario['tipo_usuario']
    ];
    
    echo "<p>✅ API simulado exitosamente</p>";
    echo "<p><strong>Total de propiedades:</strong> {$respuesta['total']}</p>";
    echo "<p><strong>Mostrar filtro:</strong> " . ($respuesta['mostrar_filtro'] ? 'SÍ' : 'NO') . "</p>";
    echo "<p><strong>Es responsable:</strong> " . ($respuesta['is_responsable'] ? 'SÍ' : 'NO') . "</p>";
    
    echo "<h4>Respuesta JSON completa:</h4>";
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 300px; overflow-y: auto;'>";
    echo json_encode($respuesta, JSON_PRETTY_PRINT);
    echo "</pre>";
    
} else {
    echo "<p style='color: red;'>❌ Error en autenticación</p>";
}

echo "<h3>🎯 Conclusión:</h3>";
if (isset($respuesta) && $respuesta['ok']) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px;'>";
    echo "<p>✅ <strong>El API debería funcionar correctamente</strong></p>";
    echo "<p>Si no aparece el filtro en el frontend, el problema puede estar en:</p>";
    echo "<ul>";
    echo "<li>La conexión entre frontend y backend</li>";
    echo "<li>Los headers de autorización</li>";
    echo "<li>El manejo de errores en JavaScript</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px;'>";
    echo "<p>❌ <strong>Hay un problema con el API</strong></p>";
    echo "</div>";
}
?>
