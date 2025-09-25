<?php
require_once __DIR__ . '/../config/db.php';

// Cargar configuración
$config = require __DIR__ . '/config_importacion.php';

$db = DB::getDB();

// Configuración desde archivo
$csv_file = $config['csv_file'];
$rol_cliente_default = $config['rol_cliente_default'];
$tipo_propiedad_default = $config['tipo_propiedad_default'];
$etapa_default = $config['etapa_default'];
$estado_default = $config['estado_default'];

echo "<h2>📊 Importador de Datos CSV</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; margin-bottom: 20px;'>";
echo "<h3>⚙️ Configuración:</h3>";
echo "• Rol Cliente: $rol_cliente_default<br>";
echo "• Tipo Propiedad: $tipo_propiedad_default<br>";
echo "• Etapa Inicial: $etapa_default<br>";
echo "• Estado Inicial: $estado_default<br>";
echo "• Formato Nombres: " . $config['procesamiento']['formato_nombres'] . "<br>";
echo "• Fechas de Propiedad: NULL (fecha_compra, fecha_hipotecario, fecha_entrega)<br>";
echo "</div>";

// Función para limpiar y validar datos
function limpiarDatos($dato) {
    return trim($dato, '" \t\n\r\0\x0B');
}

// Función para truncar texto a un máximo de caracteres
function truncarTexto($texto, $maximo = 60) {
    if (strlen($texto) <= $maximo) {
        return $texto;
    }
    return substr($texto, 0, $maximo - 3) . '...';
}

// Función para generar contraseña temporal
function generarContrasenaTemporal($config) {
    return password_hash($config['contrasena_temporal'], PASSWORD_DEFAULT);
}

// Función para verificar si usuario existe
function usuarioExiste($db, $cedula, $correo) {
    $stmt = $db->prepare("SELECT id FROM usuario WHERE cedula = :cedula OR correo = :correo");
    $stmt->execute([':cedula' => $cedula, ':correo' => $correo]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Función para verificar si propiedad existe
function propiedadExiste($db, $id_usuario, $id_urbanizacion, $manzana, $villa) {
    $stmt = $db->prepare("SELECT id FROM propiedad WHERE id_usuario = :id_usuario AND id_urbanizacion = :id_urbanizacion AND manzana = :manzana AND villa = :villa");
    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':id_urbanizacion' => $id_urbanizacion,
        ':manzana' => $manzana,
        ':villa' => $villa
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Función para obtener la etapa más alta de progreso_construccion
function obtenerEtapaMasAlta($db, $id_urbanizacion, $manzana, $villa) {
    $stmt = $db->prepare("SELECT MAX(id_etapa) as etapa_mas_alta 
                         FROM progreso_construccion 
                         WHERE id_urbanizacion = :id_urbanizacion 
                         AND mz = :manzana 
                         AND villa = :villa 
                         AND estado = 1");
    $stmt->execute([
        ':id_urbanizacion' => $id_urbanizacion,
        ':manzana' => $manzana,
        ':villa' => $villa
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['etapa_mas_alta'] ?? null;
}

// Función para insertar usuario
function insertarUsuario($db, $datos, $rol_cliente_default, $config) {
    $contrasena_hash = generarContrasenaTemporal($config);
    
    $stmt = $db->prepare("INSERT INTO usuario (
        rol_id, nombres, apellidos, cedula, telefono, correo, contrasena_hash,
        numero_propiedades, fecha_insertado
    ) VALUES (
        :rol_id, :nombres, :apellidos, :cedula, :telefono, :correo, :contrasena_hash,
        :numero_propiedades, NOW()
    )");
    
    $stmt->execute([
        ':rol_id' => $rol_cliente_default,
        ':nombres' => $datos['nombres'],
        ':apellidos' => $datos['apellidos'],
        ':cedula' => $datos['cedula'],
        ':telefono' => $datos['telefono'],
        ':correo' => $datos['correo'],
        ':contrasena_hash' => $contrasena_hash,
        ':numero_propiedades' => 1
    ]);
    
    return $db->lastInsertId();
}

// Función para insertar propiedad
function insertarPropiedad($db, $datos, $id_usuario, $tipo_propiedad_default, $estado_default, $etapa_mas_alta = null) {
    // Usar la etapa más alta si existe, sino usar la etapa por defecto
    $etapa_final = $etapa_mas_alta ?? 1;
    
    $stmt = $db->prepare("INSERT INTO propiedad (
        id_usuario, tipo_id, etapa_id, estado_id, id_urbanizacion,
        manzana, solar, villa, fecha_compra, fecha_hipotecario, 
        fecha_entrega, fecha_insertado
    ) VALUES (
        :id_usuario, :tipo_id, :etapa_id, :estado_id, :id_urbanizacion,
        :manzana, :solar, :villa, NULL, NULL, NULL, NOW()
    )");
    
    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':tipo_id' => $tipo_propiedad_default,
        ':etapa_id' => $etapa_final,
        ':estado_id' => $estado_default,
        ':id_urbanizacion' => $datos['urbanizacion'],
        ':manzana' => $datos['mz'],
        ':solar' => $datos['villa'], // Asumiendo que solar = villa
        ':villa' => $datos['villa']
    ]);
    
    return $db->lastInsertId();
}

// Verificar que el archivo CSV existe
if (!file_exists($csv_file)) {
    die("❌ Error: El archivo CSV no existe: $csv_file");
}

echo "<h3>📂 Procesando archivo: " . basename($csv_file) . "</h3>";

$handle = fopen($csv_file, 'r');
if (!$handle) {
    die("❌ Error: No se pudo abrir el archivo CSV");
}

// Leer encabezados
$headers = fgetcsv($handle);
if (!$headers) {
    die("❌ Error: No se pudieron leer los encabezados del CSV");
}

echo "<div style='background: #f9f9f9; padding: 10px; margin: 10px 0;'>";
echo "<strong>📋 Encabezados encontrados:</strong> " . implode(', ', $headers) . "<br>";
echo "</div>";

// Contadores
$total_registros = 0;
$usuarios_insertados = 0;
$propiedades_insertadas = 0;
$errores = [];

echo "<h3>🔄 Procesando registros...</h3>";
echo "<div style='max-height: 400px; overflow-y: auto; background: #f5f5f5; padding: 10px;'>";

while (($data = fgetcsv($handle)) !== FALSE) {
    $total_registros++;
    
    // Crear array asociativo
    $row = array_combine($headers, $data);
    
    // Limpiar datos
    $datos = [
        'urbanizacion' => limpiarDatos($row['urbanizacion']),
        'mz' => limpiarDatos($row['mz']),
        'villa' => limpiarDatos($row['villa']),
        'cedula' => limpiarDatos($row['cedula']),
        'cliente' => limpiarDatos($row['cliente']),
        'correo' => limpiarDatos($row['correo']),
        'telefono' => limpiarDatos($row['telefono'])
    ];
    
    // Si el correo está vacío, generar uno con la cédula
    if (empty($datos['correo'])) {
        $datos['correo'] = $datos['cedula'] . '@placeholder.costasol.com';
    }
    
    // Validaciones básicas
    if (empty($datos['cedula']) || empty($datos['cliente'])) {
        $errores[] = "Fila $total_registros: Cédula o cliente vacío";
        continue;
    }
    
    // Separar nombres y apellidos del cliente según el formato configurado
    $nombres_completos = explode(' ', $datos['cliente']);
    $apellidos = [];
    $nombres = [];
    
    if ($config['procesamiento']['formato_nombres'] === 'apellidos_primero') {
        // Formato: APELLIDO1 APELLIDO2 NOMBRE1 NOMBRE2
        // Los primeros 2 elementos son apellidos, el resto son nombres
        if (count($nombres_completos) >= 2) {
            $apellidos = array_slice($nombres_completos, 0, 2);
            $nombres = array_slice($nombres_completos, 2);
        } else {
            $nombres = $nombres_completos;
            $apellidos = $config['procesamiento']['apellidos_por_defecto'];
        }
    } else {
        // Formato: NOMBRE1 NOMBRE2 APELLIDO1 APELLIDO2 (formato anterior)
        if (count($nombres_completos) >= 2) {
            $apellidos = array_slice($nombres_completos, -2);
            $nombres = array_slice($nombres_completos, 0, -2);
        } else {
            $nombres = $nombres_completos;
            $apellidos = $config['procesamiento']['apellidos_por_defecto'];
        }
    }
    
    // Truncar nombres y apellidos para evitar errores de longitud
    $nombres_original = implode(' ', $nombres);
    $apellidos_original = implode(' ', $apellidos);
    
    $datos['nombres'] = truncarTexto($nombres_original, 60);
    $datos['apellidos'] = truncarTexto($apellidos_original, 60);
    
    // Verificar si se truncó algo para mostrar en debug
    $nombres_truncados = strlen($nombres_original) > 60;
    $apellidos_truncados = strlen($apellidos_original) > 60;
    
    try {
        // Verificar si el usuario ya existe
        $usuario_existente = usuarioExiste($db, $datos['cedula'], $datos['correo']);
        
        if ($usuario_existente) {
            $id_usuario = $usuario_existente['id'];
            echo "👤 Usuario existente: {$datos['cliente']} (ID: $id_usuario)<br>";
        } else {
            // Insertar nuevo usuario
            $id_usuario = insertarUsuario($db, $datos, $rol_cliente_default, $config);
            $usuarios_insertados++;
            echo "✅ Usuario insertado: {$datos['cliente']}<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;📝 Nombres: '{$datos['nombres']}' | Apellidos: '{$datos['apellidos']}' (ID: $id_usuario)<br>";
            if ($nombres_truncados || $apellidos_truncados) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;⚠️ Texto truncado: ";
                if ($nombres_truncados) echo "Nombres ";
                if ($apellidos_truncados) echo "Apellidos ";
                echo "(máx 60 caracteres)<br>";
            }
            echo "&nbsp;&nbsp;&nbsp;&nbsp;📧 Correo: '{$datos['correo']}'<br>";
        }
        
        // Verificar si la propiedad ya existe
        $propiedad_existente = propiedadExiste($db, $id_usuario, $datos['urbanizacion'], $datos['mz'], $datos['villa']);
        
        if ($propiedad_existente) {
            echo "🏠 Propiedad existente: MZ {$datos['mz']}-{$datos['villa']} (ID: {$propiedad_existente['id']})<br>";
        } else {
            // Obtener la etapa más alta de progreso_construccion
            $etapa_mas_alta = obtenerEtapaMasAlta($db, $datos['urbanizacion'], $datos['mz'], $datos['villa']);
            
            // Insertar nueva propiedad
            $id_propiedad = insertarPropiedad($db, $datos, $id_usuario, $tipo_propiedad_default, $estado_default, $etapa_mas_alta);
            $propiedades_insertadas++;
            
            $etapa_info = $etapa_mas_alta ? " (Etapa más alta: $etapa_mas_alta)" : " (Etapa por defecto: $etapa_default)";
            echo "✅ Propiedad insertada: MZ {$datos['mz']}-{$datos['villa']} (ID: $id_propiedad)$etapa_info<br>";
        }
        
    } catch (Exception $e) {
        $errores[] = "Fila $total_registros: " . $e->getMessage();
        echo "❌ Error en fila $total_registros: " . $e->getMessage() . "<br>";
    }
}

fclose($handle);

echo "</div>";

// Resumen
echo "<hr>";
echo "<h3>📊 Resumen de Importación</h3>";
echo "<div style='background: #e8f5e8; padding: 15px;'>";
echo "<strong>📈 Estadísticas:</strong><br>";
echo "• Total de registros procesados: $total_registros<br>";
echo "• Usuarios insertados: $usuarios_insertados<br>";
echo "• Propiedades insertadas: $propiedades_insertadas<br>";
echo "• Errores encontrados: " . count($errores) . "<br>";
echo "</div>";

if (!empty($errores)) {
    echo "<h3>⚠️ Errores encontrados:</h3>";
    echo "<div style='background: #ffe8e8; padding: 10px; max-height: 200px; overflow-y: auto;'>";
    foreach ($errores as $error) {
        echo "• $error<br>";
    }
    echo "</div>";
}

echo "<h3>🎉 Importación completada</h3>";
?>
