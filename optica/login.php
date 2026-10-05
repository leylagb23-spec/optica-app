<?php
require 'config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = $pdo->prepare('SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.correo=? AND u.activo=1');
    $s->execute([trim($_POST['correo'] ?? '')]);
    $u = $s->fetch();
    if (!$u || !password_verify($_POST['password'] ?? '', $u['password_hash'])) $error = 'Correo o contraseña incorrectos.';
    elseif (!isset(PERMISOS[$u['rol']])) $error = 'Tu rol aún no tiene acceso (el portal del cliente es Fase 3).';
    else { session_regenerate_id(true); $_SESSION['u'] = ['id'=>$u['id'],'nombre'=>$u['nombre'],'rol'=>$u['rol']]; header('Location: index.php'); exit; }
}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><title>Ingresar - Óptica</title>
<style>body{font-family:Arial;background:#1F4E78;display:flex;justify-content:center;align-items:center;height:100vh;margin:0}
form{background:#fff;padding:30px;border-radius:8px;width:300px}input{width:100%;padding:9px;margin:6px 0 12px;box-sizing:border-box}
button{width:100%;padding:10px;background:#1F4E78;color:#fff;border:0;border-radius:4px}.e{color:#c0392b;margin-bottom:10px}</style></head>
<body><form method="post"><h2>Óptica</h2><?php if ($error) echo '<div class="e">' . h($error) . '</div>'; ?>
<label>Correo</label><input type="email" name="correo" required><label>Contraseña</label><input type="password" name="password" required>
<button>Ingresar</button></form></body></html>
