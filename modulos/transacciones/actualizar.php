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
$tipo = $_POST['tipo'] ?? '';
$monto = floatval($_POST['monto'] ?? 0);
$fecha = $_POST['fecha'] ?? '';
$id_categoria = intval($_POST['id_categoria'] ?? 0);
$descripcion = trim($_POST['descripcion'] ?? '');
$metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
$recurrente = isset($_POST['recurrente']) ? 1 : 0;

if (empty($tipo) || empty($fecha) || empty($id_categoria) || $monto <= 0) {
    header("Location: editar.php?id=$id&error=campos_vacios");
    exit;
}

$stmt = $pdo->prepare("SELECT id_transaccion FROM transaccion WHERE id_transaccion = ? AND id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
if (!$stmt->fetch()) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE transaccion 
    SET tipo = ?, monto = ?, fecha = ?, id_categoria = ?, 
        descripcion = ?, metodo_pago = ?, recurrente = ?
    WHERE id_transaccion = ? AND id_usuario = ?");
$stmt->execute([
    $tipo, $monto, $fecha, $id_categoria,
    $descripcion, $metodo_pago, $recurrente,
    $id, $usuario['id']
]);

header('Location: index.php?msg=actualizado');
exit;
?>