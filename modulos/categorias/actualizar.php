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
$tipo = $_POST['tipo'] ?? '';
$color = $_POST['color'] ?? '#3498db';
$icono = trim($_POST['icono'] ?? '');

if (empty($nombre) || empty($tipo)) {
    header("Location: editar.php?id=$id&error=campos_vacios");
    exit;
}

if (!in_array($tipo, ['ingreso', 'gasto'])) {
    header("Location: editar.php?id=$id&error=campos_vacios");
    exit;
}

// Verificar nombre único (excepto el mismo)
$stmt = $pdo->prepare("SELECT id_categoria FROM categoria WHERE id_usuario = ? AND nombre = ? AND id_categoria != ?");
$stmt->execute([$usuario['id'], $nombre, $id]);
if ($stmt->fetch()) {
    header("Location: editar.php?id=$id&error=nombre_existe");
    exit;
}

$stmt = $pdo->prepare("UPDATE categoria SET nombre = ?, tipo = ?, color = ?, icono = ? WHERE id_categoria = ? AND id_usuario = ?");
$stmt->execute([$nombre, $tipo, $color, $icono, $id, $usuario['id']]);

header('Location: index.php?msg=actualizado');
exit;
?>