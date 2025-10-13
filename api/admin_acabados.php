<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';

// --- Lógica de Autenticación para Responsables ---
$auth_id = null;
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    try {
        $conn_auth = DB::getDB();
        $stmt = $conn_auth->prepare('SELECT id FROM responsable WHERE token = :token AND estado = 1');
        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $auth_id = $result['id'];
        }
    } catch (Exception $e) {}
}
if ($auth_id === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado. Solo responsables pueden acceder.']);
    exit();
}
// --- Fin Autenticación ---

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// For POST requests with JSON body, parse action from body
if ($method === 'POST' && empty($action)) {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
    }
}

try {
    $conn = DB::getDB();

    switch ($method) {
        case 'GET':
            handleGet($conn, $action);
            break;
        case 'POST':
            handlePost($conn, $action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
    }
} catch (Throwable $e) {
    error_log('admin_acabados: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}

function handleGet($conn, $action) {
    switch ($action) {
        case 'get_kits':
            getKits($conn);
            break;
        case 'get_kit':
            getKit($conn);
            break;
        case 'get_packages':
            getPackages($conn);
            break;
        case 'get_package':
            getPackage($conn);
            break;
        case 'get_color_options':
            getColorOptions($conn);
            break;
        case 'get_color_option':
            getColorOption($conn);
            break;
        case 'get_acabado_detail':
            getAcabadoDetail($conn);
            break;
        case 'get_componentes':
            getComponentes($conn);
            break;
        case 'get_componente':
            getComponente($conn);
            break;
        case 'get_color_names':
            getColorNames($conn);
            break;
        case 'get_color_names':
            getColorNames($conn);
            break;
        case 'get_all_color_options':
            getAllColorOptions($conn);
            break;
        case 'get_all_acabado_details':
            getAllAcabadoDetails($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
    }
}

function handlePost($conn, $action) {
    switch ($action) {
        case 'save_kit':
            saveKit($conn);
            break;
        case 'delete_kit':
            deleteKit($conn);
            break;
        case 'save_package':
            savePackage($conn);
            break;
        case 'delete_package':
            deletePackage($conn);
            break;
        case 'save_color_option':
            saveColorOption($conn);
            break;
        case 'delete_color_option':
            deleteColorOption($conn);
            break;
        case 'save_acabado_detail':
            saveAcabadoDetail($conn);
            break;
        case 'delete_acabado_detail':
            deleteAcabadoDetail($conn);
            break;
        case 'save_componente':
            saveComponente($conn);
            break;
        case 'delete_componente':
            deleteComponente($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
    }
}

function getKits($conn) {
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, url_imagen_principal, costo FROM acabado_kit ORDER BY id ASC");
    $stmt->execute();
    $kits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'kits' => $kits]);
}

function getKit($conn) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID de kit requerido']);
        return;
    }
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, url_imagen_principal, costo FROM acabado_kit WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $kit = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($kit) {
        echo json_encode(['ok' => true, 'kit' => $kit]);
    } else {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Kit no encontrado']);
    }
}

function getPackages($conn) {
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, precio, fotos, activo FROM paquetes_adicionales ORDER BY id ASC");
    $stmt->execute();
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($packages as &$package) {
        if (!empty($package['fotos'])) {
            $package['fotos'] = json_decode($package['fotos'], true);
        } else {
            $package['fotos'] = [];
        }
    }
    echo json_encode(['ok' => true, 'packages' => $packages]);
}

function getPackage($conn) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID de paquete requerido']);
        return;
    }
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, precio, fotos, activo FROM paquetes_adicionales WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($package) {
        if (!empty($package['fotos'])) {
            $package['fotos'] = json_decode($package['fotos'], true);
        } else {
            $package['fotos'] = [];
        }
        echo json_encode(['ok' => true, 'package' => $package]);
    } else {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Paquete no encontrado']);
    }
}

function getColorOptions($conn) {
    $kit_id = filter_input(INPUT_GET, 'kit_id', FILTER_VALIDATE_INT);
    if (!$kit_id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID de kit requerido']);
        return;
    }
    $stmt = $conn->prepare("SELECT id, nombre_opcion, color_nombre, url_imagen_opcion FROM kit_color_opcion WHERE acabado_kit_id = :kit_id ORDER BY id ASC");
    $stmt->execute([':kit_id' => $kit_id]);
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'options' => $options]);
}

function getAcabadoDetails($conn) {
    $kit_id = filter_input(INPUT_GET, 'kit_id', FILTER_VALIDATE_INT);
    $color = filter_input(INPUT_GET, 'color', FILTER_SANITIZE_STRING);
    if (!$kit_id || !$color) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'Kit ID y color requeridos']);
        return;
    }
    $stmt = $conn->prepare("
        SELECT ad.id, c.nombre AS componente, ad.color, ad.url_imagen, ad.descripcion
        FROM acabado_detalle ad
        JOIN componente c ON ad.componente_id = c.id
        WHERE ad.acabado_kit_id = :kit_id AND ad.color = :color
        ORDER BY ad.id ASC
    ");
    $stmt->execute([':kit_id' => $kit_id, ':color' => $color]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'details' => $details]);
}

function saveKit($conn) {
    $id = $_POST['kitId'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $costo = floatval($_POST['costo'] ?? 0);

    if (empty($nombre) || $costo < 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'Nombre y costo válido requeridos']);
        return;
    }

    $url_imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $url_imagen = uploadImage($_FILES['imagen'], 'kits');
    }

    if ($id) {
        // Update
        $sql = "UPDATE acabado_kit SET nombre = :nombre, descripcion = :descripcion, costo = :costo";
        $params = ['nombre' => $nombre, 'descripcion' => $descripcion, 'costo' => $costo, 'id' => $id];
        if ($url_imagen) {
            $sql .= ", url_imagen_principal = :url_imagen";
            $params['url_imagen'] = $url_imagen;
        }
        $sql .= " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    } else {
        // Insert
        $sql = "INSERT INTO acabado_kit (nombre, descripcion, costo, url_imagen_principal) VALUES (:nombre, :descripcion, :costo, :url_imagen)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['nombre' => $nombre, 'descripcion' => $descripcion, 'costo' => $costo, 'url_imagen' => $url_imagen]);
    }

    echo json_encode(['ok' => true, 'mensaje' => 'Kit guardado correctamente']);
}

function deleteKit($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID requerido']);
        return;
    }
    $stmt = $conn->prepare("DELETE FROM acabado_kit WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['ok' => true, 'mensaje' => 'Kit eliminado correctamente']);
}

function savePackage($conn) {
    error_log('savePackage called, FILES: ' . print_r($_FILES, true));
    error_log('POST: ' . print_r($_POST, true));
    $id = $_POST['packageId'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombre) || $precio < 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'Nombre y precio válido requeridos']);
        return;
    }

    $fotos_json = null;
    $fotos = [];
    if (isset($_FILES['fotos'])) {
        // Normalize to array of files
        $files = [];
        if (!is_array($_FILES['fotos']['tmp_name'])) {
            // Single file
            if ($_FILES['fotos']['error'] === UPLOAD_ERR_OK) {
                $files[] = $_FILES['fotos'];
            }
        } else {
            // Multiple files
            foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['fotos']['error'][$key] === UPLOAD_ERR_OK) {
                    $files[] = [
                        'tmp_name' => $_FILES['fotos']['tmp_name'][$key],
                        'name' => $_FILES['fotos']['name'][$key],
                        'type' => $_FILES['fotos']['type'][$key],
                        'size' => $_FILES['fotos']['size'][$key],
                        'error' => $_FILES['fotos']['error'][$key]
                    ];
                }
            }
        }
        // Upload each file
        foreach ($files as $file) {
            $uploaded = uploadImage($file, 'paquetes');
            if ($uploaded) {
                $fotos[] = $uploaded;
            }
        }
    }

    if ($id) {
        // Update: get existing fotos and append new ones
        $stmt_get = $conn->prepare("SELECT fotos FROM paquetes_adicionales WHERE id = :id");
        $stmt_get->execute([':id' => $id]);
        $existing = $stmt_get->fetch(PDO::FETCH_ASSOC);
        $existing_fotos = $existing ? json_decode($existing['fotos'], true) : [];
        if (!is_array($existing_fotos)) $existing_fotos = [];
        $fotos = array_merge($existing_fotos, $fotos);
        $fotos_json = json_encode($fotos);
        $sql = "UPDATE paquetes_adicionales SET nombre = :nombre, descripcion = :descripcion, precio = :precio, fotos = :fotos, activo = :activo WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['nombre' => $nombre, 'descripcion' => $descripcion, 'precio' => $precio, 'fotos' => $fotos_json, 'activo' => $activo, 'id' => $id]);
    } else {
        // Insert
        $fotos_json = json_encode($fotos);
        $stmt = $conn->prepare("INSERT INTO paquetes_adicionales (nombre, descripcion, precio, fotos, activo) VALUES (:nombre, :descripcion, :precio, :fotos, :activo)");
        $stmt->execute(['nombre' => $nombre, 'descripcion' => $descripcion, 'precio' => $precio, 'fotos' => $fotos_json, 'activo' => $activo]);
    }

    echo json_encode(['ok' => true, 'mensaje' => 'Paquete guardado correctamente']);
}

function deletePackage($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID requerido']);
        return;
    }
    $stmt = $conn->prepare("DELETE FROM paquetes_adicionales WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['ok' => true, 'mensaje' => 'Paquete eliminado correctamente']);
}

function saveColorOption($conn) {
    $id = $_POST['optionId'] ?? null;
    $kit_id = intval($_POST['kitId'] ?? 0);
    $nombre_opcion = trim($_POST['nombreOpcion'] ?? '');
    $color_nombre = trim($_POST['colorNombre'] ?? '');

    if (!$kit_id || empty($nombre_opcion) || empty($color_nombre)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'Kit ID, nombre de opción y color requeridos']);
        return;
    }

    $url_imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $url_imagen = uploadImage($_FILES['imagen'], 'acabados');
    }

    if ($id) {
        // Update
        $sql = "UPDATE kit_color_opcion SET nombre_opcion = :nombre_opcion, color_nombre = :color_nombre";
        $params = ['nombre_opcion' => $nombre_opcion, 'color_nombre' => $color_nombre, 'id' => $id];
        if ($url_imagen) {
            $sql .= ", url_imagen_opcion = :url_imagen";
            $params['url_imagen'] = $url_imagen;
        }
        $sql .= " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    } else {
        // Insert
        $sql = "INSERT INTO kit_color_opcion (acabado_kit_id, nombre_opcion, color_nombre, url_imagen_opcion) VALUES (:kit_id, :nombre_opcion, :color_nombre, :url_imagen)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['kit_id' => $kit_id, 'nombre_opcion' => $nombre_opcion, 'color_nombre' => $color_nombre, 'url_imagen' => $url_imagen]);
    }

    echo json_encode(['ok' => true, 'mensaje' => 'Opción de color guardada correctamente']);
}

function deleteColorOption($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID requerido']);
        return;
    }
    $stmt = $conn->prepare("DELETE FROM kit_color_opcion WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['ok' => true, 'mensaje' => 'Opción de color eliminada correctamente']);
}

function saveAcabadoDetail($conn) {
    $id = $_POST['detailId'] ?? null;
    $kit_id = intval($_POST['kitId'] ?? 0);
    $componente_id = intval($_POST['componenteId'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (!$kit_id || !$componente_id || empty($color)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'Kit ID, componente ID y color requeridos']);
        return;
    }

    $url_imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $url_imagen = uploadImage($_FILES['imagen'], 'acabados');
    }

    if ($id) {
        // Update
        $sql = "UPDATE acabado_detalle SET componente_id = :componente_id, color = :color, descripcion = :descripcion";
        $params = ['componente_id' => $componente_id, 'color' => $color, 'descripcion' => $descripcion, 'id' => $id];
        if ($url_imagen) {
            $sql .= ", url_imagen = :url_imagen";
            $params['url_imagen'] = $url_imagen;
        }
        $sql .= " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    } else {
        // Insert
        $sql = "INSERT INTO acabado_detalle (acabado_kit_id, componente_id, color, url_imagen, descripcion) VALUES (:kit_id, :componente_id, :color, :url_imagen, :descripcion)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['kit_id' => $kit_id, 'componente_id' => $componente_id, 'color' => $color, 'url_imagen' => $url_imagen, 'descripcion' => $descripcion]);
    }

    echo json_encode(['ok' => true, 'mensaje' => 'Detalle de acabado guardado correctamente']);
}

function deleteAcabadoDetail($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID requerido']);
        return;
    }
    $stmt = $conn->prepare("DELETE FROM acabado_detalle WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['ok' => true, 'mensaje' => 'Detalle de acabado eliminado correctamente']);
}

function getColorOption($conn) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID requerido']);
        return;
    }
    $stmt = $conn->prepare("SELECT id, acabado_kit_id, nombre_opcion, color_nombre, url_imagen_opcion FROM kit_color_opcion WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $option = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($option) {
        echo json_encode(['ok' => true, 'option' => $option]);
    } else {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Opción no encontrada']);
    }
}

function getAcabadoDetail($conn) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID requerido']);
        return;
    }
    $stmt = $conn->prepare("SELECT ad.id, ad.acabado_kit_id, ad.componente_id, ad.color, ad.url_imagen, ad.descripcion FROM acabado_detalle ad WHERE ad.id = :id");
    $stmt->execute([':id' => $id]);
    $detail = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($detail) {
        echo json_encode(['ok' => true, 'detail' => $detail]);
    } else {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Detalle no encontrado']);
    }
}

function getComponentes($conn) {
    $stmt = $conn->prepare("SELECT id, nombre FROM componente ORDER BY id ASC");
    $stmt->execute();
    $componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'componentes' => $componentes]);
}

function getComponente($conn) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID de componente requerido']);
        return;
    }
    $stmt = $conn->prepare("SELECT id, nombre FROM componente WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $componente = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($componente) {
        echo json_encode(['ok' => true, 'componente' => $componente]);
    } else {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Componente no encontrado']);
    }
}

function saveComponente($conn) {
    $id = $_POST['componenteId'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');

    if (empty($nombre)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'Nombre requerido']);
        return;
    }

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE componente SET nombre = :nombre WHERE id = :id");
        $stmt->execute(['nombre' => $nombre, 'id' => $id]);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO componente (nombre) VALUES (:nombre)");
        $stmt->execute(['nombre' => $nombre]);
    }

    echo json_encode(['ok' => true, 'mensaje' => 'Componente guardado correctamente']);
}

function deleteComponente($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID requerido']);
        return;
    }
    $stmt = $conn->prepare("DELETE FROM componente WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['ok' => true, 'mensaje' => 'Componente eliminado correctamente']);
}

function getAllAcabadoDetails($conn) {
    $stmt = $conn->prepare("
        SELECT ad.id, ak.nombre AS kit, c.nombre AS componente, ad.color, ad.url_imagen, ad.descripcion
        FROM acabado_detalle ad
        JOIN acabado_kit ak ON ad.acabado_kit_id = ak.id
        JOIN componente c ON ad.componente_id = c.id
        ORDER BY ad.id ASC
    ");
    $stmt->execute();
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'details' => $details]);
}

function getAllColorOptions($conn) {
    $stmt = $conn->prepare("
        SELECT kco.id, ak.nombre AS kit, kco.nombre_opcion, kco.color_nombre, kco.url_imagen_opcion
        FROM kit_color_opcion kco
        JOIN acabado_kit ak ON kco.acabado_kit_id = ak.id
        ORDER BY kco.id ASC
    ");
    $stmt->execute();
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'options' => $options]);
}

function getColorNames($conn) {
    $stmt = $conn->prepare("SELECT DISTINCT color_nombre FROM kit_color_opcion ORDER BY color_nombre ASC");
    $stmt->execute();
    $colors = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['ok' => true, 'colors' => $colors]);
}

function uploadImage($file, $subdir) {
    // Validar archivo
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }

    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        return false;
    }

    // Crear directorio
    $upload_dir = __DIR__ . '/../uploads/' . $subdir . '/';
    error_log('Upload dir: ' . $upload_dir);
    if (!is_dir($upload_dir)) {
        error_log('Creating directory: ' . $upload_dir);
        mkdir($upload_dir, 0777, true);
        if (!is_dir($upload_dir)) {
            error_log('Failed to create directory: ' . $upload_dir);
            return false;
        }
    }

    // Verificar que el directorio sea escribible
    if (!is_writable($upload_dir)) {
        error_log('Directorio de subida no es escribible: ' . $upload_dir);
        return false;
    }

    // Generar nombre único
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    error_log('Upload path: ' . $upload_path);

    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        error_log('File uploaded successfully: ' . $upload_path);
        return "https://app.costasol.com.ec/uploads/$subdir/$new_filename";
    }

    error_log('Error al mover archivo subido para ' . $subdir . ': ' . $file['name'] . ' to ' . $upload_path);
    return false;
}
?>