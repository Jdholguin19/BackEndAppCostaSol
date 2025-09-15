<?php

session_start();

ini_set('error_log', __DIR__ . '/../config/error_log'); // Added this line

/**
 *  api/login.php
 *  POST  { "correo": "...", "contrasena": "..." }
 *  OK    { ok:true, token:"...", user:{ id, nombre, correo, url_foto_perfil, rol, is_responsable } }
 */

ini_set('display_errors', 'Off');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers/audit_helper.php'; // Incluir el helper de auditoría

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok'=>false,'mensaje'=>'Método no permitido']));
}

/* ---------- 1. Entrada ---------- */
$input      = json_decode(file_get_contents('php://input'), true);
$correo     = $input['correo']     ?? null;
$contrasena = $input['contrasena'] ?? null;

if (!$correo || !$contrasena) {
    http_response_code(422);
    exit(json_encode(['ok'=>false,'mensaje'=>'Correo y contraseña requeridos']));
}

try {
    $db  = DB::getDB();
    $user_id = null; // Usado para el registro de login, puede ser ID de usuario o responsable
    $login_status = 'FALLIDO';
    $authenticated_user_info = null; // Para almacenar la info del usuario/responsable logueado

    /* ---------- 2. Consulta en tabla 'usuario' ---------- */
    $sql = 'SELECT id, nombres, apellidos, correo, url_foto_perfil,
                   rol_id, contrasena_hash
              FROM usuario
             WHERE correo = :correo
             LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->execute([':correo'=>$correo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contrasena, $user['contrasena_hash'])) {
        $user_id = $user['id']; // ID del usuario para registro_login
        $login_status = 'EXITO';
        $authenticated_user_info = $user; // Guardar info del usuario
        $authenticated_user_info['is_responsable'] = false;

        /* ---------- 3. Generar y guardar token para usuario ---------- */
        $token = base64_encode(random_bytes(24));
        $update_sql = 'UPDATE usuario SET token = :token WHERE id = :id';
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->execute([':token' => $token, ':id' => $user['id']]);

        /* ---------- 4. Respuesta para usuario ---------- */
        $_SESSION['cs_usuario'] = json_encode($authenticated_user_info);
        echo json_encode(['ok'=>true,'token'=>$token,'user'=>$authenticated_user_info]);

        log_audit_action($db, 'LOGIN_SUCCESS', $user['id'], 'usuario'); // Log de auditoría

    } else {
       /* ---------- 5. Consulta en tabla 'responsable' ---------- */
        $sql = 'SELECT id, nombre, correo, url_foto_perfil, area, contrasena_hash
                  FROM responsable
                 WHERE correo = :correo
                 LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->execute([':correo'=>$correo]);
        $responsable = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($responsable && isset($responsable['contrasena_hash']) && password_verify($contrasena, $responsable['contrasena_hash'])) {
            $user_id = $responsable['id']; // ID del responsable para registro_login
            $login_status = 'EXITO';
            $authenticated_user_info = $responsable; // Guardar info del responsable
            $authenticated_user_info['is_responsable'] = true;
            $authenticated_user_info['rol'] = 'responsable'; // Consistency

            /* ---------- 6. Generar y guardar token para responsable ---------- */
            $token = base64_encode(random_bytes(24));
            $update_sql = 'UPDATE responsable SET token = :token WHERE id = :id';
            $update_stmt = $db->prepare($update_sql);
            $update_stmt->execute([':token' => $token, ':id' => $responsable['id']]);

            /* ---------- 7. Respuesta para responsable ---------- */
            $_SESSION['cs_usuario'] = json_encode($authenticated_user_info);
            echo json_encode(['ok'=>true,'token'=>$token,'user'=>$authenticated_user_info]);

            log_audit_action($db, 'LOGIN_SUCCESS', $responsable['id'], 'responsable'); // Log de auditoría

        } else {
            /* ---------- 8. Credenciales incorrectas ---------- */
            http_response_code(401);
            echo json_encode(['ok'=>false,'mensaje'=>'Credenciales incorrectas']);
            log_audit_action($db, 'LOGIN_FAILURE', null, 'sistema', null, null, ['correo_intentado' => $correo]); // Log de auditoría
             // No hay authenticated_user_info en caso de credenciales incorrectas
        }
    }

   /* ---------- 9. Registrar intento de login ---------- */
   // Solo registramos si hubo un intento de login (ya sea exitoso o fallido después de buscar)
    if ($correo) { // Si se proporcionó correo, hubo un intento
        $sql_registro = 'INSERT INTO registro_login (id_usuario, id_responsable, estado_login, ip, user_agent)
                           VALUES (:id_usuario, :id_responsable, :estado_login, :ip, :user_agent)';
        $stmt_registro = $db->prepare($sql_registro);

        $id_usuario_registro = null;
        $id_responsable_registro = null;

        // Asignar el ID correcto para el registro si el login fue exitoso
        if ($login_status === 'EXITO') {
            if ($authenticated_user_info['is_responsable']) {
                 $id_responsable_registro = $user_id; // $user_id ya contiene el ID del responsable
            } else {
                 $id_usuario_registro = $user_id; // $user_id ya contiene el ID del usuario
            }
        }

        $stmt_registro->execute([
            ':id_usuario'     => $id_usuario_registro,
            ':id_responsable' => $id_responsable_registro,
            ':estado_login'   => $login_status,
            ':ip'             => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    // Solo salimos si se envió una respuesta JSON exitosa o de credenciales incorrectas
    // Si hubo un error interno antes, ya se habría salido con un 500.
    if ($login_status === 'EXITO' || (isset($authenticated_user_info) && !$authenticated_user_info)){
         exit;
    }

} catch (Throwable $e) {
    error_log('Login error: '.$e->getMessage()); // Registro de error
    // Solo si no se ha enviado una respuesta antes, enviar 500
    if (!headers_sent()) {
        http_response_code(500);
        echo json_encode(['ok'=>false,'mensaje'=>'Error interno']);
    }
    exit;
}
