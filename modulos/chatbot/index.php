<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/ChatbotController.php';

$usuario = usuarioActual();
$controller = new ChatbotController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'enviar':  $controller->enviar(); break;
    case 'limpiar': $controller->limpiar(); break;
    case 'index':
    default:        $controller->index(); break;
}