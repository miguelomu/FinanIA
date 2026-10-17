<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereRol('premium');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/InversionController.php';

$usuario = usuarioActual();
$controller = new InversionController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'productosAPI': $controller->productosTangiblesAPI(); break;
    case 'index':
    default:             $controller->index(); break;
}