<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/LogroController.php';

$usuario = usuarioActual();
$controller = new LogroController($pdo, $usuario['id']);
$controller->index();