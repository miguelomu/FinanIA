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

$stmt = $pdo->prepare("DELETE FROM presupuesto WHERE id_presupuesto = ? AND id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);

header('Location: index.php?msg=eliminado');
exit;
?>