<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/CuentaController.php';

$usuario = usuarioActual();
$controller = new CuentaController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'crear':      $controller->crear(); break;
    case 'guardar':    $controller->guardar(); break;
    case 'editar':     $controller->editar(); break;
    case 'actualizar': $controller->actualizar(); break;
    case 'eliminar':   $controller->eliminar(); break;
    case 'index':
    default:           $controller->index(); break;
}