<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$tipo = $_POST['tipo'] ?? '';
$color = $_POST['color'] ?? '#3498db';
$icono = trim($_POST['icono'] ?? '');

if (empty($nombre) || empty($tipo)) {
    header('Location: crear.php?error=campos_vacios');
    exit;
}

if (!in_array($tipo, ['ingreso', 'gasto'])) {
    header('Location: crear.php?error=campos_vacios');
    exit;
}

// Verificar nombre único
$stmt = $pdo->prepare("SELECT id_categoria FROM categoria WHERE id_usuario = ? AND nombre = ?");
$stmt->execute([$usuario['id'], $nombre]);
if ($stmt->fetch()) {
    header('Location: crear.php?error=nombre_existe');
    exit;
}

$stmt = $pdo->prepare("INSERT INTO categoria (id_usuario, nombre, tipo, color, icono) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$usuario['id'], $nombre, $tipo, $color, $icono]);

header('Location: index.php?msg=creado');
exit;
?>