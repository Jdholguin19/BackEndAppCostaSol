<?php
// api/acabado_seleccion_guardada.php

declare(strict_types=1);
header('Content-Type: application/json');

require_once '/../../config/db.php';

// --- Lógica de Autenticación ---
$auth_data = null;
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    try {
        $pdo = DB::getDB();
        $stmt = $pdo->prepare("SELECT id, rol_id, 'usuario' as tipo FROM usuario WHERE token = :token UNION SELECT id, NULL as rol_id, 'responsable' as tipo FROM responsable WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $auth_data = [
                'id' => (int)$user['id'],
                'rol_id' => $user['rol_id'] ? (int)$user['rol_id'] : null,
                'is_responsable' => $user['tipo'] === 'responsable'
            ];
        }
    } catch (Exception $e) {
        // Silencio en caso de error de BD durante la autenticación
    }
}

if ($auth_data === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado.']);
    exit;
}
$authUserId = $auth_data['id'];
$isResponsable = $auth_data['is_responsable'];
// --- Fin Autenticación ---


if (!isset($_GET['propiedad_id'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'El ID de la propiedad es requerido.']);
    exit;
}
$propiedadId = filter_var($_GET['propiedad_id'], FILTER_VALIDATE_INT);
if ($propiedadId === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'El ID de la propiedad no es válido.']);
    exit;
}

try {
    $pdo = DB::getDB();

    // CONSULTA CORREGIDA con los nombres de columna correctos de la tabla propiedad
    $stmt = $pdo->prepare("SELECT id_usuario, acabado_kit_seleccionado_id, acabado_color_seleccionado FROM propiedad WHERE id = ?");
    $stmt->execute([$propiedadId]);
    $propiedad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$propiedad) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Propiedad no encontrada.']);
        exit;
    }

    if (!$isResponsable && $propiedad['id_usuario'] != $authUserId) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'mensaje' => 'No tienes permiso para ver esta propiedad.']);
        exit;
    }

    // Verificación CORREGIDA usando el nombre de columna correcto
    if (is_null($propiedad['acabado_kit_seleccionado_id'])) {
        echo json_encode(['ok' => true, 'seleccionGuardada' => false]);
        exit;
    }

    // Asignación CORREGIDA usando los nombres de columna correctos
    $kitId = (int)$propiedad['acabado_kit_seleccionado_id'];
    $colorNombre = $propiedad['acabado_color_seleccionado'];

    $stmt_kit = $pdo->prepare("SELECT id, nombre, costo, descripcion, url_imagen_principal FROM acabado_kit WHERE id = ?");
    $stmt_kit->execute([$kitId]);
    $kitDetails = $stmt_kit->fetch(PDO::FETCH_ASSOC);

    $stmt_color = $pdo->prepare("SELECT nombre_opcion, url_imagen_opcion, color_nombre FROM kit_color_opcion WHERE acabado_kit_id = ? AND color_nombre = ?");
    $stmt_color->execute([$kitId, $colorNombre]);
    $colorDetails = $stmt_color->fetch(PDO::FETCH_ASSOC);

    $stmt_packages = $pdo->prepare("
        SELECT p.id, p.nombre, p.descripcion, p.precio 
        FROM propiedad_paquetes_adicionales ppa
        JOIN paquetes_adicionales p ON ppa.paquete_id = p.id
        WHERE ppa.propiedad_id = ?
    ");
    $stmt_packages->execute([$propiedadId]);
    $packages = $stmt_packages->fetchAll(PDO::FETCH_ASSOC);

    $stmt_fotos = $pdo->prepare("SELECT url_foto FROM paquete_fotos WHERE paquete_id = ?");
    foreach ($packages as &$pkg) {
        $stmt_fotos->execute([$pkg['id']]);
        $fotos = $stmt_fotos->fetchAll(PDO::FETCH_COLUMN, 0);
        $pkg['fotos'] = $fotos;
    }
    unset($pkg);

    $response = [
        'ok' => true,
        'seleccionGuardada' => true,
        'data' => [
            'kit' => $kitDetails,
            'color' => $colorDetails,
            'packages' => $packages
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Database error in acabado_seleccion_guardada: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor.']);
}
?>