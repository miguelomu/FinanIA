<?php
/**
 * Punto de entrada del módulo de Metas
 */

require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/MetaController.php';

$usuario = usuarioActual();
$controller = new MetaController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'crear':            $controller->crear(); break;
    case 'guardar':          $controller->guardar(); break;
    case 'editar':           $controller->editar(); break;
    case 'actualizar':       $controller->actualizar(); break;
    case 'eliminar':         $controller->eliminar(); break;
    case 'ver':              $controller->ver(); break;
    case 'aportar':          $controller->aportar(); break;
    case 'editarAporte':     $controller->editarAporte(); break;
    case 'actualizarAporte': $controller->actualizarAporte(); break;
    case 'eliminarAporte':   $controller->eliminarAporte(); break;
    case 'index':
    default:                 $controller->index(); break;
}