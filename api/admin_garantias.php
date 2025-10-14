<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Cargar configuración de base de datos
require_once '../config/db.php';

try {
    $db = DB::getDB();

    // Verificar autenticación de responsable
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (!$authHeader || !preg_match('/Bearer\s(.+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token de autorización requerido']);
        exit;
    }

    $token = $matches[1];

    // Verificar que sea responsable
    $stmt = $db->prepare('SELECT id, nombre FROM responsable WHERE token = ? AND estado = 1');
    $stmt->execute([$token]);
    $responsable = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$responsable) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Acceso denegado. Solo para responsables.']);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    // GET - Obtener tipos de propiedad
    if ($method === 'GET' && $action === 'get_tipo_propiedad') {
        $stmt = $db->query('SELECT id, nombre FROM tipo_propiedad ORDER BY nombre');
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['ok' => true, 'tipos' => $tipos]);
        exit;
    }

    // GET - Listar garantías
    if ($method === 'GET') {
        $stmt = $db->prepare("
            SELECT g.*,
                   tp.nombre as tipo_propiedad_nombre
            FROM garantias g
            LEFT JOIN tipo_propiedad tp ON g.tipo_propiedad_id = tp.id
            ORDER BY g.orden ASC, g.nombre ASC
        ");
        $stmt->execute();
        $garantias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['ok' => true, 'garantias' => $garantias]);
        exit;
    }

    // POST - Crear garantía
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Datos JSON inválidos']);
            exit;
        }

        // Validar campos requeridos
        if (empty($input['nombre']) || !isset($input['tiempo_garantia_meses'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Nombre y tiempo de garantía son requeridos']);
            exit;
        }

        // Preparar datos
        $data = [
            'nombre' => trim($input['nombre']),
            'descripcion' => trim($input['descripcion'] ?? ''),
            'tiempo_garantia_meses' => intval($input['tiempo_garantia_meses']),
            'tipo_propiedad_id' => !empty($input['tipo_propiedad_id']) ? intval($input['tipo_propiedad_id']) : null,
            'estado' => isset($input['estado']) ? intval($input['estado']) : 1,
            'orden' => isset($input['orden']) ? intval($input['orden']) : 0
        ];

        // Validar tiempo de garantía
        if ($data['tiempo_garantia_meses'] < 1) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'El tiempo de garantía debe ser al menos 1 mes']);
            exit;
        }

        // Insertar
        $stmt = $db->prepare("
            INSERT INTO garantias (nombre, descripcion, tiempo_garantia_meses, tipo_propiedad_id, estado, orden)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['tiempo_garantia_meses'],
            $data['tipo_propiedad_id'],
            $data['estado'],
            $data['orden']
        ]);

        echo json_encode([
            'ok' => true,
            'mensaje' => 'Garantía creada correctamente',
            'id' => $db->lastInsertId()
        ]);
        exit;
    }

    // PUT - Actualizar garantía
    if ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID de garantía requerido']);
            exit;
        }

        $id = intval($input['id']);

        // Verificar que existe
        $stmt = $db->prepare('SELECT id FROM garantias WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Garantía no encontrada']);
            exit;
        }

        // Validar campos requeridos
        if (empty($input['nombre']) || !isset($input['tiempo_garantia_meses'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Nombre y tiempo de garantía son requeridos']);
            exit;
        }

        // Preparar datos
        $data = [
            'nombre' => trim($input['nombre']),
            'descripcion' => trim($input['descripcion'] ?? ''),
            'tiempo_garantia_meses' => intval($input['tiempo_garantia_meses']),
            'tipo_propiedad_id' => !empty($input['tipo_propiedad_id']) ? intval($input['tipo_propiedad_id']) : null,
            'estado' => isset($input['estado']) ? intval($input['estado']) : 1,
            'orden' => isset($input['orden']) ? intval($input['orden']) : 0
        ];

        // Validar tiempo de garantía
        if ($data['tiempo_garantia_meses'] < 1) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'El tiempo de garantía debe ser al menos 1 mes']);
            exit;
        }

        // Actualizar
        $stmt = $db->prepare("
            UPDATE garantias
            SET nombre = ?, descripcion = ?, tiempo_garantia_meses = ?,
                tipo_propiedad_id = ?, estado = ?, orden = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['tiempo_garantia_meses'],
            $data['tipo_propiedad_id'],
            $data['estado'],
            $data['orden'],
            $id
        ]);

        echo json_encode(['ok' => true, 'mensaje' => 'Garantía actualizada correctamente']);
        exit;
    }

    // DELETE - Eliminar garantía
    if ($method === 'DELETE') {
        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID de garantía requerido']);
            exit;
        }

        // Verificar que existe
        $stmt = $db->prepare('SELECT id FROM garantias WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Garantía no encontrada']);
            exit;
        }

        // Eliminar
        $stmt = $db->prepare('DELETE FROM garantias WHERE id = ?');
        $stmt->execute([$id]);

        echo json_encode(['ok' => true, 'mensaje' => 'Garantía eliminada correctamente']);
        exit;
    }

    // Método no soportado
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);

} catch (Exception $e) {
    error_log("Admin Garantías API - Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>