<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$tipo = $_POST['tipo'] ?? '';
$monto = floatval($_POST['monto'] ?? 0);
$fecha = $_POST['fecha'] ?? '';
$id_categoria = intval($_POST['id_categoria'] ?? 0);
$descripcion = trim($_POST['descripcion'] ?? '');
$metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
$recurrente = isset($_POST['recurrente']) ? 1 : 0;

if (empty($tipo) || empty($fecha) || empty($id_categoria)) {
    header('Location: crear.php?error=campos_vacios');
    exit;
}

if ($monto <= 0) {
    header('Location: crear.php?error=monto_invalido');
    exit;
}

if (!in_array($tipo, ['ingreso', 'gasto'])) {
    header('Location: crear.php?error=campos_vacios');
    exit;
}

// Verificar que la categoría pertenece al usuario
$stmt = $pdo->prepare("SELECT id_categoria FROM categoria WHERE id_categoria = ? AND id_usuario = ?");
$stmt->execute([$id_categoria, $usuario['id']]);
if (!$stmt->fetch()) {
    header('Location: crear.php?error=campos_vacios');
    exit;
}

$stmt = $pdo->prepare("INSERT INTO transaccion 
    (id_usuario, id_categoria, monto, tipo, descripcion, fecha, metodo_pago, recurrente) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $usuario['id'],
    $id_categoria,
    $monto,
    $tipo,
    $descripcion,
    $fecha,
    $metodo_pago,
    $recurrente
]);

header('Location: index.php?msg=creado');
exit;
?>