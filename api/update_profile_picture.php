<?php
/**
 *  api/update_profile_picture.php  – Actualiza la foto de perfil del usuario autenticado.
 *
 *  POST /api/update_profile_picture.php
 *  Requiere autenticación mediante token Bearer.
 *  Body: FormData con archivo 'profile_picture'
 *
 *  Respuesta:
 *      200 OK  { "ok": true, "url_foto_perfil": "nueva_url", "mensaje": "Foto actualizada" }
 *      400 Bad Request  { "ok": false, "mensaje": "Error en la solicitud" }
 *      401 Unauthorized  { "ok": false, "mensaje": "Token inválido o expirado" }
 *      500 Internal Server Error  { "ok": false, "mensaje": "Error interno del servidor" }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = DB::getDB();
    
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
        exit;
    }
    
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
    $sql_user = 'SELECT id, nombres, apellidos FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    if ($authenticated_user) {
        $is_responsable = false;
    } else {
        // Buscar en tabla 'responsable'
        $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
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
    
    // --- Validar archivo subido --- //
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'No se recibió el archivo o hubo un error en la subida']);
        exit;
    }
    
    $file = $_FILES['profile_picture'];
    
    // Validar tipo de archivo
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF']);
        exit;
    }
    
    // Validar tamaño (máximo 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'El archivo es demasiado grande. Máximo 5MB permitido']);
        exit;
    }
    
    // --- Procesar y guardar imagen --- //
    
    // Crear directorio de imágenes si no existe
    $upload_dir = __DIR__ . '/../ImagenesPerfil/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Usar permisos 0777 como en ctg_insert_form.php
    }
    
    // Verificar que el directorio sea escribible
    if (!is_writable($upload_dir)) {
        error_log('Directorio de subida no es escribible: ' . $upload_dir);
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error de permisos en el servidor']);
        exit;
    }
    
    // Generar nombre único para el archivo usando uniqid() como en ctg_insert_form.php
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '-profile-' . $authenticated_user['id'] . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Mover archivo subido
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        error_log('Error al mover archivo subido para foto de perfil.');
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error al guardar el archivo']);
        exit;
    }
    
    // URL pública de la imagen usando dominio absoluto como en ctg_insert_form.php
    $url_foto_perfil = "https://app.costasol.com.ec/ImagenesPerfil/$new_filename";
    
    // --- Actualizar base de datos --- //
    
    if ($is_responsable) {
        // Actualizar tabla responsable
        $sql_update = 'UPDATE responsable SET url_foto_perfil = :url_foto_perfil WHERE id = :id';
        $stmt_update = $db->prepare($sql_update);
        $stmt_update->execute([
            ':url_foto_perfil' => $url_foto_perfil,
            ':id' => $authenticated_user['id']
        ]);
    } else {
        // Actualizar tabla usuario
        $sql_update = 'UPDATE usuario SET url_foto_perfil = :url_foto_perfil WHERE id = :id';
        $stmt_update = $db->prepare($sql_update);
        $stmt_update->execute([
            ':url_foto_perfil' => $url_foto_perfil,
            ':id' => $authenticated_user['id']
        ]);
    }
    
    // Verificar que se actualizó correctamente
    if ($stmt_update->rowCount() === 0) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error al actualizar la base de datos']);
        exit;
    }
    
    // Respuesta exitosa
    echo json_encode([
        'ok' => true,
        'url_foto_perfil' => $url_foto_perfil,
        'mensaje' => 'Foto de perfil actualizada correctamente'
    ]);
    
} catch (Exception $e) {
    error_log("Update Profile Picture API - Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
