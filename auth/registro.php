<?php 
require_once '../includes/auth.php';
require_once '../app/bootstrap.php';   // ← AGREGAR ESTA LÍNEA
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro - Gestor Financiero</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
  <div class="auth-container">
    <div class="auth-card">
      <h1>💰 Crear Cuenta</h1>
      <p class="auth-subtitle">Regístrate gratis</p>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
          <?php
          $errores = [
            'campos_vacios' => 'Todos los campos son obligatorios',
            'email_invalido' => 'El email no es válido',
            'passwords_no_coinciden' => 'Las contraseñas no coinciden',
            'password_corta' => 'La contraseña debe tener al menos 6 caracteres',
            'email_existe' => 'Este email ya está registrado'
          ];
          echo $errores[$_GET['error']] ?? 'Error desconocido';
          ?>
        </div>
      <?php endif; ?>

      <form action="procesar_auth.php" method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="accion" value="registro">
        <div class="form-group">
          <label>Nombre Completo</label>
          <input type="text" name="nombre" required>
        </div>
        <div class="form-group">
          <label>Correo Electrónico</label>
          <input type="email" name="email" required>
        </div>
        <div class="form-group">
          <label>Contraseña</label>
          <input type="password" name="password" required minlength="6">
        </div>
        <div class="form-group">
          <label>Confirmar Contraseña</label>
          <input type="password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
      </form>

      <p class="auth-link">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
      </p>
    </div>
  </div>
</body>
</html>