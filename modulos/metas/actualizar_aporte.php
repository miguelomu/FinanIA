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
    header("Location: editar_aporte.php?id=$id&error=monto_invalido");
    exit;
}

$stmt = $pdo->prepare("SELECT id_meta FROM aporte_meta WHERE id_aporte = ? AND id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
$aporte = $stmt->fetch();

if (!$aporte) {
    header('Location: index.php');
    exit;
}

$id_meta = $aporte['id_meta'];

$stmt = $pdo->prepare("UPDATE aporte_meta SET monto = ?, nota = ? WHERE id_aporte = ?");
$stmt->execute([$monto, $nota, $id]);

// Recalcular: monto_actual = monto_inicial + SUM(aportes)
$stmt = $pdo->prepare("SELECT monto_inicial, monto_objetivo FROM meta WHERE id_meta = ?");
$stmt->execute([$id_meta]);
$meta = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(monto),0) AS total FROM aporte_meta WHERE id_meta = ?");
$stmt->execute([$id_meta]);
$totalAportes = $stmt->fetch()['total'];

$montoFinal = $meta['monto_inicial'] + $totalAportes;
$nuevoEstado = $montoFinal >= $meta['monto_objetivo'] ? 'completada' : 'activa';

$stmt = $pdo->prepare("UPDATE meta SET monto_actual = ?, estado = ? WHERE id_meta = ?");
$stmt->execute([$montoFinal, $nuevoEstado, $id_meta]);

header("Location: ver.php?id=$id_meta&msg=editado");
exit;
?>