<?php include __DIR__ . '/../../../includes/header.php'; ?>

<?php 
$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>📅 <?= $meses[$presupuesto['mes']] ?> <?= $presupuesto['anio'] ?></h1>
      <div style="display:flex; gap:10px;">
        <a href="index.php?accion=editar&id=<?= $presupuesto['id_presupuesto'] ?>" class="btn btn-primary">✏️ Editar</a>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
      </div>
    </div>

    <div class="cards-grid">
      <div class="card">
        <h3>Presupuestado</h3>
        <div class="value">$<?= number_format($totales['presupuestado'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Gastado</h3>
        <div class="value negativo">$<?= number_format($totales['gastado'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Disponible</h3>
        <div class="value <?= $totales['disponible'] >= 0 ? 'positivo' : 'negativo' ?>">
          $<?= number_format($totales['disponible'], 2) ?>
        </div>
      </div>
      <div class="card">
        <h3>Progreso</h3>
        <div class="value"><?= number_format($totales['progreso'], 1) ?>%</div>
      </div>
    </div>

    <div class="progress" style="margin-bottom:30px;">
      <div class="progress-bar <?= $totales['progreso'] >= 100 ? 'danger' : ($totales['progreso'] >= 80 ? 'warning' : 'success') ?>" 
           style="width: <?= min(100, $totales['progreso']) ?>%;"></div>
    </div>

    <h2 style="color: #d4af37; margin-bottom:20px;">Detalle por Categoría</h2>

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
              <td style="text-align:right; color: #e74c3c;">$<?= number_format($d['gastado'], 2) ?></td>
              <td style="text-align:right; font-weight:bold; color: <?= $disponible >= 0 ? '#27ae60' : '#e74c3c' ?>;">
                $<?= number_format($disponible, 2) ?>
              </td>
              <td>
                <div class="progress" style="margin:0;">
                  <div class="progress-bar <?= $claseBarra ?>" style="width: <?= min(100, $progreso) ?>%;"></div>
                </div>
                <small style="color: #9fb3c8;"><?= number_format($progreso, 1) ?>%</small>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>