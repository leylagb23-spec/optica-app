<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=optica;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
const ESTADOS = ['Orden creada','Enviada al laboratorio','En producción','Recibida','Control de calidad','Lista para entrega','Entregada'];
const PERMISOS = [
    'Administrador' => ['index','clientes','productos','ventas','ordenes','usuarios'],
    'Optómetra'     => ['index','clientes'],
    'Vendedor'      => ['index','clientes','ventas'],
    'Auxiliar'      => ['index','productos','ventas','ordenes'],
];
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return '$' . number_format((float)$n, 0, ',', '.'); }
function flash($m, $tipo = 'ok') { $_SESSION['flash'] = [$m, $tipo]; }
function err($e) { $m = $e->getMessage(); return ($p = strrpos($m, ': ')) !== false ? substr($m, $p + 2) : $m; }
function requiere($mod) {
    if (empty($_SESSION['u'])) { header('Location: login.php'); exit; }
    if (!in_array($mod, PERMISOS[$_SESSION['u']['rol']] ?? [])) { http_response_code(403); die('Sin permiso para este módulo.'); }
}
function top($mod, $titulo) {
    $u = $_SESSION['u'];
    $items = ['index'=>'Dashboard','clientes'=>'Clientes','productos'=>'Productos e inventario','ventas'=>'Ventas','ordenes'=>'Órdenes','usuarios'=>'Usuarios'];
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . h($titulo) . ' - Óptica</title><style>
    body{font-family:Arial,sans-serif;margin:0;background:#f4f6f9;color:#222}
    nav{background:#1F4E78;padding:10px 20px;display:flex;gap:6px;flex-wrap:wrap;align-items:center}
    nav a{color:#fff;text-decoration:none;padding:6px 12px;border-radius:4px;font-size:14px}
    nav a.on,nav a:hover{background:#2d6aa3} nav span{margin-left:auto;color:#cfe0f0;font-size:13px}
    main{max-width:1100px;margin:20px auto;padding:0 16px} h1{font-size:22px;color:#1F4E78}
    .card{background:#fff;border-radius:6px;padding:16px;margin-bottom:16px;box-shadow:0 1px 3px #0002}
    table{width:100%;border-collapse:collapse;font-size:14px} th{background:#1F4E78;color:#fff;text-align:left;padding:8px} td{padding:7px 8px;border-bottom:1px solid #e3e3e3}
    input,select,textarea{padding:7px;border:1px solid #bbb;border-radius:4px;font-size:14px;margin:2px 0}
    button{background:#1F4E78;color:#fff;border:0;padding:8px 14px;border-radius:4px;cursor:pointer;font-size:14px} button.sec{background:#777}
    .ok{background:#e1f4e3;padding:10px;border-radius:4px;margin-bottom:12px} .error{background:#fde2e2;padding:10px;border-radius:4px;margin-bottom:12px}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px} .kpi b{font-size:26px;color:#1F4E78;display:block}
    .bajo{color:#c0392b;font-weight:bold} .row{display:flex;gap:8px;flex-wrap:wrap;align-items:end}
    </style></head><body><nav>';
    foreach ($items as $k => $v) if (in_array($k, PERMISOS[$u['rol']])) echo '<a href="' . $k . '.php"' . ($k == $mod ? ' class="on"' : '') . '>' . $v . '</a>';
    echo '<span>' . h($u['nombre']) . ' (' . h($u['rol']) . ') · <a href="logout.php">Salir</a></span></nav><main><h1>' . h($titulo) . '</h1>';
    if (!empty($_SESSION['flash'])) { [$m, $t] = $_SESSION['flash']; echo '<div class="' . $t . '">' . h($m) . '</div>'; unset($_SESSION['flash']); }
}
function bottom() { echo '</main></body></html>'; }
