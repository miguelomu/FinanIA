<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$acreedor = trim($_POST['acreedor'] ?? '');
$monto_total = floatval($_POST['monto_total'] ?? 0);
$monto_pagado = floatval($_POST['monto_pagado'] ?? 0);
$tasa_interes = floatval($_POST['tasa_interes'] ?? 0);
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
$pago_minimo = floatval($_POST['pago_minimo'] ?? 0);

if (empty($acreedor) || empty($fecha_inicio) || empty($fecha_vencimiento)) {
    header('Location: crear.php?error=campos_vacios');
    exit;
}

if ($monto_total <= 0) {
    header('Location: crear.php?error=monto_invalido');
    exit;
}

if (strtotime($fecha_vencimiento) <= strtotime($fecha_inicio)) {
    header('Location: crear.php?error=fecha_invalida');
    exit;
}

$stmt = $pdo->prepare("INSERT INTO deuda 
    (id_usuario, acreedor, monto_total, monto_inicial, monto_pagado, tasa_interes, 
     fecha_inicio, fecha_vencimiento, pago_minimo, estado) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activa')");
$stmt->execute([
    $usuario['id'],
    $acreedor,
    $monto_total,
    $monto_total,   // monto_inicial
    $monto_pagado,
    $tasa_interes,
    $fecha_inicio,
    $fecha_vencimiento,
    $pago_minimo
]);

header('Location: index.php?msg=creado');
exit;
?>