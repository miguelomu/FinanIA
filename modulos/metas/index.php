<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

$estado = $_GET['estado'] ?? '';

$sql = "SELECT * FROM meta WHERE id_usuario = ?";
$params = [$usuario['id']];

if (in_array($estado, ['activa', 'completada', 'cancelada'])) {
    $sql .= " AND estado = ?";
    $params[] = $estado;
}

$sql .= " ORDER BY 
    CASE estado 
        WHEN 'activa' THEN 1 
        WHEN 'completada' THEN 2 
        ELSE 3 
    END, fecha_limite ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$metas = $stmt->fetchAll();

// Totales
$totalObjetivo = 0;
$totalAhorrado = 0;
foreach ($metas as $m) {
    $totalObjetivo += $m['monto_objetivo'];
    $totalAhorrado += $m['monto_actual'];
}
$progresoTotal = $totalObjetivo > 0 ? ($totalAhorrado / $totalObjetivo) * 100 : 0;

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>🎯 Mis Metas de Ahorro</h1>
      <a href="crear.php" class="btn btn-primary">+ Nueva Meta</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'creado' => 'Meta creada exitosamente',
          'actualizado' => 'Meta actualizada exitosamente',
          'eliminado' => 'Meta eliminada exitosamente',
          'aportado' => 'Aporte registrado exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <!-- Resumen -->
    <div class="cards-grid">
      <div class="card">
        <h3>Total Objetivos</h3>
        <div class="value">$<?= number_format($totalObjetivo, 2) ?></div>
      </div>
      <div class="card">
        <h3>Total Ahorrado</h3>
        <div class="value positivo">$<?= number_format($totalAhorrado, 2) ?></div>
      </div>
      <div class="card">
        <h3>Falta por Ahorrar</h3>
        <div class="value negativo">$<?= number_format($totalObjetivo - $totalAhorrado, 2) ?></div>
      </div>
      <div class="card">
        <h3>Progreso Total</h3>
        <div class="value"><?= number_format($progresoTotal, 1) ?>%</div>
      </div>
    </div>

    <!-- Filtros -->
    <div style="background:rgba(19,47,76,0.5); padding:20px; border-radius:12px; margin-bottom:20px;">
      <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="index.php" class="btn <?= empty($estado) ? 'btn-primary' : 'btn-secondary' ?>">Todas</a>
        <a href="?estado=activa" class="btn <?= $estado === 'activa' ? 'btn-primary' : 'btn-secondary' ?>">Activas</a>
        <a href="?estado=completada" class="btn <?= $estado === 'completada' ? 'btn-primary' : 'btn-secondary' ?>">Completadas</a>
        <a href="?estado=cancelada" class="btn <?= $estado === 'cancelada' ? 'btn-primary' : 'btn-secondary' ?>">Canceladas</a>
      </div>
    </div>

    <!-- Lista -->
    <?php if (empty($metas)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">🎯 No tienes metas creadas</p>
        <a href="crear.php" class="btn btn-primary">Crear primera meta</a>
      </div>
    <?php else: ?>
      <?php foreach ($metas as $m): 
        $progreso = $m['monto_objetivo'] > 0 
            ? min(100, ($m['monto_actual'] / $m['monto_objetivo']) * 100) 
            : 0;
        $claseBarra = $progreso >= 100 ? 'success' : ($progreso >= 50 ? '' : 'warning');
        $estadoColor = [
            'activa' => '#3498db',
            'completada' => '#27ae60',
            'cancelada' => '#e74c3c'
        ][$m['estado']];
      ?>
        <div class="meta-card" style="border-left:4px solid <?= $estadoColor ?>;">
          <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:15px;">
            <h3 style="margin:0;"><?= htmlspecialchars($m['nombre']) ?></h3>
            <span style="padding:4px 12px; border-radius:12px; font-size:0.75rem; font-weight:bold;
              background: <?= $estadoColor ?>22; color: <?= $estadoColor ?>;">
              <?= ucfirst($m['estado']) ?>
            </span>
          </div>

          <div class="meta-info">
            <span>Objetivo: <strong>$<?= number_format($m['monto_objetivo'], 2) ?></strong></span>
            <span>Ahorrado: <strong>$<?= number_format($m['monto_actual'], 2) ?></strong></span>
            <span>Falta: <strong>$<?= number_format($m['monto_objetivo'] - $m['monto_actual'], 2) ?></strong></span>
          </div>

          <div class="meta-info">
            <span>Inicio: <strong><?= date('d/m/Y', strtotime($m['fecha_inicio'])) ?></strong></span>
            <span>Límite: <strong><?= date('d/m/Y', strtotime($m['fecha_limite'])) ?></strong></span>
            <span>Progreso: <strong><?= number_format($progreso, 1) ?>%</strong></span>
          </div>

          <div class="progress">
            <div class="progress-bar <?= $claseBarra ?>" style="width: <?= $progreso ?>%;"></div>
          </div>

          <div class="btn-group" style="margin-top:15px;">
            <?php if ($m['estado'] === 'activa'): ?>
                <a href="aportar.php?id=<?= $m['id_meta'] ?>" class="btn btn-primary">+ Aporte</a>
            <?php endif; ?>
            <a href="ver.php?id=<?= $m['id_meta'] ?>" class="btn btn-secondary">📋 Ver Aportes</a>
            <a href="editar.php?id=<?= $m['id_meta'] ?>" class="btn btn-secondary">✏️ Editar</a>
            <a href="eliminar.php?id=<?= $m['id_meta'] ?>" 
               class="btn btn-secondary"
               style="color:#e74c3c;"
               onclick="return confirm('¿Eliminar esta meta?')">🗑️ Eliminar</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>