<?php
require_once __DIR__ . '/../includes/auth.php';
requiereRol(['registrado', 'premium']);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Cuenta.php';

$usuario = usuarioActual();

// Estadísticas de transacciones
$stmt = $pdo->prepare("SELECT 
    COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END),0) AS ingresos,
    COALESCE(SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END),0) AS gastos
    FROM transaccion WHERE id_usuario = ?");
$stmt->execute([$usuario['id']]);
$stats = $stmt->fetch();
$balance = $stats['ingresos'] - $stats['gastos'];

// Patrimonio
$modelCuenta = new Cuenta($pdo);
$patrimonio = $modelCuenta->calcularPatrimonio($usuario['id']);

// Metas activas
$stmt = $pdo->prepare("SELECT 
    COUNT(*) AS total,
    COALESCE(SUM(monto_actual),0) AS ahorrado,
    COALESCE(SUM(monto_objetivo),0) AS objetivo
    FROM meta WHERE id_usuario = ? AND estado = 'activa'");
$stmt->execute([$usuario['id']]);
$metas = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>Dashboard</h1>
      <div>
        <span>Bienvenido, <strong><?= e($usuario['nombre']) ?></strong></span>
        <span class="badge badge-<?= e($usuario['rol']) ?>"><?= e($usuario['rol']) ?></span>
      </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cambio_rol'): ?>
      <div class="alert alert-success">
        👤 Has vuelto al rol Registrado.
      </div>
    <?php endif; ?>

    <!-- 🏆 PATRIMONIO NETO - Número principal -->
    <div class="card" style="border:2px solid #d4af37; background: linear-gradient(135deg, rgba(212,175,55,0.1), rgba(19,47,76,0.6)); text-align:center; padding:35px;">
      <h3 style="font-size:1rem; letter-spacing:2px;">💰 PATRIMONIO NETO</h3>
      <div class="value <?= $patrimonio['neto'] >= 0 ? 'positivo' : 'negativo' ?>" style="font-size:3rem; margin:15px 0;">
        <?= formatoCOP($patrimonio['neto']) ?>
      </div>
      <p style="color: #9fb3c8; font-size:0.9rem;">
        Activos <strong style="color: #27ae60;"><?= formatoCOP($patrimonio['activos']) ?></strong>
        − 
        Pasivos <strong style="color: #e74c3c;"><?= formatoCOP($patrimonio['pasivos']) ?></strong>
      </p>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="cards-grid" style="margin-top:25px;">
      <div class="card">
        <h3>📈 Activos</h3>
        <div class="value positivo"><?= formatoCOP($patrimonio['activos']) ?></div>
        <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">En <?= count($patrimonio['cuentas']) ?> cuenta(s)</p>
      </div>
      <div class="card">
        <h3>📉 Pasivos</h3>
        <div class="value negativo"><?= formatoCOP($patrimonio['pasivos']) ?></div>
        <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">Deudas pendientes</p>
      </div>
      <div class="card">
        <h3>💵 Balance del mes</h3>
        <div class="value <?= $balance >= 0 ? 'positivo' : 'negativo' ?>">
          <?= formatoCOP($balance) ?>
        </div>
        <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">Ingresos − Gastos totales</p>
      </div>
      <div class="card">
        <h3>🎯 Metas activas</h3>
        <div class="value"><?= $metas['total'] ?></div>
        <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">
          <?= formatoCOP($metas['ahorrado']) ?> de <?= formatoCOP($metas['objetivo']) ?>
        </p>
      </div>
    </div>

    <!-- 🏦 Desglose por cuenta -->
    <h2 style="color #d4af37; margin:35px 0 20px;">🏦 Distribución de tu dinero</h2>

    <?php if (empty($patrimonio['cuentas'])): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.1rem; margin-bottom:15px;">🏦 No tienes cuentas creadas</p>
        <a href="/FinanIA/modulos/cuentas/index.php?accion=crear" class="btn btn-primary">Crear primera cuenta</a>
      </div>
    <?php else: ?>
      <div class="cards-grid">
        <?php foreach ($patrimonio['cuentas'] as $c): 
          $porcentaje = $patrimonio['activos'] > 0 
            ? ($c['saldo_actual'] / $patrimonio['activos']) * 100 
            : 0;
        ?>
          <div class="card" style="border-left:4px solid <?= e($c['color']) ?>;">
            <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
              <h3 style="margin:0; font-size:1rem;"><?= e($c['nombre']) ?></h3>
              <span style="font-size:0.75rem; color: #9fb3c8;"><?= number_format($porcentaje, 1) ?>%</span>
            </div>
            <div class="value <?= $c['saldo_actual'] >= 0 ? 'positivo' : 'negativo' ?>" style="font-size:1.4rem;">
              <?= formatoCOP($c['saldo_actual']) ?>
            </div>
            <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">
              <?= e(capitalizar($c['tipo'])) ?>
              <?php if ($c['etiqueta']): ?>
                · <?= e($c['etiqueta']) ?>
              <?php endif; ?>
            </p>
            <div class="progress" style="margin-top:10px; height:6px;">
              <div class="progress-bar" style="width: <?= $porcentaje ?>%; background: <?= e($c['color']) ?>;"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Acciones rápidas -->
    <!-- 🌟 Upgrade a Premium -->
    <?php
    require_once __DIR__ . '/../config/dev.php';
    if (esDesarrollador()): 
    ?>

    <!-- 🛠️ Herramienta de desarrollo: cambiar a Premium -->
    <div class="card" style="border:2px solid #9b59b6; background: linear-gradient(135deg, rgba(155,89,182,0.15), rgba(19,47,76,0.6)); text-align:center; padding:30px; margin-top:30px;">
      <h3 style="color:#d5a6ff; letter-spacing:2px;">🛠️ MODO DESARROLLO</h3>
      <p style="color:#c9d6e3; margin:15px 0; font-size:0.95rem;">
        Cambiar a rol <strong>Premium</strong> para probar las funciones avanzadas.
      </p>
      <a href="/FinanIA/auth/cambiar_rol.php" 
        class="btn btn-primary" 
        style="background: linear-gradient(135deg, #9b59b6, #6c3483); color:#fff; padding:12px 30px;">
        🌟 Cambiar a Premium
      </a>
    </div>
    <?php endif; ?>
    <h2 style="color: #d4af37; margin:35px 0 20px;">⚡ Acciones rápidas</h2>
    <div style="display:flex; gap:15px; flex-wrap:wrap;">
      <a href="/FinanIA/modulos/transacciones/index.php?accion=crear" class="btn btn-primary">+ Nueva Transacción</a>
      <a href="/FinanIA/modulos/metas/index.php?accion=crear" class="btn btn-primary">+ Nueva Meta</a>
      <a href="/FinanIA/modulos/deudas/index.php?accion=crear" class="btn btn-primary">+ Nueva Deuda</a>
      <a href="/FinanIA/modulos/cuentas/index.php?accion=crear" class="btn btn-primary">+ Nueva Cuenta</a>
      <a href="/FinanIA/modulos/reportes/index.php" class="btn btn-secondary">📈 Ver Reportes</a>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>