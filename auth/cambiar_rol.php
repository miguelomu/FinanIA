<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/dev.php';
requiereLogin();
require_once __DIR__ . '/../config/database.php';

$usuario = usuarioActual();

// Solo el desarrollador puede cambiar de rol
if (!esDesarrollador()) {
    header('Location: /FinanIA/dashboards/' . $usuario['rol'] . '.php');
    exit;
}

// Determinar el nuevo rol (toggle)
$nuevoRol = $usuario['rol'] === 'premium' ? 'registrado' : 'premium';

// Actualizar en BD
$stmt = $pdo->prepare("UPDATE usuario SET rol = ? WHERE id_usuario = ?");
$stmt->execute([$nuevoRol, $usuario['id']]);

// Actualizar sesión
$_SESSION['rol'] = $nuevoRol;

// Redirigir al dashboard correspondiente
header("Location: /FinanIA/dashboards/{$nuevoRol}.php?msg=cambio_rol");
exit;