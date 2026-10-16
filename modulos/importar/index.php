<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/ImportarController.php';

$usuario = usuarioActual();
$controller = new ImportarController($pdo, $usuario['id']);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'preview':  $controller->preview(); break;
    case 'importar': $controller->importar(); break;
    case 'index':
    default:         $controller->index(); break;
}