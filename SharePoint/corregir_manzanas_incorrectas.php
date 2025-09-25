<?php
require_once __DIR__ . '/../config/db.php';

$db = DB::getDB();

echo "<h2>🔧 Corrector de Manzanas Incorrectas</h2>";
echo "<div style='background: #fff3cd; padding: 15px; margin-bottom: 20px;'>";
echo "<h3>⚠️ IMPORTANTE:</h3>";
echo "Este script corregirá las manzanas que fueron modificadas incorrectamente.<br>";
echo "Por ejemplo: <strong>0071</strong> → <strong>7100</strong> y <strong>0711</strong> → <strong>7110</strong>";
echo "</div>";

// Función para corregir manzanas que tienen ceros a la izquierda
function corregirManzanasIncorrectas($db) {
    $resultados = [];
    
    // Buscar manzanas que empiezan con 0 y tienen 4 dígitos
    $stmt = $db->prepare("SELECT id, manzana FROM propiedad WHERE manzana REGEXP '^0[0-9]{3}$'");
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $actualizados = 0;
    $errores = [];
    
    foreach ($registros as $registro) {
        $id = $registro['id'];
        $manzana_actual = $registro['manzana'];
        
        // Convertir 0071 → 7100, 0711 → 7110, etc.
        // Quitar el primer cero y moverlo al final
        $manzana_corregida = substr($manzana_actual, 1) . '0';
        
        try {
            $update_stmt = $db->prepare("UPDATE propiedad SET manzana = :manzana_corregida WHERE id = :id");
            $update_stmt->execute([
                ':manzana_corregida' => $manzana_corregida,
                ':id' => $id
            ]);
            $actualizados++;
            
            echo "✅ ID $id: '$manzana_actual' → '$manzana_corregida'<br>";
            
        } catch (Exception $e) {
            $errores[] = "Error actualizando ID $id: " . $e->getMessage();
            echo "❌ Error ID $id: " . $e->getMessage() . "<br>";
        }
    }
    
    return ['actualizados' => $actualizados, 'errores' => $errores];
}

echo "<h3>🔍 Buscando manzanas incorrectas...</h3>";

// Buscar manzanas que necesitan corrección
$stmt = $db->query("SELECT id, manzana FROM propiedad WHERE manzana REGEXP '^0[0-9]{3}$' LIMIT 10");
$ejemplos_incorrectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($ejemplos_incorrectos)) {
    echo "<h4>📋 Ejemplos de manzanas que necesitan corrección:</h4>";
    echo "<div style='background: #f8d7da; padding: 10px;'>";
    foreach ($ejemplos_incorrectos as $ejemplo) {
        $manzana_corregida = substr($ejemplo['manzana'], 1) . '0';
        echo "• ID {$ejemplo['id']}: '{$ejemplo['manzana']}' → '$manzana_corregida'<br>";
    }
    echo "</div>";
    
    echo "<h3>🔄 Iniciando corrección...</h3>";
    $resultado = corregirManzanasIncorrectas($db);
    
    echo "<hr>";
    echo "<h3>📊 Resumen de Corrección</h3>";
    echo "<div style='background: #d4edda; padding: 15px;'>";
    echo "<strong>🏠 Tabla propiedad:</strong><br>";
    echo "• Manzanas corregidas: {$resultado['actualizados']}<br>";
    echo "</div>";
    
    // Mostrar errores si los hay
    if (!empty($resultado['errores'])) {
        echo "<h3>⚠️ Errores encontrados:</h3>";
        echo "<div style='background: #f8d7da; padding: 10px;'>";
        foreach ($resultado['errores'] as $error) {
            echo "• $error<br>";
        }
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #d4edda; padding: 15px;'>";
    echo "✅ <strong>¡Excelente!</strong> No se encontraron manzanas con formato incorrecto.<br>";
    echo "Todas las manzanas ya tienen el formato correcto.";
    echo "</div>";
}

echo "<h3>🔍 Verificación final - Ejemplos de manzanas corregidas:</h3>";
echo "<div style='background: #f8f9fa; padding: 10px;'>";

$stmt = $db->query("SELECT id, manzana, villa FROM propiedad WHERE manzana REGEXP '^[0-9]{2}00$' LIMIT 5");
$ejemplos_corregidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($ejemplos_corregidos)) {
    echo "<strong>✅ Ejemplos de manzanas con formato correcto:</strong><br>";
    foreach ($ejemplos_corregidos as $ejemplo) {
        echo "• ID {$ejemplo['id']}: MZ {$ejemplo['manzana']} - Villa {$ejemplo['villa']}<br>";
    }
} else {
    echo "No se encontraron manzanas con formato XX00.<br>";
}

echo "</div>";

echo "<h3>🎉 Corrección completada</h3>";
?>
