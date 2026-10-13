<?php
require_once '../includes/auth.php';

if (estaLogueado()) {
    header('Location: ' . $_SESSION['rol'] . '.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Bienvenido - Gestor Financiero</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
  <div class="auth-container" style="max-width:700px;">
    <div class="auth-card">
      <h1>💰 Gestor Inteligente Financiero</h1>
      <p class="auth-subtitle">Tu asistente personal para administrar tus finanzas</p>

      <div style="margin:30px 0;">
        <h3 style="color:#d4af37; margin-bottom:15px;">✨ ¿Qué puedes hacer?</h3>
        <ul style="color:#c9d6e3; line-height:2; padding-left:20px;">
          <li>Registrar y controlar ingresos y gastos</li>
          <li>Crear presupuestos mensuales</li>
          <li>Establecer metas de ahorro</li>
          <li>Gestionar tus deudas</li>
          <li>Generar reportes financieros</li>
          <li>Recibir asesoría con IA (Premium)</li>
        </ul>
      </div>

      <div style="display:flex; gap:15px;">
        <a href="../auth/registro.php" class="btn btn-primary" style="flex:1; text-align:center;">Crear Cuenta Gratis</a>
        <a href="../auth/login.php" class="btn btn-secondary" style="flex:1; text-align:center;">Iniciar Sesión</a>
      </div>
    </div>
  </div>
</body>
</html>