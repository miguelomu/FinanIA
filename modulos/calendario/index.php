<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/CalendarioController.php';

$usuario = usuarioActual();
$controller = new CalendarioController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'dia': $controller->dia(); break;
    case 'index':
    default:    $controller->index(); break;
}