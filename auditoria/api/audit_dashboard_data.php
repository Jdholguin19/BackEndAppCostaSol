<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../api/helpers/audit_helper.php';

$db = DB::getDB();

// Función para validar autenticación de responsable
function validateResponsableAuth($db) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return ['valid' => false, 'message' => 'Token de autorización requerido'];
    }
    
    $token = $matches[1];
    
    try {
        $stmt = $db->prepare('SELECT id, nombre FROM responsable WHERE token = ? AND estado = 1');
        $stmt->execute([$token]);
        $responsable = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$responsable) {
            return ['valid' => false, 'message' => 'Token inválido o responsable inactivo'];
        }
        
        return ['valid' => true, 'responsable' => $responsable];
    } catch (Exception $e) {
        return ['valid' => false, 'message' => 'Error de autenticación: ' . $e->getMessage()];
    }
}

// Función para obtener datos de módulos
function getModulesData($db) {
    $modules = [
        'autenticacion' => [
            'name' => 'Autenticación',
            'description' => 'Inicios de sesión y cierres de sesión',
            'actions' => ['LOGIN_SUCCESS', 'LOGIN_FAILURE', 'LOGOUT']
        ],
        'usuario' => [
            'name' => 'Usuarios',
            'description' => 'Gestión de usuarios del sistema',
            'actions' => ['CREATE_USER', 'UPDATE_USER', 'DELETE_USER']
        ],
        'cita' => [
            'name' => 'Citas',
            'description' => 'Agendamiento y gestión de citas',
            'actions' => ['CREATE_CITA', 'CANCEL_CITA', 'UPDATE_CITA_STATUS', 'DELETE_CITA']
        ],
        'ctg' => [
            'name' => 'CTG',
            'description' => 'Gestión de contingencias',
            'actions' => ['CREATE_CTG', 'UPDATE_CTG_STATUS', 'ADD_CTG_RESPONSE', 'UPDATE_CTG_OBSERVATION']
        ],
        'pqr' => [
            'name' => 'PQR',
            'description' => 'Peticiones, quejas y recomendaciones',
            'actions' => ['CREATE_PQR', 'UPDATE_PQR_STATUS', 'ADD_PQR_RESPONSE', 'UPDATE_PQR_OBSERVATION']
        ],
        'acabados' => [
            'name' => 'Acabados',
            'description' => 'Selección de acabados de propiedades',
            'actions' => ['SAVE_ACABADOS']
        ],
        'perfil' => [
            'name' => 'Perfil',
            'description' => 'Gestión de perfil de usuario',
            'actions' => ['UPDATE_PROFILE_PICTURE']
        ],
        'notificaciones' => [
            'name' => 'Notificaciones',
            'description' => 'Gestión de notificaciones push',
            'actions' => ['UPDATE_ONESIGNAL_PLAYER_ID']
        ],
        'acceso_modulo' => [
            'name' => 'Acceso a Módulos',
            'description' => 'Acceso a diferentes módulos del sistema',
            'actions' => ['ACCESS_MODULE']
        ]
    ];
    
    $result = [];
    
    foreach ($modules as $resource => $module) {
        // Construir la consulta con parámetros preparados correctamente
        $placeholders = str_repeat('?,', count($module['actions']) - 1) . '?';
        $sql = "SELECT COUNT(*) as count FROM audit_log 
                WHERE target_resource = ? OR action IN ($placeholders)";
        
        $params = array_merge([$resource], $module['actions']);
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $result[] = [
            'resource' => $resource,
            'name' => $module['name'],
            'description' => $module['description'],
            'count' => $count
        ];
    }
    
    return $result;
}

// Función para obtener auditorías recientes
function getRecentAudits($db, $limit = 10) {
    $sql = "SELECT al.*, 
                   CASE 
                       WHEN al.user_type = 'usuario' THEN CONCAT(u.nombres, ' ', u.apellidos)
                       WHEN al.user_type = 'responsable' THEN r.nombre
                       ELSE 'Sistema'
                   END as user_name,
                   CASE 
                       WHEN al.user_type = 'usuario' THEN CONCAT(u.nombres, ' ', u.apellidos)
                       WHEN al.user_type = 'responsable' THEN r.nombre
                       ELSE 'Sistema'
                   END as user_display_name
            FROM audit_log al
            LEFT JOIN usuario u ON al.user_type = 'usuario' AND al.user_id = u.id
            LEFT JOIN responsable r ON al.user_type = 'responsable' AND al.user_id = r.id
            ORDER BY al.timestamp DESC
            LIMIT " . intval($limit);
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $audits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar detalles para mejorar la visualización
    foreach ($audits as &$audit) {
        $audit['formatted_details'] = formatAuditDetails($audit['details'], $db);
    }
    
    return $audits;
}

// Función para obtener auditorías de un módulo específico
function getModuleAudits($db, $resource, $filters = [], $offset = 0, $limit = 20) {
    // Construir la consulta base
    $sql = "SELECT al.*, 
                   CASE 
                       WHEN al.user_type = 'usuario' THEN CONCAT(u.nombres, ' ', u.apellidos)
                       WHEN al.user_type = 'responsable' THEN r.nombre
                       ELSE 'Sistema'
                   END as user_name,
                   CASE 
                       WHEN al.user_type = 'usuario' THEN CONCAT(u.nombres, ' ', u.apellidos)
                       WHEN al.user_type = 'responsable' THEN r.nombre
                       ELSE 'Sistema'
                   END as user_display_name
            FROM audit_log al
            LEFT JOIN usuario u ON al.user_type = 'usuario' AND al.user_id = u.id
            LEFT JOIN responsable r ON al.user_type = 'responsable' AND al.user_id = r.id
            WHERE 1=1";
    
    $params = [];
    
    // Filtro por recurso
    if ($resource) {
        $sql .= " AND (al.target_resource = ?";
        $params[] = $resource;
        
        // También incluir acciones específicas del módulo
        $moduleActions = getModuleActions($resource);
        if (!empty($moduleActions)) {
            $placeholders = str_repeat('?,', count($moduleActions) - 1) . '?';
            $sql .= " OR al.action IN ($placeholders)";
            $params = array_merge($params, $moduleActions);
        }
        $sql .= ")";
    }
    
    // Aplicar filtros adicionales
    if (!empty($filters['date_from'])) {
        $sql .= " AND al.timestamp >= ?";
        $params[] = $filters['date_from'] . ' 00:00:00';
    }
    
    if (!empty($filters['date_to'])) {
        $sql .= " AND al.timestamp <= ?";
        $params[] = $filters['date_to'] . ' 23:59:59';
    }
    
    if (!empty($filters['user_type'])) {
        $sql .= " AND al.user_type = ?";
        $params[] = $filters['user_type'];
    }
    
    if (!empty($filters['action'])) {
        $sql .= " AND al.action LIKE ?";
        $params[] = '%' . $filters['action'] . '%';
    }
    
    if (!empty($filters['target_id'])) {
        $sql .= " AND al.target_id = ?";
        $params[] = $filters['target_id'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (al.details LIKE ? OR al.action LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Contar total de resultados (usar la misma lógica de filtros)
    $countSql = "SELECT COUNT(*) as total FROM audit_log al WHERE 1=1";
    $countParams = [];
    
    if ($resource) {
        $countSql .= " AND (al.target_resource = ?";
        $countParams[] = $resource;
        
        $moduleActions = getModuleActions($resource);
        if (!empty($moduleActions)) {
            $placeholders = str_repeat('?,', count($moduleActions) - 1) . '?';
            $countSql .= " OR al.action IN ($placeholders)";
            $countParams = array_merge($countParams, $moduleActions);
        }
        $countSql .= ")";
    }
    
    // Aplicar los mismos filtros al count
    if (!empty($filters['date_from'])) {
        $countSql .= " AND al.timestamp >= ?";
        $countParams[] = $filters['date_from'] . ' 00:00:00';
    }
    
    if (!empty($filters['date_to'])) {
        $countSql .= " AND al.timestamp <= ?";
        $countParams[] = $filters['date_to'] . ' 23:59:59';
    }
    
    if (!empty($filters['user_type'])) {
        $countSql .= " AND al.user_type = ?";
        $countParams[] = $filters['user_type'];
    }
    
    if (!empty($filters['action'])) {
        $countSql .= " AND al.action LIKE ?";
        $countParams[] = '%' . $filters['action'] . '%';
    }
    
    if (!empty($filters['target_id'])) {
        $countSql .= " AND al.target_id = ?";
        $countParams[] = $filters['target_id'];
    }
    
    if (!empty($filters['search'])) {
        $countSql .= " AND (al.details LIKE ? OR al.action LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
    }
    
    // Ejecutar count
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($countParams);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ordenar y paginar
    $sql .= " ORDER BY al.timestamp DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $audits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar detalles para mejorar la visualización
    foreach ($audits as &$audit) {
        $audit['formatted_details'] = formatAuditDetails($audit['details'], $db);
    }
    
    return ['audits' => $audits, 'total' => $total];
}

// Función para formatear los detalles de auditoría
function formatAuditDetails($details, $db = null) {
    if (!$details) return '-';
    
    try {
        $parsed = is_string($details) ? json_decode($details, true) : $details;
        
        if (!$parsed || !is_array($parsed)) {
            return $details;
        }
        
        // Casos especiales para mejorar la visualización
        if (isset($parsed['menu_name'])) {
            return $parsed['menu_name'];
        }
        
        if (isset($parsed['kit_name'])) {
            return $parsed['kit_name'];
        }
        
        // Si tenemos kit_id (para acabados), obtener información completa
        if (isset($parsed['kit_id']) && $db) {
            try {
                // Obtener nombre del kit
                $stmt = $db->prepare("SELECT nombre FROM acabado_kit WHERE id = ?");
                $stmt->execute([$parsed['kit_id']]);
                $kit_name = $stmt->fetchColumn();
                
                if ($kit_name) {
                    $result = $kit_name;
                    
                    // Agregar color si existe
                    if (isset($parsed['color_nombre'])) {
                        $result .= " " . $parsed['color_nombre'];
                    }
                    
                    // Agregar paquetes adicionales si existen
                    if (isset($parsed['paquetes_adicionales_ids']) && is_array($parsed['paquetes_adicionales_ids']) && !empty($parsed['paquetes_adicionales_ids'])) {
                        try {
                            $placeholders = implode(',', array_fill(0, count($parsed['paquetes_adicionales_ids']), '?'));
                            $stmt_packages = $db->prepare("SELECT nombre FROM paquetes_adicionales WHERE id IN ($placeholders)");
                            $stmt_packages->execute($parsed['paquetes_adicionales_ids']);
                            $package_names = $stmt_packages->fetchAll(PDO::FETCH_COLUMN);
                            
                            if (!empty($package_names)) {
                                $result .= " + " . implode(", ", $package_names);
                            }
                        } catch (Exception $e) {
                            // Si hay error obteniendo paquetes, continuar sin ellos
                        }
                    }
                    
                    return $result;
                }
            } catch (Exception $e) {
                // Si hay error, continuar con el procesamiento normal
            }
        }
        
        // Si tenemos proposito_id (para citas), obtener información completa
        if (isset($parsed['proposito_id'])) {
            $proposito_name = null;
            
            // Intentar obtener el nombre desde la base de datos
            if ($db) {
                try {
                    $stmt = $db->prepare("SELECT proposito FROM proposito_agendamiento WHERE id = ?");
                    $stmt->execute([$parsed['proposito_id']]);
                    $proposito_name = $stmt->fetchColumn();
                } catch (Exception $e) {
                    // Si hay error, usar fallback hardcodeado
                }
            }
            
            // Fallback hardcodeado si no se pudo obtener de la DB
            if (!$proposito_name) {
                $proposito_names = [
                    1 => 'Recorrido de Obra',
                    2 => 'Entrega de Llaves',
                    3 => 'Inspección Técnica',
                    4 => 'Reunión Comercial',
                    5 => 'Visita Técnica',
                    6 => 'Entrega de Documentos'
                ];
                $proposito_name = $proposito_names[$parsed['proposito_id']] ?? 'Propósito ID ' . $parsed['proposito_id'];
            }
            
            $result = $proposito_name;
            
            // Agregar hora si existe
            if (isset($parsed['hora_reunion'])) {
                $result .= ", Hora " . $parsed['hora_reunion'];
            }
            
            // Agregar ID de propiedad si existe
            if (isset($parsed['id_propiedad'])) {
                $result .= ", id_propiedad " . $parsed['id_propiedad'];
            }
            
            // Agregar fecha de reunión si existe
            if (isset($parsed['fecha_reunion'])) {
                $result .= ", fecha de reunión " . $parsed['fecha_reunion'];
            }
            
            // Agregar nombre del responsable si existe
            if (isset($parsed['responsable_id']) && $db) {
                try {
                    $stmt_resp = $db->prepare("SELECT nombre FROM responsable WHERE id = ?");
                    $stmt_resp->execute([$parsed['responsable_id']]);
                    $responsable_name = $stmt_resp->fetchColumn();
                    if ($responsable_name) {
                        $result .= ", (" . $responsable_name . ")";
                    }
                } catch (Exception $e) {
                    // Si hay error obteniendo responsable, continuar sin él
                }
            }
            
            // Agregar duración si existe
            if (isset($parsed['duracion_minutos'])) {
                $result .= ", Tiempo " . $parsed['duracion_minutos'] . " minutos";
            }
            
            return $result;
        }
        
        // Si tenemos deleted_cita_data (para citas eliminadas), obtener información del propósito
        if (isset($parsed['deleted_cita_data']) && is_array($parsed['deleted_cita_data']) && isset($parsed['deleted_cita_data']['proposito_id'])) {
            $proposito_name = null;
            
            // Intentar obtener el nombre desde la base de datos
            if ($db) {
                try {
                    $stmt = $db->prepare("SELECT proposito FROM proposito_agendamiento WHERE id = ?");
                    $stmt->execute([$parsed['deleted_cita_data']['proposito_id']]);
                    $proposito_name = $stmt->fetchColumn();
                } catch (Exception $e) {
                    // Si hay error, usar fallback hardcodeado
                }
            }
            
            // Fallback hardcodeado si no se pudo obtener de la DB
            if (!$proposito_name) {
                $proposito_names = [
                    1 => 'Recorrido de Obra',
                    2 => 'Entrega de Llaves',
                    3 => 'Inspección Técnica',
                    4 => 'Reunión Comercial',
                    5 => 'Visita Técnica',
                    6 => 'Entrega de Documentos'
                ];
                $proposito_name = $proposito_names[$parsed['deleted_cita_data']['proposito_id']] ?? 'Propósito ID ' . $parsed['deleted_cita_data']['proposito_id'];
            }
            
            $result = $proposito_name . " (Eliminada)";
            
            // Agregar hora si existe
            if (isset($parsed['deleted_cita_data']['hora_reunion'])) {
                $result .= ", Hora " . $parsed['deleted_cita_data']['hora_reunion'];
            }
            
            // Agregar fecha de reunión si existe
            if (isset($parsed['deleted_cita_data']['fecha_reunion'])) {
                $result .= ", fecha de reunión " . $parsed['deleted_cita_data']['fecha_reunion'];
            }
            
            // Agregar nombre del responsable si existe
            if (isset($parsed['deleted_cita_data']['responsable_id']) && $db) {
                try {
                    $stmt_resp = $db->prepare("SELECT nombre FROM responsable WHERE id = ?");
                    $stmt_resp->execute([$parsed['deleted_cita_data']['responsable_id']]);
                    $responsable_name = $stmt_resp->fetchColumn();
                    if ($responsable_name) {
                        $result .= ", (" . $responsable_name . ")";
                    }
                } catch (Exception $e) {
                    // Si hay error obteniendo responsable, continuar sin él
                }
            }
            
            // Agregar duración si existe
            if (isset($parsed['deleted_cita_data']['duracion_minutos'])) {
                $result .= ", Tiempo " . $parsed['deleted_cita_data']['duracion_minutos'] . " minutos";
            }
            
            return $result;
        }
        
        // Si tenemos reason (para citas canceladas), mostrar el motivo
        if (isset($parsed['reason'])) {
            return $parsed['reason'];
        }
        
        if (isset($parsed['responsable_name'])) {
            return 'Responsable: ' . $parsed['responsable_name'];
        }
        
        if (isset($parsed['dashboard'])) {
            return 'Dashboard: ' . $parsed['dashboard'];
        }
        
        // Para otros casos, mostrar los valores más importantes
        $importantKeys = ['old_status', 'new_status', 'old_value', 'new_value', 'property_name', 'kit_name', 'color_name'];
        $formatted = [];
        
        foreach ($parsed as $key => $value) {
            if (in_array($key, $importantKeys)) {
                $formatted[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
            }
        }
        
        return empty($formatted) ? json_encode($parsed) : implode(', ', $formatted);
        
    } catch (Exception $e) {
        return $details;
    }
}

// Función para obtener acciones específicas de un módulo
function getModuleActions($resource) {
    $moduleActions = [
        'autenticacion' => ['LOGIN_SUCCESS', 'LOGIN_FAILURE', 'LOGOUT'],
        'usuario' => ['CREATE_USER', 'UPDATE_USER', 'DELETE_USER'],
        'cita' => ['CREATE_CITA', 'CANCEL_CITA', 'UPDATE_CITA_STATUS', 'DELETE_CITA'],
        'ctg' => ['CREATE_CTG', 'UPDATE_CTG_STATUS', 'ADD_CTG_RESPONSE', 'UPDATE_CTG_OBSERVATION'],
        'pqr' => ['CREATE_PQR', 'UPDATE_PQR_STATUS', 'ADD_PQR_RESPONSE', 'UPDATE_PQR_OBSERVATION'],
        'acabados' => ['SAVE_ACABADOS'],
        'perfil' => ['UPDATE_PROFILE_PICTURE'],
        'notificaciones' => ['UPDATE_ONESIGNAL_PLAYER_ID'],
        'acceso_modulo' => ['ACCESS_MODULE']
    ];
    
    return $moduleActions[$resource] ?? [];
}

// Procesar la solicitud
try {
    // Validar autenticación
    $auth = validateResponsableAuth($db);
    if (!$auth['valid']) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'mensaje' => $auth['message']]);
        exit;
    }
    
    // Obtener datos de la solicitud
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    
    switch ($action) {
        case 'get_modules_data':
            $modules = getModulesData($db);
            echo json_encode([
                'ok' => true,
                'modules' => $modules
            ]);
            break;
            
        case 'get_recent_audits':
            $audits = getRecentAudits($db, 10);
            echo json_encode([
                'ok' => true,
                'audits' => $audits
            ]);
            break;
            
        case 'get_module_audits':
            $resource = $input['resource'] ?? '';
            $filters = [
                'date_from' => $input['date_from'] ?? '',
                'date_to' => $input['date_to'] ?? '',
                'user_type' => $input['user_type'] ?? '',
                'action' => $input['action_filter'] ?? '', // Usar action_filter en lugar de action
                'target_id' => $input['target_id'] ?? '',
                'search' => $input['search'] ?? ''
            ];
            $offset = intval($input['offset'] ?? 0);
            $limit = intval($input['limit'] ?? 20);
            
            $result = getModuleAudits($db, $resource, $filters, $offset, $limit);
            echo json_encode([
                'ok' => true,
                'audits' => $result['audits'],
                'total' => $result['total']
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'ok' => false, 
                'mensaje' => 'Acción no válida: ' . $action,
                'received_action' => $action,
                'available_actions' => ['get_modules_data', 'get_recent_audits', 'get_module_audits']
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>
