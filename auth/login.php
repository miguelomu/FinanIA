<?php require_once '../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login - Gestor Financiero</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
  <div class="auth-container">
    <div class="auth-card">
      <h1>💰 Gestor Financiero</h1>
      <p class="auth-subtitle">Inicia sesión para continuar</p>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
          <?php
          $errores = [
            'credenciales_invalidas' => 'Email o contraseña incorrectos',
            'campos_vacios' => 'Todos los campos son obligatorios'
          ];
          echo $errores[$_GET['error']] ?? 'Error desconocido';
          ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Registro exitoso. Inicia sesión.</div>
      <?php endif; ?>

      <form action="procesar_auth.php" method="POST">
        <input type="hidden" name="accion" value="login">
        <div class="form-group">
          <label>Correo Electrónico</label>
          <input type="email" name="email" required placeholder="usuario@email.com">
        </div>
        <div class="form-group">
          <label>Contraseña</label>
          <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
      </form>

      <p class="auth-link">
        ¿No tienes cuenta? <a href="registro.php">Regístrate</a>
      </p>
    </div>
  </div>
</body>
</html>