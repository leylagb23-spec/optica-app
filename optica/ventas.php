<?php
require 'config.php'; requiere('ventas');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        if ($_POST['accion'] === 'venta') {
            $lineas = array_filter(array_map(null, $_POST['producto'] ?? [], $_POST['cantidad'] ?? []), fn($l) => $l[0] && (int)$l[1] > 0);
            if (!$lineas) throw new Exception('Agrega al menos un producto.');
            $pdo->prepare('INSERT INTO ventas (cliente_id,vendedor_id) VALUES (?,?)')->execute([$_POST['cliente_id'], $_SESSION['u']['id']]);
            $vid = $pdo->lastInsertId();
            $pr = $pdo->prepare('SELECT precio FROM productos WHERE id=?');
            $ins = $pdo->prepare('INSERT INTO detalle_ventas (venta_id,producto_id,cantidad,precio_unitario) VALUES (?,?,?,?)');
            foreach ($lineas as [$pid, $cant]) { $pr->execute([$pid]); $ins->execute([$vid, $pid, (int)$cant, $pr->fetchColumn()]); }
            if ((float)$_POST['abono'] > 0) $pdo->prepare('INSERT INTO pagos (venta_id,monto,forma_pago) VALUES (?,?,?)')->execute([$vid, $_POST['abono'], $_POST['forma_pago']]);
            if (isset($_POST['crear_orden'])) $pdo->prepare('CALL crear_orden_desde_venta(?,NULL,?,NULL,@o)')->execute([$vid, $_SESSION['u']['id']]);
            flash("Venta #$vid registrada.");
        } else {
            $pdo->prepare('INSERT INTO pagos (venta_id,monto,forma_pago) VALUES (?,?,?)')->execute([$_POST['venta_id'], $_POST['monto'], $_POST['forma_pago']]);
            flash('Pago registrado.');
        }
        $pdo->commit();
    } catch (Exception $e) { if ($pdo->inTransaction()) $pdo->rollBack(); flash(err($e), 'error'); }
    header('Location: ventas.php'); exit;
}
top('ventas', 'Ventas');
$clientes = $pdo->query('SELECT id,documento,nombre FROM clientes WHERE activo=1 ORDER BY nombre')->fetchAll();
$prods = $pdo->query('SELECT id,codigo,marca,modelo,precio,existencias,controla_stock FROM productos WHERE activo=1 ORDER BY codigo')->fetchAll();
$opts = '<option value="">— producto —</option>';
foreach ($prods as $p) $opts .= '<option value="' . $p['id'] . '">' . h($p['codigo'] . ' ' . $p['marca'] . ' ' . $p['modelo'] . ' · ' . money($p['precio']) . ($p['controla_stock'] ? ' · stock ' . $p['existencias'] : '')) . '</option>';
?>
<div class="card"><h3>Nueva venta</h3><form method="post"><input type="hidden" name="accion" value="venta">
<select name="cliente_id" required><option value="">— cliente —</option><?php foreach ($clientes as $c) echo '<option value="' . $c['id'] . '">' . h($c['documento'] . ' - ' . $c['nombre']) . '</option>'; ?></select>
<div id="lineas"><div class="row"><select name="producto[]"><?= $opts ?></select><input type="number" min="1" name="cantidad[]" value="1" style="width:70px"></div></div>
<template id="tpl"><div class="row"><select name="producto[]"><?= $opts ?></select><input type="number" min="1" name="cantidad[]" value="1" style="width:70px"></div></template>
<button type="button" class="sec" onclick="document.getElementById('lineas').appendChild(document.getElementById('tpl').content.cloneNode(true))">+ Agregar línea</button>
<div class="row" style="margin-top:10px"><input type="number" step="0.01" min="0" name="abono" placeholder="Abono inicial (opcional)">
<select name="forma_pago"><option>efectivo</option><option>tarjeta</option><option>transferencia</option><option>otro</option></select>
<label><input type="checkbox" name="crear_orden" checked> Crear orden de lentes</label><button>Registrar venta</button></div></form></div>
<div class="card"><h3>Ventas recientes</h3><table><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Total</th><th>Pagado</th><th>Estado</th><th>Abonar</th></tr>
<?php foreach ($pdo->query("SELECT v.*, c.nombre cliente, (SELECT COALESCE(SUM(monto),0) FROM pagos WHERE venta_id=v.id) pagado FROM ventas v JOIN clientes c ON c.id=v.cliente_id ORDER BY v.id DESC LIMIT 30") as $v): ?>
<tr><td><?= $v['id'] ?></td><td><?= h(substr($v['fecha'], 0, 16)) ?></td><td><?= h($v['cliente']) ?></td><td><?= money($v['total']) ?></td><td><?= money($v['pagado']) ?></td><td><?= h($v['estado']) ?></td>
<td><?php if ($v['estado'] == 'abierta'): ?><form method="post" class="row"><input type="hidden" name="accion" value="pago"><input type="hidden" name="venta_id" value="<?= $v['id'] ?>">
<input type="number" step="0.01" min="1" name="monto" value="<?= $v['total'] - $v['pagado'] ?>" style="width:100px"><select name="forma_pago"><option>efectivo</option><option>tarjeta</option><option>transferencia</option><option>otro</option></select><button>Pagar</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></table></div>
<?php bottom();
