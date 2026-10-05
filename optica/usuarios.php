<?php
require 'config.php'; requiere('usuarios');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['toggle'])) $pdo->prepare('UPDATE usuarios SET activo=1-activo WHERE id=? AND id<>?')->execute([$_POST['toggle'], $_SESSION['u']['id']]);
        else {
            if (strlen($_POST['password']) < 6) throw new Exception('La contraseña debe tener al menos 6 caracteres.');
            $pdo->prepare('INSERT INTO usuarios (rol_id,nombre,correo,password_hash) VALUES (?,?,?,?)')->execute([$_POST['rol_id'], trim($_POST['nombre']), trim($_POST['correo']), password_hash($_POST['password'], PASSWORD_DEFAULT)]);
            flash('Usuario creado.');
        }
    } catch (Exception $e) { flash($e->getCode() == 23000 ? 'Ese correo ya está registrado.' : err($e), 'error'); }
    header('Location: usuarios.php'); exit;
}
top('usuarios', 'Usuarios y roles');
?>
<div class="card"><form method="post" class="row"><input name="nombre" placeholder="Nombre" required><input type="email" name="correo" placeholder="Correo" required>
<input type="password" name="password" placeholder="Contraseña" required><select name="rol_id"><?php foreach ($pdo->query('SELECT * FROM roles WHERE nombre<>"Cliente"') as $r) echo '<option value="' . $r['id'] . '">' . h($r['nombre']) . '</option>'; ?></select><button>Crear usuario</button></form></div>
<div class="card"><table><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th></th></tr>
<?php foreach ($pdo->query('SELECT u.*, r.nombre rol FROM usuarios u JOIN roles r ON r.id=u.rol_id ORDER BY u.id') as $u): ?>
<tr><td><?= h($u['nombre']) ?></td><td><?= h($u['correo']) ?></td><td><?= h($u['rol']) ?></td><td><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></td>
<td><form method="post"><button class="sec" name="toggle" value="<?= $u['id'] ?>"><?= $u['activo'] ? 'Desactivar' : 'Activar' ?></button></form></td></tr>
<?php endforeach; ?></table></div>
<?php bottom();
