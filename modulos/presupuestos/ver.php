<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM presupuesto WHERE id_presupuesto = ? AND id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
$presupuesto = $stmt->fetch();

if (!$presupuesto) {
    header('Location: index.php');
    exit;
}

// Cargar detalles con gasto real del mes
$stmt = $pdo->prepare("
    SELECT dp.*, c.nombre AS categoria_nombre,
        COALESCE((
            SELECT SUM(monto) FROM transaccion t
            WHERE t.id_usuario = ?
              AND t.id_categoria = dp.id_categoria
              AND t.tipo = 'gasto'
              AND MONTH(t.fecha) = ?
              AND YEAR(t.fecha) = ?
        ), 0) AS gastado
    FROM detalle_presupuesto dp
    INNER JOIN categoria c ON dp.id_categoria = c.id_categoria
    WHERE dp.id_presupuesto = ?
    ORDER BY c.nombre
");
$stmt->execute([$usuario['id'], $presupuesto['mes'], $presupuesto['anio'], $id]);
$detalles = $stmt->fetchAll();

$totalPresupuestado = 0;
$totalGastado = 0;
foreach ($detalles as $d) {
    $totalPresupuestado += $d['limite'];
    $totalGastado += $d['gastado'];
}
$progresoTotal = $totalPresupuestado > 0 ? ($totalGastado / $totalPresupuestado) * 100 : 0;

$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>📅 <?= $meses[$presupuesto['mes']] ?> <?= $presupuesto['anio'] ?></h1>
      <div style="display:flex; gap:10px;">
        <a href="editar.php?id=<?= $presupuesto['id_presupuesto'] ?>" class="btn btn-primary">✏️ Editar</a>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
      </div>
    </div>

    <div class="cards-grid">
      <div class="card">
        <h3>Presupuestado</h3>
        <div class="value">$<?= number_format($totalPresupuestado, 2) ?></div>
      </div>
      <div class="card">
        <h3>Gastado</h3>
        <div class="value negativo">$<?= number_format($totalGastado, 2) ?></div>
      </div>
      <div class="card">
        <h3>Disponible</h3>
        <div class="value <?= ($totalPresupuestado - $totalGastado) >= 0 ? 'positivo' : 'negativo' ?>">
          $<?= number_format($totalPresupuestado - $totalGastado, 2) ?>
        </div>
      </div>
      <div class="card">
        <h3>Progreso</h3>
        <div class="value"><?= number_format($progresoTotal, 1) ?>%</div>
      </div>
    </div>

    <div class="progress" style="margin-bottom:30px;">
      <div class="progress-bar <?= $progresoTotal >= 100 ? 'danger' : ($progresoTotal >= 80 ? 'warning' : 'success') ?>" 
           style="width: <?= min(100, $progresoTotal) ?>%;"></div>
    </div>

    <h2 style="color:#d4af37; margin-bottom:20px;">Detalle por Categoría</h2>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Categoría</th>
            <th style="text-align:right;">Límite</th>
            <th style="text-align:right;">Gastado</th>
            <th style="text-align:right;">Disponible</th>
            <th style="width:200px;">Progreso</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($detalles as $d): 
            $progreso = $d['limite'] > 0 ? ($d['gastado'] / $d['limite']) * 100 : 0;
            $claseBarra = $progreso >= 100 ? 'danger' : ($progreso >= 80 ? 'warning' : 'success');
            $disponible = $d['limite'] - $d['gastado'];
          ?>
            <tr>
              <td><?= htmlspecialchars($d['categoria_nombre']) ?></td>
              <td style="text-align:right;">$<?= number_format($d['limite'], 2) ?></td>
              <td style="text-align:right; color:#e74c3c;">$<?= number_format($d['gastado'], 2) ?></td>
              <td style="text-align:right; font-weight:bold; color: <?= $disponible >= 0 ? '#27ae60' : '#e74c3c' ?>;">
                $<?= number_format($disponible, 2) ?>
              </td>
              <td>
                <div class="progress" style="margin:0;">
                  <div class="progress-bar <?= $claseBarra ?>" style="width: <?= min(100, $progreso) ?>%;"></div>
                </div>
                <small style="color:#9fb3c8;"><?= number_format($progreso, 1) ?>%</small>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>