<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereRol('premium'); 
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/ConfiguracionController.php';

$usuario = usuarioActual();
$controller = new ConfiguracionController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'actualizar': $controller->actualizar(); break;
    case 'index':
    default:           $controller->index(); break;
}