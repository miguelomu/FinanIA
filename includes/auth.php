<?php
session_start();

function estaLogueado() {
    return isset($_SESSION['id_usuario']);
}

function requiereLogin() {
    if (!estaLogueado()) {
        header('Location: /gestor-financiero/auth/login.php');
        exit;
    }
}

function requiereRol($roles) {
    requiereLogin();
    if (!in_array($_SESSION['rol'], (array)$roles)) {
        header('Location: /gestor-financiero/dashboards/' . $_SESSION['rol'] . '.php');
        exit;
    }
}

function usuarioActual() {
    return [
        'id' => $_SESSION['id_usuario'] ?? null,
        'nombre' => $_SESSION['nombre'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'rol' => $_SESSION['rol'] ?? null
    ];
}
?>