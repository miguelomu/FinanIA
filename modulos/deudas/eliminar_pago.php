<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();
$id = intval($_GET['id'] ?? 0);

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

$nuevoPagado = $pago['monto_pagado'] - $pago['monto'];
if ($nuevoPagado < 0) $nuevoPagado = 0;
$nuevoEstado = $nuevoPagado >= $pago['monto_total'] ? 'pagada' : 'activa';

$pdo->beginTransaction();

$stmt = $pdo->prepare("DELETE FROM pago_deuda WHERE id_pago = ?");
$stmt->execute([$id]);

$stmt = $pdo->prepare("UPDATE deuda SET monto_pagado = ?, estado = ? WHERE id_deuda = ?");
$stmt->execute([$nuevoPagado, $nuevoEstado, $pago['id_deuda']]);

$pdo->commit();

header("Location: ver.php?id={$pago['id_deuda']}&msg=eliminado");
exit;
?>