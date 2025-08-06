<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Cargar la configuración de la base de datos
require_once '../config/db.php';

try {
    // Obtener la conexión a la base de datos
    $db = DB::getDB();
    
    // Verificar token de autenticación
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        $auth_header = $headers['Authorization'];
        if (strpos($auth_header, 'Bearer ') === 0) {
            $token = substr($auth_header, 7);
        }
    }
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token no proporcionado']);
        exit;
    }
    
    // Verificar token en la base de datos (buscar en usuario y responsable)
    $sql_verify = 'SELECT id, nombres, apellidos, rol_id, "usuario" as tipo FROM usuario WHERE token = :token AND token IS NOT NULL
                   UNION ALL
                   SELECT id, nombre as nombres, "" as apellidos, 0 as rol_id, "responsable" as tipo FROM responsable WHERE token = :token AND token IS NOT NULL';
    $stmt_verify = $db->prepare($sql_verify);
    $stmt_verify->execute([':token' => $token]);
    $authenticated_user = $stmt_verify->fetch();
    
    if (!$authenticated_user) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token inválido']);
        exit;
    }
    
    // Permitir acceso a todos los usuarios autenticados (incluyendo responsables)
    // Los responsables tienen tipo "responsable", usuarios normales tienen tipo "usuario"
    
    // Obtener datos de garantías
    $sql = 'SELECT id, nombre, tiempo_garantia_min, tiempo_garantia_max FROM tipo_ctg ORDER BY nombre';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $garantias = $stmt->fetchAll();
    
    // Procesar los datos para formatear las duraciones
    $garantias_procesadas = [];
    foreach ($garantias as $garantia) {
        $min = floatval($garantia['tiempo_garantia_min']);
        $max = floatval($garantia['tiempo_garantia_max']);
        
        // Convertir a formato legible
        $duracion = '';
        if ($min == $max) {
            // Si son iguales, mostrar solo uno
            if ($min >= 1) {
                $duracion = $min == 1 ? '1 año' : $min . ' años';
            } else {
                $meses = intval($min * 12); // Usar intval en lugar de round para evitar +1
                $duracion = $meses == 1 ? '1 mes' : $meses . ' meses';
            }
        } else {
            // Si son diferentes, mostrar rango
            $min_text = '';
            $max_text = '';
            
            if ($min >= 1) {
                $min_text = $min == 1 ? '1 año' : $min . ' años';
            } else {
                $meses_min = intval($min * 12); // Usar intval en lugar de round
                $min_text = $meses_min == 1 ? '1 mes' : $meses_min . ' meses';
            }
            
            if ($max >= 1) {
                $max_text = $max == 1 ? '1 año' : $max . ' años';
            } else {
                $meses_max = intval($max * 12); // Usar intval en lugar de round
                $max_text = $meses_max == 1 ? '1 mes' : $meses_max . ' meses';
            }
            
            $duracion = $min_text . ' a ' . $max_text;
        }
        
        $garantias_procesadas[] = [
            'id' => $garantia['id'],
            'categoria' => $garantia['nombre'],
            'elemento' => $garantia['nombre'], // Por ahora usamos el mismo nombre
            'duracion' => $duracion,
            'responsable' => 'Thalia Victoria Constructora'
        ];
    }
    
    echo json_encode(['ok' => true, 'garantias' => $garantias_procesadas]);
    
} catch (Exception $e) {
    error_log("Garantías API - Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al obtener garantías: ' . $e->getMessage()]);
}
?> 