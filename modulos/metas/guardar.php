<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$monto_objetivo = floatval($_POST['monto_objetivo'] ?? 0);
$monto_actual = floatval($_POST['monto_actual'] ?? 0);
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_limite = $_POST['fecha_limite'] ?? '';

if (empty($nombre) || empty($fecha_inicio) || empty($fecha_limite)) {
    header('Location: crear.php?error=campos_vacios');
    exit;
}

if ($monto_objetivo <= 0) {
    header('Location: crear.php?error=monto_invalido');
    exit;
}

if (strtotime($fecha_limite) <= strtotime($fecha_inicio)) {
    header('Location: crear.php?error=fecha_invalida');
    exit;
}

// El monto_actual se guarda como monto_inicial
// Y también se guarda como monto_actual (porque aún no hay aportes en la tabla)
$stmt = $pdo->prepare("INSERT INTO meta 
    (id_usuario, nombre, monto_objetivo, monto_inicial, monto_actual, fecha_inicio, fecha_limite, estado) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 'activa')");
$stmt->execute([
    $usuario['id'],
    $nombre,
    $monto_objetivo,
    $monto_actual,   // monto_inicial
    $monto_actual,   // monto_actual (mismo valor al inicio)
    $fecha_inicio,
    $fecha_limite
]);

header('Location: index.php?msg=creado');
exit;
?>