<?php
/**
 * Bootstrap: Carga todos los helpers del proyecto.
 * 
 * Este archivo se incluye una sola vez (desde header.php)
 * y deja disponibles todas las funciones helper en cualquier
 * parte del sistema.
 * 
 * Bootstrap: Carga todos los helpers del proyecto.
 */

// Evitar doble carga
if (defined('FINANIA_BOOTSTRAP_CARGADO')) {
    return;
}
define('FINANIA_BOOTSTRAP_CARGADO', true);

require_once __DIR__ . '/helpers/FormatoHelper.php';
require_once __DIR__ . '/helpers/RequestHelper.php';
require_once __DIR__ . '/helpers/TextoHelper.php';
require_once __DIR__ . '/helpers/CsrfHelper.php';   // ← NUEVA LÍNEA
require_once __DIR__ . '/helpers/ToastHelper.php';