<?php
require_once __DIR__ . '/../config/db.php';
$db = DB::getDB();

/* --------- 1. Lista de roles para el <select> --------- */
$roles = $db->query('SELECT id, nombre FROM rol ORDER BY nombre')->fetchAll(PDO::FETCH_KEY_PAIR);

/* --------- 2. Usuarios (incluye rol_id para edición) --------- */
$usuarios = $db->query(
  'SELECT u.id, u.nombres, u.apellidos, u.correo,
          u.rol_id, r.nombre AS rol
     FROM usuario u
     JOIN rol r ON r.id = u.rol_id
     ORDER BY u.id DESC'
)->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Gestión de usuarios</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Usuarios</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario"
            onclick="nuevoUsuario()">Nuevo</button>
  </div>

  <table class="table table-sm table-striped align-middle">
    <thead class="table-dark"><tr>
      <th>ID</th><th>Nombres</th><th>Apellidos</th><th>Correo</th><th>Rol</th><th></th>
    </tr></thead>
    <tbody id="tbody">
      <?php foreach ($usuarios as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['nombres']) ?></td>
          <td><?= htmlspecialchars($u['apellidos']) ?></td>
          <td><?= htmlspecialchars($u['correo']) ?></td>
          <td><?= htmlspecialchars($u['rol']) ?></td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-secondary me-1"
                    data-bs-toggle="modal" data-bs-target="#modalUsuario"
                    onclick='editarUsuario(<?= json_encode($u) ?>)'>✏️</button>
            <button class="btn btn-sm btn-outline-danger"
                    onclick="borrarUsuario(<?= $u['id'] ?>)">🗑️</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal alta/edición -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
  <div class="modal-dialog"><form class="modal-content" id="formUsuario">
    <div class="modal-header">
      <h5 class="modal-title" id="modalTitle">Nuevo usuario</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body row g-2">
      <input type="hidden" name="id" id="id">
      <div class="col-6"><input required class="form-control" name="nombres" id="nombres" placeholder="Nombres"></div>
      <div class="col-6"><input required class="form-control" name="apellidos" id="apellidos" placeholder="Apellidos"></div>
      <div class="col-8"><input required type="email" class="form-control" name="correo" id="correo" placeholder="Correo"></div>

      <!-- Select de roles -->
      <div class="col-4">
        <select class="form-select" name="rol_id" id="rol_id" required>
          <?php foreach ($roles as $id => $nombre): ?>
            <option value="<?= $id ?>"><?= htmlspecialchars($nombre) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12">
        <input type="password" class="form-control" name="contrasena" id="contrasena"
               placeholder="Contraseña (solo al crear / cambiar)">
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      <button class="btn btn-primary">Guardar</button>
    </div>
  </form></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const form = document.getElementById('formUsuario');

form.addEventListener('submit', async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(form).entries());
  const metodo = data.id ? 'PUT' : 'POST';
  await fetch('user_crud.php', {
    method: metodo,
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  }).then(r => r.json()).then(r => {
    if (!r.ok) return alert(r.mensaje);
    location.reload();
  });
});

function nuevoUsuario() {
  form.reset();
  document.getElementById('id').value = '';
  document.getElementById('modalTitle').textContent = 'Nuevo usuario';
}

function editarUsuario(u) {
  document.getElementById('modalTitle').textContent = 'Editar usuario';
  for (const k in u) if (document.getElementById(k)) document.getElementById(k).value = u[k];
  document.getElementById('rol_id').value = u.rol_id; // selecciona el rol correcto
}

async function borrarUsuario(id) {
  if (!confirm('¿Borrar usuario?')) return;
  await fetch('user_crud.php?id=' + id, { method: 'DELETE' })
    .then(r => r.json()).then(r => {
      if (!r.ok) return alert(r.mensaje);
      location.reload();
    });
}
</script>
</body></html>
