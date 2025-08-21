<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($active_page)) {
    $active_page = '';
}

$pdo = DB::getDB();

// Fetch all menus marked for menu_bar = 1 AND estado = 1
$stmt = $pdo->prepare("SELECT id, nombre, url_icono FROM menu WHERE menu_bar = 1 ORDER BY orden ASC");
$stmt->execute();
$menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!-- Bottom Navigation -->
<div class="bottom-nav">
  <div class="bottom-nav-content">
    <a href="/Front/menu_front.php" class="nav-item <?php echo ($active_page === 'inicio') ? 'active' : ''; ?>">
      <i class="bi bi-house"></i>
      <span>Inicio</span>
    </a>
    <?php foreach ($menuItems as $menuItem):
        $linkHref = '';
        $linkActivePage = '';
        $linkName = $menuItem['nombre'];
        $linkIcon = $menuItem['url_icono'];

        switch ($menuItem['id']) {
            case 2: // CTG
                $linkHref = '/Front/ctg/ctg.php';
                $linkActivePage = 'ctg';
                break;
            case 3: // Agendar Visitas (Cita)
                $linkHref = '/Front/citas.php';
                $linkActivePage = 'citas';
                $linkName = 'Visitas'; // Override name
                $linkIcon = 'bi-calendar'; // Override icon
                break;
            case 4: // Empresas Aliadas (Empresas)
                $linkHref = ''; // As per user's request
                $linkActivePage = ''; // As per user's request
                $linkName = 'Convenios'; // Override name
                $linkIcon = 'bi-building'; // Override icon
                break;  

            case 6: // Garantias
                $linkHref = '/Front/garantias.php'; 
                $linkActivePage = 'garantias'; 
                $linkName = 'Garantias'; // Override name
                $linkIcon = 'bi-shield-check'; // Override icon
                break;

            case 8: // Notificaciones
                $linkHref = '/Front/notificaciones.php';
                $linkActivePage = 'notificaciones';
                break;
            case 9: // PQR
                $linkHref = '/Front/pqr/pqr.php';
                $linkActivePage = 'pqr';
                break;
            default:
                // If the menu item is not explicitly mapped, skip it for bottom nav
                continue 2; // Skip to the next item in the foreach loop
        }
    ?>
    <a href="<?php echo $linkHref; ?>" class="nav-item <?php echo ($active_page === $linkActivePage) ? 'active' : ''; ?>">
      <?php if (strpos($linkIcon, 'bi-') === 0): ?>
        <i class="bi <?php echo $linkIcon; ?>"></i>
      <?php else: ?>
        <img src="<?php echo $linkIcon; ?>" alt="<?php echo $linkName; ?> Icon" style="width: 24px; height: 24px;">
      <?php endif; ?>
      <span><?php echo $linkName; ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</div>