<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Verificar que no tenga transacciones asociadas
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM transaccion WHERE id_categoria = ?");
$stmt->execute([$id]);
$transacciones = $stmt->fetch()['total'];

if ($transacciones > 0) {
    header('Location: index.php?error=con_transacciones');
    exit;
}

// Verificar que no esté en presupuestos
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM detalle_presupuesto WHERE id_categoria = ?");
$stmt->execute([$id]);
$presupuestos = $stmt->fetch()['total'];

if ($presupuestos > 0) {
    header('Location: index.php?error=con_transacciones');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM categoria WHERE id_categoria = ? AND id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);

header('Location: index.php?msg=eliminado');
exit;
?>