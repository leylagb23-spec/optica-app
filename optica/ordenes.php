<?php
require 'config.php'; requiere('ordenes');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $s = $pdo->prepare('SELECT estado_actual FROM ordenes WHERE id=?'); $s->execute([$_POST['id']]);
        $i = array_search($s->fetchColumn(), ESTADOS);
        if ($i === false || $i >= count(ESTADOS) - 1) throw new Exception('La orden ya fue entregada.');
        $pdo->prepare('UPDATE ordenes SET estado_actual=?, actualizado_por=? WHERE id=?')->execute([ESTADOS[$i + 1], $_SESSION['u']['id'], $_POST['id']]);
        flash('Orden #' . (int)$_POST['id'] . ' → ' . ESTADOS[$i + 1]);
    } catch (Exception $e) { flash(err($e), 'error'); }
    header('Location: ordenes.php'); exit;
}
top('ordenes', 'Órdenes de lentes');
$seg = $pdo->prepare('SELECT s.estado, s.fecha, u.nombre FROM seguimiento_ordenes s LEFT JOIN usuarios u ON u.id=s.usuario_id WHERE orden_id=? ORDER BY s.id');
?>
<div class="card"><table><tr><th>#</th><th>Cliente</th><th>Venta</th><th>Estado</th><th>Entrega est.</th><th>Seguimiento</th><th></th></tr>
<?php foreach ($pdo->query('SELECT o.*, c.nombre cliente FROM ordenes o JOIN ventas v ON v.id=o.venta_id JOIN clientes c ON c.id=v.cliente_id ORDER BY o.estado_actual="Entregada", o.id DESC') as $o): ?>
<tr><td><?= $o['id'] ?></td><td><?= h($o['cliente']) ?></td><td>#<?= $o['venta_id'] ?></td><td><b><?= h($o['estado_actual']) ?></b></td><td><?= h($o['fecha_entrega_estimada']) ?></td>
<td><details><summary>Ver</summary><?php $seg->execute([$o['id']]); foreach ($seg as $x) echo h(substr($x['fecha'], 0, 16) . ' · ' . $x['estado'] . ' · ' . $x['nombre']) . '<br>'; ?></details></td>
<td><?php $i = array_search($o['estado_actual'], ESTADOS); if ($i < count(ESTADOS) - 1): ?><form method="post"><input type="hidden" name="id" value="<?= $o['id'] ?>"><button>→ <?= h(ESTADOS[$i + 1]) ?></button></form><?php endif; ?></td></tr>
<?php endforeach; ?></table></div>
<?php bottom();
