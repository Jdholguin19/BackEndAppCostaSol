<?php
require_once __DIR__.'/../../config/db.php';
$db = DB::getDB();

// Mapeo de íconos por tipo de contingencia
$iconos = [
    'Eléctrica' => 'https://via.placeholder.com/48/FFD700/000000?text=⚡',
    'Plomería' => 'https://via.placeholder.com/48/0000FF/FFFFFF?text=🚰',
    'Construcción' => 'https://via.placeholder.com/48/8B4513/FFFFFF?text=🏗️',
    'Acabados' => 'https://via.placeholder.com/48/FF69B4/FFFFFF?text=🎨',
    'Estructural' => 'https://via.placeholder.com/48/800080/FFFFFF?text=🏛️',
    'Jardinería' => 'https://via.placeholder.com/48/00FF00/FFFFFF?text=🌳',
    'Otros' => 'https://via.placeholder.com/48/808080/FFFFFF?text=❓'
];

$tipos = $db->query('SELECT id, nombre FROM tipo_ctg ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);

// Agregar íconos a cada tipo
foreach ($tipos as &$tipo) {
    $tipo['url_icono'] = $iconos[$tipo['nombre']] ?? $iconos['Otros'];
}

echo json_encode(['ok' => true, 'items' => $tipos]);
