<?php
// Ejecutar UNA vez: http://localhost/optica/setup.php  (luego borrar este archivo)
require 'config.php';
if ($pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() > 0) die('Ya existen usuarios. Borra este archivo.');
$ins = $pdo->prepare('INSERT INTO usuarios (rol_id,nombre,correo,password_hash) VALUES (?,?,?,?)');
$demo = [[1,'Administrador','admin@optica.com'],[2,'Optómetra Demo','optometra@optica.com'],[3,'Vendedor Demo','vendedor@optica.com'],[4,'Auxiliar Demo','auxiliar@optica.com']];
foreach ($demo as $d) $ins->execute([$d[0], $d[1], $d[2], password_hash('123456', PASSWORD_DEFAULT)]);
echo 'Usuarios creados (contraseña de todos: 123456): admin@optica.com, optometra@optica.com, vendedor@optica.com, auxiliar@optica.com.<br><b>Borra setup.php.</b> <a href="login.php">Ir al login</a>';
