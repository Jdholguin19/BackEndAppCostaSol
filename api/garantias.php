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
    $db = DB::getDB();
    
    // 1. VERIFICAR TOKEN
    $headers = getallheaders();
    $token = null;
    $auth_id = null;
    $is_responsable = false;

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
    
    $stmt_user = $db->prepare('SELECT id FROM usuario WHERE token = :token AND token IS NOT NULL');
    $stmt_user->execute([':token' => $token]);
    $user = $stmt_user->fetch();

    if ($user) {
        $auth_id = $user['id'];
    } else {
        $stmt_resp = $db->prepare('SELECT id FROM responsable WHERE token = :token AND token IS NOT NULL');
        $stmt_resp->execute([':token' => $token]);
        $responsable = $stmt_resp->fetch();
        if ($responsable) {
            $auth_id = $responsable['id'];
            $is_responsable = true;
        }
    }
    
    if (!$auth_id) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token inválido']);
        exit;
    }

    // 2. OBTENER FECHA DE ENTREGA (si es un usuario normal)
    $fecha_entrega_str = null;
    if (!$is_responsable) {
        $sql_propiedad = "SELECT fecha_entrega FROM propiedad WHERE id_usuario = :id_usuario AND fecha_entrega IS NOT NULL ORDER BY fecha_entrega DESC LIMIT 1";
        $stmt_propiedad = $db->prepare($sql_propiedad);
        $stmt_propiedad->execute([':id_usuario' => $auth_id]);
        $propiedad = $stmt_propiedad->fetch(PDO::FETCH_ASSOC);
        if ($propiedad) {
            $fecha_entrega_str = $propiedad['fecha_entrega'];
        }
    }

    // 3. OBTENER LISTA DE GARANTÍAS
    $sql = 'SELECT id, nombre, tiempo_garantia_min, tiempo_garantia_max FROM tipo_ctg WHERE tiempo_garantia_max IS NOT NULL ORDER BY nombre';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $garantias = $stmt->fetchAll();
    
    // 4. PROCESAR GARANTÍAS Y CALCULAR VIGENCIA
    $garantias_procesadas = [];
    foreach ($garantias as $garantia) {
        // Formatear duración
        $max = floatval($garantia['tiempo_garantia_max']);
        $duracion_texto = '';
        if ($max >= 1) {
            $duracion_texto = ($max == 1) ? '1 año' : $max . ' años';
        } else {
            $meses = intval($max * 12);
            $duracion_texto = ($meses == 1) ? '1 mes' : $meses . ' meses';
        }

        // Calcular vigencia y estado
        $vigencia_texto = 'No aplica';
        $activa = false; // Por defecto, no está activa si no hay fecha de entrega

        if ($fecha_entrega_str) {
            try {
                $fecha_vencimiento = new DateTime($fecha_entrega_str);
                
                // Sumar el intervalo de la garantía. Nota: Se asumen valores fijos como 1.0 para 1 año, 0.5 para 6 meses, etc.
                if ($max >= 1) {
                    $years = floor($max);
                    $months = round(($max - $years) * 12);
                    if ($years > 0) $fecha_vencimiento->add(new DateInterval("P{$years}Y"));
                    if ($months > 0) $fecha_vencimiento->add(new DateInterval("P{$months}M"));
                } else {
                    $months = round($max * 12);
                    if ($months > 0) $fecha_vencimiento->add(new DateInterval("P{$months}M"));
                }
                
                $vigencia_texto = $fecha_vencimiento->format('d/m/Y');
                
                // Comparar con la fecha actual para determinar si está activa
                $hoy = new DateTime();
                // Se considera activa si la fecha de vencimiento es hoy o en el futuro.
                $activa = ($fecha_vencimiento >= $hoy->setTime(0, 0, 0));

            } catch (Exception $e) {
                $vigencia_texto = 'Error';
                $activa = false;
            }
        }
        
        $garantias_procesadas[] = [
            'id' => $garantia['id'],
            'categoria' => $garantia['nombre'],
            'elemento' => $garantia['nombre'],
            'duracion' => $duracion_texto,
            'vigencia' => $vigencia_texto,
            'activa' => $activa, // Nuevo campo para indicar si la garantía está activa
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