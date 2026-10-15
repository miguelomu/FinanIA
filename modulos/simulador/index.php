<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereRol('premium');  
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/SimuladorController.php';

$usuario = usuarioActual();
$controller = new SimuladorController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'simular':
        $controller->simular();
        break;
    case 'index':
    default:
        $controller->index();
        break;
}