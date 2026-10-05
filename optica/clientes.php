<?php
require 'config.php'; requiere('clientes');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->prepare('INSERT INTO clientes (documento,nombre,fecha_nacimiento,telefono,correo,direccion,observaciones) VALUES (?,?,?,?,?,?,?)')
            ->execute([trim($_POST['documento']), trim($_POST['nombre']), $_POST['fecha_nacimiento'] ?: null, $_POST['telefono'], $_POST['correo'], $_POST['direccion'], $_POST['observaciones']]);
        flash('Cliente registrado.');
    } catch (PDOException $e) { flash($e->getCode() == 23000 ? 'Ya existe un cliente con ese documento.' : err($e), 'error'); }
    header('Location: clientes.php'); exit;
}
top('clientes', 'Clientes');
$busca = trim($_GET['q'] ?? '');
$s = $pdo->prepare('SELECT * FROM clientes WHERE nombre LIKE ? OR documento LIKE ? ORDER BY id DESC LIMIT 50');
$s->execute(["%$busca%", "%$busca%"]);
?>
<div class="card"><h3>Nuevo cliente</h3><form method="post" class="row">
<input name="documento" placeholder="Documento" required><input name="nombre" placeholder="Nombre completo" required>
<input type="date" name="fecha_nacimiento" title="Fecha de nacimiento"><input name="telefono" placeholder="Teléfono"><input type="email" name="correo" placeholder="Correo">
<input name="direccion" placeholder="Dirección"><input name="observaciones" placeholder="Observaciones"><button>Guardar</button></form></div>
<div class="card"><form class="row"><input name="q" value="<?= h($busca) ?>" placeholder="Buscar por nombre o documento"><button class="sec">Buscar</button></form><br>
<table><tr><th>Documento</th><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Registro</th></tr>
<?php foreach ($s as $c): ?><tr><td><?= h($c['documento']) ?></td><td><?= h($c['nombre']) ?></td><td><?= h($c['telefono']) ?></td><td><?= h($c['correo']) ?></td><td><?= h(substr($c['fecha_registro'], 0, 10)) ?></td></tr><?php endforeach; ?>
</table></div>
<?php bottom();
