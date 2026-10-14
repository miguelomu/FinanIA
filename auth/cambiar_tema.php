<?php
require_once __DIR__ . '/../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../config/database.php';

$tema = $_POST['tema'] ?? 'oscuro';
if (!in_array($tema, ['oscuro', 'claro'])) $tema = 'oscuro';

$_SESSION['tema'] = $tema;

$stmt = $pdo->prepare("UPDATE usuario SET tema = ? WHERE id_usuario = ?");
$stmt->execute([$tema, $_SESSION['id_usuario']]);

echo json_encode(['success' => true]);