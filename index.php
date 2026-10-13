<?php
    require_once 'includes/auth.php';

    if (estaLogueado()) {
        $rol = $_SESSION['rol'];
        $dashboard = $rol === 'invitado' ? 'registrado' : $rol;
        header("Location: dashboards/{$dashboard}.php");
        exit;
    }

    header('Location: dashboards/invitado.php');
    exit;
?>