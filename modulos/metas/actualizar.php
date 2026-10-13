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
$nombre = trim($_POST['nombre'] ?? '');
$monto_objetivo = floatval($_POST['monto_objetivo'] ?? 0);
$monto_actual = floatval($_POST['monto_actual'] ?? 0);
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_limite = $_POST['fecha_limite'] ?? '';
$estado = $_POST['estado'] ?? 'activa';

if (empty($nombre) || empty($fecha_inicio) || empty($fecha_limite) || $monto_objetivo <= 0) {
    header("Location: editar.php?id=$id&error=campos_vacios");
    exit;
}

if (!in_array($estado, ['activa', 'completada', 'cancelada'])) {
    $estado = 'activa';
}

// Calcular total aportes de la tabla
$stmt = $pdo->prepare("SELECT COALESCE(SUM(monto),0) AS total FROM aporte_meta WHERE id_meta = ?");
$stmt->execute([$id]);
$totalAportes = $stmt->fetch()['total'];

// El monto_actual que ingresa el usuario se interpreta como "monto_inicial"
$montoInicial = $monto_actual;
$montoFinal = $montoInicial + $totalAportes;

if ($montoFinal >= $monto_objetivo && $estado === 'activa') {
    $estado = 'completada';
}

$stmt = $pdo->prepare("UPDATE meta 
    SET nombre = ?, monto_objetivo = ?, monto_inicial = ?, monto_actual = ?, 
        fecha_inicio = ?, fecha_limite = ?, estado = ?
    WHERE id_meta = ? AND id_usuario = ?");
$stmt->execute([
    $nombre, $monto_objetivo, $montoInicial, $montoFinal,
    $fecha_inicio, $fecha_limite, $estado,
    $id, $usuario['id']
]);

header('Location: index.php?msg=actualizado');
exit;
?>