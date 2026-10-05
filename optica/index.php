<?php
require 'config.php'; requiere('index'); top('index', 'Dashboard');
$q = fn($sql) => $pdo->query($sql)->fetchColumn();
?>
<div class="grid">
<div class="card kpi"><b><?= $q('SELECT COUNT(*) FROM clientes WHERE activo=1') ?></b>Clientes</div>
<div class="card kpi"><b><?= money($q("SELECT COALESCE(SUM(total),0) FROM ventas WHERE DATE(fecha)=CURDATE() AND estado<>'anulada'")) ?></b>Ventas de hoy</div>
<div class="card kpi"><b><?= $q('SELECT COUNT(*) FROM v_ordenes_pendientes') ?></b>Órdenes pendientes</div>
<div class="card kpi"><b><?= $q('SELECT COUNT(*) FROM v_productos_bajo_stock') ?></b>Productos con bajo stock</div>
</div>
<div class="card"><h3>Alertas de inventario</h3><table><tr><th>Código</th><th>Producto</th><th>Existencias</th><th>Mínimo</th><th>Alerta</th></tr>
<?php foreach ($pdo->query('SELECT * FROM v_productos_bajo_stock') as $p): ?>
<tr><td><?= h($p['codigo']) ?></td><td><?= h($p['marca'] . ' ' . $p['modelo']) ?></td><td><?= $p['existencias'] ?></td><td><?= $p['stock_minimo'] ?></td><td class="bajo"><?= h($p['alerta']) ?></td></tr>
<?php endforeach; ?></table></div>
<div class="card"><h3>Órdenes pendientes</h3><table><tr><th>#</th><th>Cliente</th><th>Estado</th><th>Entrega estimada</th></tr>
<?php foreach ($pdo->query('SELECT * FROM v_ordenes_pendientes ORDER BY fecha_entrega_estimada') as $o): ?>
<tr><td><?= $o['orden_id'] ?></td><td><?= h($o['cliente']) ?></td><td><?= h($o['estado_actual']) ?></td><td><?= h($o['fecha_entrega_estimada']) ?></td></tr>
<?php endforeach; ?></table></div>
<?php bottom();
