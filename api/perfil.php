<?php
/**
 *  api/perfil.php  – Devuelve los datos del perfil del usuario autenticado.
 *
 *  GET /api/perfil.php
 *  Requiere autenticación mediante token Bearer.
 *
 *  Respuesta:
 *      200 OK  { "ok": true, "usuario": { id, nombres, cedula, telefono, email, url_foto_perfil } }
 *      401 Unauthorized  { "ok": false, "mensaje": "Token inválido o expirado" }
 *      500 Internal Server Error  { "ok": false, "mensaje": "Error interno del servidor" }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = DB::getDB();
    
    // --- Lógica de Autenticación --- //
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = null;
    
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
    }
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'mensaje' => 'Token no proporcionado']);
        exit;
    }
    
    $authenticated_user = null;
    $is_responsable = false;
    
    // Buscar en tabla 'usuario'
    $sql_user = 'SELECT id, nombres, apellidos, cedula, telefono, correo, url_foto_perfil, rol_id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    if ($authenticated_user) {
        $is_responsable = false;
    } else {
        // Buscar en tabla 'responsable' (solo tiene: id, nombre, correo, url_foto_perfil, area)
        $sql_resp = 'SELECT id, nombre, correo, url_foto_perfil, area FROM responsable WHERE token = :token LIMIT 1';
        $stmt_resp = $db->prepare($sql_resp);
        $stmt_resp->execute([':token' => $token]);
        $authenticated_user = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($authenticated_user) {
            $is_responsable = true;
        }
    }
    
    if (!$authenticated_user) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'mensaje' => 'Token inválido o expirado']);
        exit;
    }
    
    // Preparar respuesta según el tipo de usuario
    if ($is_responsable) {
        // Usuario es responsable (solo tiene: nombre, correo, area, url_foto_perfil)
        $usuario = [
            'id' => $authenticated_user['id'],
            'nombres' => $authenticated_user['nombre'],  // Para responsables, el campo se llama 'nombre'
            'cedula' => 'No disponible',  // Los responsables no tienen cédula en la BD
            'telefono' => 'No disponible',  // Los responsables no tienen teléfono en la BD
            'email' => $authenticated_user['correo'] ?? 'No disponible',
            'url_foto_perfil' => $authenticated_user['url_foto_perfil'] ?? null,
            'area' => $authenticated_user['area'] ?? null,
            'tipo' => 'responsable'
        ];
    } else {
        // Usuario regular
        $usuario = [
            'id' => $authenticated_user['id'],
            'nombres' => $authenticated_user['nombres'] ?? 'No disponible',
            'apellidos' => $authenticated_user['apellidos'] ?? 'No disponible',
            'cedula' => $authenticated_user['cedula'] ?? 'No disponible',
            'telefono' => $authenticated_user['telefono'] ?? 'No disponible',
            'email' => $authenticated_user['correo'] ?? 'No disponible',  // Campo se llama 'correo'
            'url_foto_perfil' => $authenticated_user['url_foto_perfil'] ?? null,
            'rol_id' => $authenticated_user['rol_id'],
            'tipo' => 'usuario'
        ];
        
        // Combinar nombres y apellidos para mostrar completo
        if ($usuario['nombres'] !== 'No disponible' && $usuario['apellidos'] !== 'No disponible') {
            $usuario['nombre_completo'] = trim($usuario['nombres'] . ' ' . $usuario['apellidos']);
        } else {
            $usuario['nombre_completo'] = $usuario['nombres'];
        }
    }
    
    echo json_encode(['ok' => true, 'usuario' => $usuario]);
    
} catch (Exception $e) {
    error_log("Perfil API - Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
