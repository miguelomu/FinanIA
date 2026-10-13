<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/ReporteController.php';

$usuario = usuarioActual();
$controller = new ReporteController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'index':
    default:
        $controller->index();
        break;
}