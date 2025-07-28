<?php
require_once __DIR__ . '/../config/db.php';
$db = DB::getDB();

// Leer filtros actuales
$etapa = $_GET['etapa'] ?? null;
$mz = $_GET['mz'] ?? null;

// Etapas disponibles
$etapas = $db->query("SELECT DISTINCT id_etapa FROM progreso_construccion ORDER BY id_etapa")->fetchAll();

// Manzanas para la etapa seleccionada
$manzanas = [];
if ($etapa) {
    $stmt = $db->prepare("SELECT DISTINCT mz FROM progreso_construccion WHERE id_etapa = :etapa");
    $stmt->execute([':etapa' => $etapa]);
    $manzanas = $stmt->fetchAll();
}

// Villas para la manzana seleccionada
$villas = [];
if ($etapa && $mz) {
    $stmt = $db->prepare("SELECT DISTINCT villa FROM progreso_construccion WHERE id_etapa = :etapa AND mz = :mz");
    $stmt->execute([':etapa' => $etapa, ':mz' => $mz]);
    $villas = $stmt->fetchAll();
}
?>

<form method="GET">
    <label>Etapa:
        <select name="etapa" onchange="this.form.submit()">
            <option value="">-- Todas --</option>
            <?php foreach ($etapas as $e): ?>
                <option value="<?= $e['id_etapa']; ?>" <?= $etapa == $e['id_etapa'] ? 'selected' : ''; ?>>
                    Etapa <?= $e['id_etapa']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Mz:
        <select name="mz" onchange="this.form.submit()">
            <option value="">-- Todas --</option>
            <?php foreach ($manzanas as $m): ?>
                <option value="<?= $m['mz']; ?>" <?= $mz == $m['mz'] ? 'selected' : ''; ?>>
                    <?= $m['mz']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Villa:
        <select name="villa">
            <option value="">-- Todas --</option>
            <?php foreach ($villas as $v): ?>
                <option value="<?= $v['villa']; ?>" <?= ($_GET['villa'] ?? '') == $v['villa'] ? 'selected' : ''; ?>>
                    <?= $v['villa']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Filtrar</button>
</form>

<hr>

<?php
// Ahora mostrar los resultados
$sql = "SELECT * FROM progreso_construccion WHERE 1";
$params = [];

if ($etapa) {
    $sql .= " AND id_etapa = :etapa";
    $params[':etapa'] = $etapa;
}
if ($mz) {
    $sql .= " AND mz = :mz";
    $params[':mz'] = $mz;
}
if (!empty($_GET['villa'])) {
    $sql .= " AND villa = :villa";
    $params[':villa'] = $_GET['villa'];
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll();

// Mostrar las fotos
if ($resultados) {
    foreach ($resultados as $img) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
        echo "<h3>Etapa: {$img['id_etapa']} | Mz: {$img['mz']} | Villa: {$img['villa']}</h3>";
        echo "<img src='mostrar_imagen.php?id=" . $img['id'] . "' style='max-width:300px;' alt='Imagen'><br />";
        echo "<a href='mostrar_imagen.php?id=" . $img['id'] . "' target='_blank'>Ver imagen completa</a><br />";
        echo "<small>Creado: {$img['fecha_creado_sharepoint']} por {$img['usuario_creador']} | Modificado: {$img['fecha_modificado_sharepoint']} por {$img['usuario_modificado_sharepoint']}</small>";
        echo "</div>";
    }
} else {
    echo "<p>No hay registros para esta búsqueda.</p>";
}
?>
