<?php
if (!isset($active_page)) {
    $active_page = '';
}
?>
<!-- Bottom Navigation -->
<div class="bottom-nav">
  <div class="bottom-nav-content">
    <a href="/Front/menu_front.php" class="nav-item <?php echo ($active_page === 'inicio') ? 'active' : ''; ?>">
      <i class="bi bi-house"></i>
      <span>Inicio</span>
    </a>
    <a href="/Front/citas.php" class="nav-item <?php echo ($active_page === 'citas') ? 'active' : ''; ?>">
      <i class="bi bi-calendar"></i>
      <span>Citas</span>
    </a>
    <a href="" class="nav-item <?php echo ($active_page === '') ? 'active' : ''; ?>">
      <i class="bi bi-chat-dots"></i>
      <span>Empresas</span>
    </a>
  </div>
</div>
