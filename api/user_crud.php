<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$db = DB::getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {               // alta
        $d = json_decode(file_get_contents('php://input'), true);
        $sql = 'INSERT INTO usuario (rol_id, nombres, apellidos, correo, contrasena_hash)
                VALUES (:rol, :nom, :ape, :cor, :hash)';
        $db->prepare($sql)->execute([
          ':rol'  => $d['rol_id'],
          ':nom'  => $d['nombres'],
          ':ape'  => $d['apellidos'],
          ':cor'  => $d['correo'],
          ':hash' => password_hash($d['contrasena'], PASSWORD_DEFAULT)
        ]);
        exit(json_encode(['ok'=>true]));

    } elseif ($method === 'PUT') {          // edición
        $d = json_decode(file_get_contents('php://input'), true);
        $set = 'rol_id=:rol, nombres=:nom, apellidos=:ape, correo=:cor';
        $params = [
          ':rol' => $d['rol_id'], ':nom'=>$d['nombres'],
          ':ape'=>$d['apellidos'], ':cor'=>$d['correo'], ':id'=>$d['id']
        ];
        if (!empty($d['contrasena'])) {     // cambiar contraseña opcional
            $set .= ', contrasena_hash=:hash';
            $params[':hash'] = password_hash($d['contrasena'], PASSWORD_DEFAULT);
        }
        $sql = "UPDATE usuario SET $set WHERE id=:id";
        $db->prepare($sql)->execute($params);
        exit(json_encode(['ok'=>true]));

    } elseif ($method === 'DELETE') {       // borrado
        $id = $_GET['id'] ?? 0;
        $db->prepare('DELETE FROM usuario WHERE id = ?')->execute([$id]);
        exit(json_encode(['ok'=>true]));
    }

    http_response_code(405);
    echo json_encode(['ok'=>false,'mensaje'=>'Método no permitido']);

} catch (Throwable $e) {
    error_log('user_crud: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'mensaje'=>'Error interno']);
}
