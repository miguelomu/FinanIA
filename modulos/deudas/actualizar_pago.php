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
$monto = floatval($_POST['monto'] ?? 0);
$nota = trim($_POST['nota'] ?? '');

if ($monto <= 0) {
    header("Location: editar_pago.php?id=$id&error=monto_invalido");
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, d.monto_total, d.monto_pagado 
    FROM pago_deuda p 
    INNER JOIN deuda d ON p.id_deuda = d.id_deuda 
    WHERE p.id_pago = ? AND p.id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
$pago = $stmt->fetch();

if (!$pago) {
    header('Location: index.php');
    exit;
}

// Calcular diferencia para ajustar monto_pagado
$diferencia = $monto - $pago['monto'];
$nuevoPagado = $pago['monto_pagado'] + $diferencia;
$nuevoEstado = $nuevoPagado >= $pago['monto_total'] ? 'pagada' : 'activa';

$pdo->beginTransaction();

$stmt = $pdo->prepare("UPDATE pago_deuda SET monto = ?, nota = ? WHERE id_pago = ?");
$stmt->execute([$monto, $nota, $id]);

$stmt = $pdo->prepare("UPDATE deuda SET monto_pagado = ?, estado = ? WHERE id_deuda = ?");
$stmt->execute([$nuevoPagado, $nuevoEstado, $pago['id_deuda']]);

$pdo->commit();

header("Location: ver.php?id={$pago['id_deuda']}&msg=editado");
exit;
?>