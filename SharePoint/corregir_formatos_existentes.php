<?php
require_once __DIR__ . '/../config/db.php';

$db = DB::getDB();

echo "<h2>🔧 Corrector de Formatos Existentes</h2>";
echo "<div style='background: #fff3cd; padding: 15px; margin-bottom: 20px;'>";
echo "<h3>⚠️ IMPORTANTE:</h3>";
echo "Este script corregirá los formatos de villa en los registros existentes.<br>";
echo "Se ejecutará sobre las tablas <strong>propiedad</strong> y <strong>progreso_construccion</strong>.";
echo "</div>";

// Función para actualizar formato de villa
function actualizarFormatoVilla($db, $tabla, $campo_villa) {
    $resultados = [];
    
    // Buscar villas que son números de 1 dígito
    $stmt = $db->prepare("SELECT id, $campo_villa as villa_actual FROM $tabla WHERE $campo_villa REGEXP '^[0-9]$' AND LENGTH($campo_villa) = 1");
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $actualizados = 0;
    $errores = [];
    
    foreach ($registros as $registro) {
        $id = $registro['id'];
        $villa_actual = $registro['villa_actual'];
        $villa_corregida = str_pad($villa_actual, 2, '0', STR_PAD_LEFT);
        
        try {
            $update_stmt = $db->prepare("UPDATE $tabla SET $campo_villa = :villa_corregida WHERE id = :id");
            $update_stmt->execute([
                ':villa_corregida' => $villa_corregida,
                ':id' => $id
            ]);
            $actualizados++;
            
            echo "✅ $tabla ID $id: '$villa_actual' → '$villa_corregida'<br>";
            
        } catch (Exception $e) {
            $errores[] = "Error actualizando $tabla ID $id: " . $e->getMessage();
            echo "❌ Error $tabla ID $id: " . $e->getMessage() . "<br>";
        }
    }
    
    return ['actualizados' => $actualizados, 'errores' => $errores];
}

// Función para actualizar formato de manzana
function actualizarFormatoManzana($db, $tabla, $campo_manzana) {
    $resultados = [];
    
    // Buscar manzanas que son números y necesitan ser completadas a 4 dígitos
    $stmt = $db->prepare("SELECT id, $campo_manzana as manzana_actual FROM $tabla WHERE $campo_manzana REGEXP '^[0-9]+$' AND LENGTH($campo_manzana) < 4");
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $actualizados = 0;
    $errores = [];
    
    foreach ($registros as $registro) {
        $id = $registro['id'];
        $manzana_actual = $registro['manzana_actual'];
        $manzana_corregida = str_pad($manzana_actual, 4, '0', STR_PAD_RIGHT);
        
        try {
            $update_stmt = $db->prepare("UPDATE $tabla SET $campo_manzana = :manzana_corregida WHERE id = :id");
            $update_stmt->execute([
                ':manzana_corregida' => $manzana_corregida,
                ':id' => $id
            ]);
            $actualizados++;
            
            echo "✅ $tabla ID $id: Manzana '$manzana_actual' → '$manzana_corregida'<br>";
            
        } catch (Exception $e) {
            $errores[] = "Error actualizando $tabla ID $id: " . $e->getMessage();
            echo "❌ Error $tabla ID $id: " . $e->getMessage() . "<br>";
        }
    }
    
    return ['actualizados' => $actualizados, 'errores' => $errores];
}

echo "<h3>🔄 Iniciando corrección de formatos...</h3>";

// 1. Actualizar tabla propiedad
echo "<h4>📋 Tabla: propiedad</h4>";
$resultado_propiedad_villa = actualizarFormatoVilla($db, 'propiedad', 'villa');
$resultado_propiedad_solar = actualizarFormatoVilla($db, 'propiedad', 'solar');
$resultado_propiedad_manzana = actualizarFormatoManzana($db, 'propiedad', 'manzana');

// 2. Tabla progreso_construccion NO necesita corrección (ya está bien)
echo "<h4>📋 Tabla: progreso_construccion</h4>";
echo "✅ Esta tabla ya tiene el formato correcto, no necesita corrección.<br>";

// Resumen
echo "<hr>";
echo "<h3>📊 Resumen de Correcciones</h3>";
echo "<div style='background: #d4edda; padding: 15px;'>";
echo "<strong>🏠 Tabla propiedad:</strong><br>";
echo "• Villas corregidas: {$resultado_propiedad_villa['actualizados']}<br>";
echo "• Solar corregido: {$resultado_propiedad_solar['actualizados']}<br>";
echo "• Manzanas corregidas: {$resultado_propiedad_manzana['actualizados']}<br>";
echo "<strong>🏗️ Tabla progreso_construccion:</strong><br>";
echo "• No necesita corrección (ya está bien)<br>";

$total_actualizados = $resultado_propiedad_villa['actualizados'] + $resultado_propiedad_solar['actualizados'] + $resultado_propiedad_manzana['actualizados'];
echo "<strong>📈 Total de registros corregidos: $total_actualizados</strong><br>";
echo "</div>";

// Mostrar errores si los hay
$todos_errores = array_merge(
    $resultado_propiedad_villa['errores'], 
    $resultado_propiedad_solar['errores'], 
    $resultado_propiedad_manzana['errores']
);

if (!empty($todos_errores)) {
    echo "<h3>⚠️ Errores encontrados:</h3>";
    echo "<div style='background: #f8d7da; padding: 10px;'>";
    foreach ($todos_errores as $error) {
        echo "• $error<br>";
    }
    echo "</div>";
}

echo "<h3>🎉 Corrección completada</h3>";

// Verificación final
echo "<h3>🔍 Verificación final - Ejemplos corregidos:</h3>";
echo "<div style='background: #f8f9fa; padding: 10px;'>";

// Verificar villas corregidas
$stmt = $db->query("SELECT id, manzana, villa FROM propiedad WHERE villa LIKE '0%' LIMIT 3");
$ejemplos_villas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($ejemplos_villas)) {
    echo "<strong>✅ Ejemplos de villas con formato correcto:</strong><br>";
    foreach ($ejemplos_villas as $ejemplo) {
        echo "• ID {$ejemplo['id']}: MZ {$ejemplo['manzana']} - Villa {$ejemplo['villa']}<br>";
    }
} else {
    echo "No se encontraron villas con formato 0X.<br>";
}

echo "<br>";

// Verificar manzanas corregidas
$stmt = $db->query("SELECT id, manzana, villa FROM propiedad WHERE manzana LIKE '0%' LIMIT 3");
$ejemplos_manzanas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($ejemplos_manzanas)) {
    echo "<strong>✅ Ejemplos de manzanas con formato correcto:</strong><br>";
    foreach ($ejemplos_manzanas as $ejemplo) {
        echo "• ID {$ejemplo['id']}: MZ {$ejemplo['manzana']} - Villa {$ejemplo['villa']}<br>";
    }
} else {
    echo "No se encontraron manzanas con formato 0XXX.<br>";
}

echo "</div>";
?>
