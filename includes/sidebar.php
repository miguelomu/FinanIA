<?php
$usuario = usuarioActual();
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <h2>💰 Gestor</h2>
  <nav>
    <a href="/FinanIA/dashboards/<?= $usuario['rol'] ?>.php" class="<?= $paginaActual === $usuario['rol'] . '.php' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="/FinanIA/modulos/cuentas/index.php">🏦 Cuentas</a>
    <a href="/FinanIA/modulos/transacciones/index.php" class="<?= $paginaActual === 'index.php' && strpos($_SERVER['PHP_SELF'], 'transacciones') ? 'active' : '' ?>">💸 Transacciones</a>
    <a href="/FinanIA/modulos/presupuestos/index.php">📅 Presupuestos</a>
    <a href="/FinanIA/modulos/metas/index.php">🎯 Metas</a>
    <a href="/FinanIA/modulos/deudas/index.php">💳 Deudas</a>
    <a href="/FinanIA/modulos/gastos_compartidos/index.php">👥 Gastos Compartidos</a>
    <a href="/FinanIA/modulos/simulador/index.php">🏔️ Simulador de Deudas</a>
    <a href="/FinanIA/modulos/categorias/index.php">🏷️ Categorías</a>
    <a href="/FinanIA/modulos/reportes/index.php">📈 Reportes</a>
    <a href="/FinanIA/modulos/configuracion/index.php">⚙️ Configuración</a>
    <?php if ($usuario['rol'] === 'premium'): ?>
      <a href="#">🤖 Chatbot IA</a>
      <a href="#">🔍 Gastos Hormiga</a>
    <?php endif; ?>
    <hr style="border-color:rgba(212,175,55,0.2); margin:15px 0;">
    <a href="/FinanIA/auth/logout.php" style="color:#e74c3c;">🚪 Cerrar Sesión</a>
  </nav>
</aside>