<?php
/**
 * Punto de entrada del módulo de Categorías
 * 
 * Este archivo recibe la petición del navegador,
 * carga el controlador y ejecuta la acción correspondiente.
 */

require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/CategoriaController.php';

// Obtener el usuario actual
$usuario = usuarioActual();

// Crear el controlador
$controller = new CategoriaController($pdo, $usuario['id']);

// Determinar qué acción ejecutar
$accion = $_GET['accion'] ?? 'index';

// Ejecutar la acción correspondiente
switch ($accion) {
    case 'crear':
        $controller->crear();
        break;
    case 'guardar':
        $controller->guardar();
        break;
    case 'editar':
        $controller->editar();
        break;
    case 'actualizar':
        $controller->actualizar();
        break;
    case 'eliminar':
        $controller->eliminar();
        break;
    case 'index':
    default:
        $controller->index();
        break;
}