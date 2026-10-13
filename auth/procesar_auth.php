<?php
require_once '../config/database.php';
require_once '../app/bootstrap.php';
session_start();

$accion = $_POST['accion'] ?? '';

// Cargar helpers (por si acaso no se cargaron aún)
require_once __DIR__ . '/../app/bootstrap.php';

// Validar CSRF
if (!validarCsrf()) {
    header('Location: login.php?error=csrf');
    exit;
}

$accion = $_POST['accion'] ?? '';

// ============================================
// REGISTRO
// ============================================
if ($accion === 'registro') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        header('Location: registro.php?error=campos_vacios');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: registro.php?error=email_invalido');
        exit;
    }

    if ($password !== $confirm) {
        header('Location: registro.php?error=passwords_no_coinciden');
        exit;
    }

    if (strlen($password) < 6) {
        header('Location: registro.php?error=password_corta');
        exit;
    }

    // Verificar email único
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: registro.php?error=email_existe');
        exit;
    }

    // Crear usuario
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuario (nombre, email, password_hash, rol) VALUES (?, ?, ?, 'registrado')");
    $stmt->execute([$nombre, $email, $hash]);

    header('Location: login.php?success=registro_exitoso');
    exit;
}

// ============================================
// LOGIN
// ============================================
if ($accion === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ? AND estado = 'activo'");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
        header('Location: login.php?error=credenciales_invalidas');
        exit;
    }

    // Guardar sesión
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['rol'] = $usuario['rol'];

    // Actualizar último acceso
    $stmt = $pdo->prepare("UPDATE usuario SET ultimo_acceso = NOW() WHERE id_usuario = ?");
    $stmt->execute([$usuario['id_usuario']]);

    // Redirigir según rol
    $dashboard = $usuario['rol'] === 'invitado' ? 'registrado' : $usuario['rol'];
    header("Location: ../dashboards/{$dashboard}.php");
    exit;
}
?>