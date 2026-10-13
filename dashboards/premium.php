<?php
require_once __DIR__ . '/../includes/auth.php';
requiereRol('premium');
require_once __DIR__ . '/../config/database.php';

$usuario = usuarioActual();

$stmt = $pdo->prepare("SELECT 
    COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END),0) AS ingresos,
    COALESCE(SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END),0) AS gastos
    FROM transaccion WHERE id_usuario = ?");
$stmt->execute([$usuario['id']]);
$stats = $stmt->fetch();
$balance = $stats['ingresos'] - $stats['gastos'];

include __DIR__ . '/../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>Dashboard Premium 🌟</h1>
      <div>
        <span>Bienvenido, <strong><?= htmlspecialchars($usuario['nombre']) ?></strong></span>
        <span class="badge badge-premium">Premium</span>
      </div>
    </div>

    <div class="cards-grid">
      <div class="card">
        <h3>Ingresos Totales</h3>
        <div class="value positivo">$<?= number_format($stats['ingresos'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Gastos Totales</h3>
        <div class="value negativo">$<?= number_format($stats['gastos'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Balance</h3>
        <div class="value <?= $balance >= 0 ? 'positivo' : 'negativo' ?>">
          $<?= number_format($balance, 2) ?>
        </div>
      </div>
      <div class="card">
        <h3>Acceso IA</h3>
        <div class="value" style="font-size:1.2rem; color:#d5a6ff;">Activo ✅</div>
      </div>
    </div>

    <h2 style="color:#d4af37; margin:30px 0 20px;">Funciones Premium</h2>
    <div class="cards-grid">
      <div class="card">
        <h3>🤖 Chatbot IA</h3>
        <p style="color:#9fb3c8; font-size:0.85rem; margin:10px 0;">Consulta financiera con IA</p>
        <a href="#" class="btn btn-primary btn-block">Abrir Chat</a>
      </div>
      <div class="card">
        <h3>📈 Predicciones</h3>
        <p style="color:#9fb3c8; font-size:0.85rem; margin:10px 0;">Proyección con inflación</p>
        <a href="#" class="btn btn-primary btn-block">Ver Predicción</a>
      </div>
      <div class="card">
        <h3>🔍 Gastos Hormiga</h3>
        <p style="color:#9fb3c8; font-size:0.85rem; margin:10px 0;">Detección automática</p>
        <a href="#" class="btn btn-primary btn-block">Analizar</a>
      </div>
    </div>

    <h2 style="color:#d4af37; margin:30px 0 20px;">Acciones Rápidas</h2>
    <div style="display:flex; gap:15px; flex-wrap:wrap;">
      <a href="/FinanIA/modulos/transacciones/crear.php" class="btn btn-primary">+ Nueva Transacción</a>
      <a href="/FinanIA/modulos/metas/crear.php" class="btn btn-primary">+ + Nueva Meta</a>
      <a href="/FinanIA/modulos/deudas/crear.php" class="btn btn-primary">+ Nueva Deuda</a>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>