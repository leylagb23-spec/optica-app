<?php
require 'config.php'; requiere('productos');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($_POST['accion'] === 'producto') {
            $pdo->prepare('INSERT INTO productos (codigo,categoria_id,marca,modelo,descripcion,precio,existencias,stock_minimo,controla_stock) VALUES (?,?,?,?,?,?,0,?,?)')
                ->execute([trim($_POST['codigo']), $_POST['categoria_id'], $_POST['marca'], $_POST['modelo'], $_POST['descripcion'], $_POST['precio'], (int)$_POST['stock_minimo'], isset($_POST['servicio']) ? 0 : 1]);
            flash('Producto creado. Registra una entrada para cargar existencias.');
        } else {
            $c = (int)$_POST['cantidad'];
            if ($_POST['tipo'] === 'ajuste' && ($_POST['signo'] ?? '+') === '-') $c = -$c;
            $pdo->prepare('INSERT INTO movimientos_inventario (producto_id,tipo,cantidad,usuario_id,motivo) VALUES (?,?,?,?,?)')
                ->execute([$_POST['producto_id'], $_POST['tipo'], $c, $_SESSION['u']['id'], $_POST['motivo']]);
            flash('Movimiento registrado.');
        }
    } catch (PDOException $e) { flash($e->getCode() == 23000 ? 'Código de producto repetido.' : err($e), 'error'); }
    header('Location: productos.php'); exit;
}
top('productos', 'Productos e inventario');
$cats = $pdo->query('SELECT * FROM categorias')->fetchAll();
$prods = $pdo->query('SELECT p.*, c.nombre cat FROM productos p JOIN categorias c ON c.id=p.categoria_id WHERE p.activo=1 ORDER BY p.id DESC')->fetchAll();
?>
<div class="card"><h3>Nuevo producto</h3><form method="post" class="row"><input type="hidden" name="accion" value="producto">
<input name="codigo" placeholder="Código" required><select name="categoria_id"><?php foreach ($cats as $c) echo '<option value="' . $c['id'] . '">' . h($c['nombre']) . '</option>'; ?></select>
<input name="marca" placeholder="Marca"><input name="modelo" placeholder="Modelo"><input name="descripcion" placeholder="Descripción">
<input type="number" step="0.01" min="0" name="precio" placeholder="Precio" required><input type="number" min="0" name="stock_minimo" placeholder="Stock mínimo" value="2">
<label><input type="checkbox" name="servicio"> Es servicio (sin stock)</label><button>Crear</button></form></div>
<div class="card"><h3>Movimiento de inventario</h3><form method="post" class="row"><input type="hidden" name="accion" value="mov">
<select name="producto_id"><?php foreach ($prods as $p) if ($p['controla_stock']) echo '<option value="' . $p['id'] . '">' . h($p['codigo'] . ' - ' . $p['marca'] . ' ' . $p['modelo']) . '</option>'; ?></select>
<select name="tipo"><option value="entrada">Entrada</option><option value="salida">Salida</option><option value="ajuste">Ajuste</option></select>
<select name="signo" title="Solo para ajustes"><option value="+">+ (ajuste suma)</option><option value="-">− (ajuste resta)</option></select>
<input type="number" min="1" name="cantidad" placeholder="Cantidad" required><input name="motivo" placeholder="Motivo"><button>Registrar</button></form></div>
<div class="card"><h3>Catálogo</h3><table><tr><th>Código</th><th>Categoría</th><th>Producto</th><th>Precio</th><th>Existencias</th><th>Mínimo</th></tr>
<?php foreach ($prods as $p): $bajo = $p['controla_stock'] && $p['existencias'] <= $p['stock_minimo']; ?>
<tr><td><?= h($p['codigo']) ?></td><td><?= h($p['cat']) ?></td><td><?= h($p['marca'] . ' ' . $p['modelo']) ?></td><td><?= money($p['precio']) ?></td>
<td class="<?= $bajo ? 'bajo' : '' ?>"><?= $p['controla_stock'] ? $p['existencias'] . ($p['existencias'] == 0 ? ' (agotado)' : ($bajo ? ' (bajo)' : '')) : '—' ?></td><td><?= $p['controla_stock'] ? $p['stock_minimo'] : '—' ?></td></tr>
<?php endforeach; ?></table></div>
<?php bottom();
