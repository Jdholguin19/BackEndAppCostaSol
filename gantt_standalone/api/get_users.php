<?php
// get_users.php - Obtiene la lista de usuarios desde la base de datos principal (portalao_appcostasol)

// Configuración de la base de datos principal
define('MAIN_DB_HOST', 'localhost');
define('MAIN_DB_USER', 'root');
define('MAIN_DB_PASS', '');
define('MAIN_DB_NAME', 'portalao_appCostaSol'); // Nombre de la base de datos principal

header('Content-Type: application/json');

$users = [];
$conn_main = new mysqli(MAIN_DB_HOST, MAIN_DB_USER, MAIN_DB_PASS, MAIN_DB_NAME);

if ($conn_main->connect_error) {
    error_log("Conexión fallida a la base de datos principal: " . $conn_main->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos principal']);
    exit;
}

$conn_main->set_charset("utf8");

// Obtener usuarios (asumiendo una tabla 'usuario' con 'id' y 'nombre' o 'username')
// Ajusta la consulta según la estructura real de tu tabla de usuarios
// La tabla 'usuario' tiene 'nombre' y 'apellido', así que los concatenamos para el nombre completo.
$result = $conn_main->query("SELECT id, CONCAT(nombres, ' ', apellidos) AS name FROM usuario ORDER BY name ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    error_log("Error al obtener usuarios de la base de datos principal: " . $conn_main->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener usuarios']);
    exit;
}

echo json_encode($users);

$conn_main->close();
?>