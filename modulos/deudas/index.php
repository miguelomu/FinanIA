<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/bootstrap.php'; 
require_once __DIR__ . '/../../app/controllers/DeudaController.php';

$usuario = usuarioActual();
$controller = new DeudaController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'crear':           $controller->crear(); break;
    case 'guardar':         $controller->guardar(); break;
    case 'editar':          $controller->editar(); break;
    case 'actualizar':      $controller->actualizar(); break;
    case 'eliminar':        $controller->eliminar(); break;
    case 'ver':             $controller->ver(); break;
    case 'pagar':           $controller->pagar(); break;
    case 'editarPago':      $controller->editarPago(); break;
    case 'actualizarPago':  $controller->actualizarPago(); break;
    case 'eliminarPago':    $controller->eliminarPago(); break;
    case 'index':
    default:                $controller->index(); break;
}