<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = intval($_POST['id'] ?? 0);
$acreedor = trim($_POST['acreedor'] ?? '');
$monto_total = floatval($_POST['monto_total'] ?? 0);
$monto_pagado = floatval($_POST['monto_pagado'] ?? 0);
$tasa_interes = floatval($_POST['tasa_interes'] ?? 0);
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
$pago_minimo = floatval($_POST['pago_minimo'] ?? 0);
$estado = $_POST['estado'] ?? 'activa';

if (empty($acreedor) || empty($fecha_inicio) || empty($fecha_vencimiento) || $monto_total <= 0) {
    header("Location: editar.php?id=$id&error=campos_vacios");
    exit;
}

if (!in_array($estado, ['activa', 'pagada', 'vencida'])) {
    $estado = 'activa';
}

// Si se pagó todo, marcar como pagada
if ($monto_pagado >= $monto_total && $estado === 'activa') {
    $estado = 'pagada';
}

$stmt = $pdo->prepare("UPDATE deuda 
    SET acreedor = ?, monto_total = ?, monto_inicial = ?, monto_pagado = ?, 
        tasa_interes = ?, fecha_inicio = ?, fecha_vencimiento = ?, 
        pago_minimo = ?, estado = ?
    WHERE id_deuda = ? AND id_usuario = ?");
$stmt->execute([
    $acreedor, $monto_total, $monto_total, $monto_pagado,
    $tasa_interes, $fecha_inicio, $fecha_vencimiento,
    $pago_minimo, $estado,
    $id, $usuario['id']
]);

header('Location: index.php?msg=actualizado');
exit;
?>