<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/GastoCompartidoController.php';

$usuario = usuarioActual();
$controller = new GastoCompartidoController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'crear':                $controller->crear(); break;
    case 'guardar':              $controller->guardar(); break;
    case 'ver':                  $controller->ver(); break;
    case 'eliminar':             $controller->eliminar(); break;
    case 'marcarPagado':         $controller->marcarPagado(); break;
    
    case 'contactos':            $controller->contactos(); break;
    case 'crearContacto':        $controller->crearContacto(); break;
    case 'guardarContacto':      $controller->guardarContacto(); break;
    case 'editarContacto':       $controller->editarContacto(); break;
    case 'actualizarContacto':   $controller->actualizarContacto(); break;
    case 'eliminarContacto':     $controller->eliminarContacto(); break;
    
    case 'index':
    default:                     $controller->index(); break;
}