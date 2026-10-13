<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT id_meta FROM aporte_meta WHERE id_aporte = ? AND id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
$aporte = $stmt->fetch();

if (!$aporte) {
    header('Location: index.php');
    exit;
}

$id_meta = $aporte['id_meta'];

$stmt = $pdo->prepare("DELETE FROM aporte_meta WHERE id_aporte = ?");
$stmt->execute([$id]);

// Recalcular: monto_actual = monto_inicial + SUM(aportes restantes)
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

header("Location: ver.php?id=$id_meta&msg=eliminado");
exit;
?>