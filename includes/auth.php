<?php
session_start();

// Cargar helpers automáticamente
require_once __DIR__ . '/../app/bootstrap.php';

function estaLogueado() {
    return isset($_SESSION['id_usuario']);
}

function requiereLogin() {
    if (!estaLogueado()) {
        header('Location: /FinanIA/auth/login.php');
        exit;
    }
}

function requiereRol($roles) {
    requiereLogin();
    if (!in_array($_SESSION['rol'], (array)$roles)) {
        header('Location: /FinanIA/dashboards/' . $_SESSION['rol'] . '.php');
        exit;
    }
}

function usuarioActual() {
    // Cargar tema desde la BD la primera vez
    if (!isset($_SESSION['tema']) && isset($_SESSION['id_usuario'])) {
        global $pdo;
        if (isset($pdo)) {
            $stmt = $pdo->prepare("SELECT tema FROM usuario WHERE id_usuario = ?");
            $stmt->execute([$_SESSION['id_usuario']]);
            $t = $stmt->fetch();
            $_SESSION['tema'] = $t['tema'] ?? 'oscuro';
        }
    }
    
    return [
        'id' => $_SESSION['id_usuario'] ?? null,
        'nombre' => $_SESSION['nombre'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'rol' => $_SESSION['rol'] ?? null,
        'tema' => $_SESSION['tema'] ?? 'oscuro'
    ];
}
?>