<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

// Filtro de fechas (por defecto: últimos 6 meses)
$fecha_desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-6 months'));
$fecha_hasta = $_GET['hasta'] ?? date('Y-m-d');

// ============================================
// 1. RESUMEN GENERAL
// ============================================
$stmt = $pdo->prepare("SELECT 
    COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END),0) AS total_ingresos,
    COALESCE(SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END),0) AS total_gastos,
    COUNT(*) AS total_transacciones
    FROM transaccion 
    WHERE id_usuario = ? AND fecha BETWEEN ? AND ?");
$stmt->execute([$usuario['id'], $fecha_desde, $fecha_hasta]);
$resumen = $stmt->fetch();
$balance = $resumen['total_ingresos'] - $resumen['total_gastos'];
$tasaAhorro = $resumen['total_ingresos'] > 0 
    ? ($balance / $resumen['total_ingresos']) * 100 
    : 0;

// ============================================
// 2. INGRESOS VS GASTOS POR MES
// ============================================
$stmt = $pdo->prepare("SELECT 
    DATE_FORMAT(fecha, '%Y-%m') AS periodo,
    DATE_FORMAT(fecha, '%b %Y') AS mes_nombre,
    COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END),0) AS ingresos,
    COALESCE(SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END),0) AS gastos
    FROM transaccion 
    WHERE id_usuario = ? AND fecha BETWEEN ? AND ?
    GROUP BY periodo, mes_nombre
    ORDER BY periodo ASC");
$stmt->execute([$usuario['id'], $fecha_desde, $fecha_hasta]);
$mensual = $stmt->fetchAll();

// ============================================
// 3. GASTOS POR CATEGORÍA (para gráfica circular)
// ============================================
$stmt = $pdo->prepare("SELECT 
    c.nombre AS categoria,
    c.color,
    COALESCE(SUM(t.monto),0) AS total
    FROM categoria c
    INNER JOIN transaccion t ON c.id_categoria = t.id_categoria
    WHERE t.id_usuario = ? 
      AND t.tipo = 'gasto'
      AND t.fecha BETWEEN ? AND ?
    GROUP BY c.id_categoria, c.nombre, c.color
    HAVING total > 0
    ORDER BY total DESC");
$stmt->execute([$usuario['id'], $fecha_desde, $fecha_hasta]);
$gastosPorCategoria = $stmt->fetchAll();

// ============================================
// 4. TOP 5 GASTOS MÁS ALTOS
// ============================================
$stmt = $pdo->prepare("SELECT 
    t.monto, t.descripcion, t.fecha, c.nombre AS categoria
    FROM transaccion t
    INNER JOIN categoria c ON t.id_categoria = c.id_categoria
    WHERE t.id_usuario = ? AND t.tipo = 'gasto' AND t.fecha BETWEEN ? AND ?
    ORDER BY t.monto DESC
    LIMIT 5");
$stmt->execute([$usuario['id'], $fecha_desde, $fecha_hasta]);
$topGastos = $stmt->fetchAll();

// ============================================
// 5. INGRESOS POR CATEGORÍA
// ============================================
$stmt = $pdo->prepare("SELECT 
    c.nombre AS categoria,
    c.color,
    COALESCE(SUM(t.monto),0) AS total
    FROM categoria c
    INNER JOIN transaccion t ON c.id_categoria = t.id_categoria
    WHERE t.id_usuario = ? 
      AND t.tipo = 'ingreso'
      AND t.fecha BETWEEN ? AND ?
    GROUP BY c.id_categoria, c.nombre, c.color
    HAVING total > 0
    ORDER BY total DESC");
$stmt->execute([$usuario['id'], $fecha_desde, $fecha_hasta]);
$ingresosPorCategoria = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<!-- Chart.js para las gráficas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>📈 Reportes</h1>
      <button onclick="window.print()" class="btn btn-secondary">🖨️ Exportar PDF</button>
    </div>

    <!-- Filtro de fechas -->
    <div style="background:rgba(19,47,76,0.5); padding:20px; border-radius:12px; margin-bottom:25px;">
      <form method="GET" style="display:flex; gap:15px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="margin:0; flex:1; min-width:150px;">
          <label>Desde</label>
          <input type="date" name="desde" value="<?= htmlspecialchars($fecha_desde) ?>">
        </div>
        <div class="form-group" style="margin:0; flex:1; min-width:150px;">
          <label>Hasta</label>
          <input type="date" name="hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Aplicar</button>
        <a href="index.php" class="btn btn-secondary">Últimos 6 meses</a>
      </form>
    </div>

    <!-- Resumen general -->
    <div class="cards-grid">
      <div class="card">
        <h3>Total Ingresos</h3>
        <div class="value positivo">$<?= number_format($resumen['total_ingresos'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Total Gastos</h3>
        <div class="value negativo">$<?= number_format($resumen['total_gastos'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Balance</h3>
        <div class="value <?= $balance >= 0 ? 'positivo' : 'negativo' ?>">
          $<?= number_format($balance, 2) ?>
        </div>
      </div>
      <div class="card">
        <h3>Tasa de Ahorro</h3>
        <div class="value"><?= number_format($tasaAhorro, 1) ?>%</div>
      </div>
    </div>

    <p style="color:#9fb3c8; text-align:center; margin-bottom:30px;">
      <strong><?= $resumen['total_transacciones'] ?></strong> transacciones entre
      <strong><?= date('d/m/Y', strtotime($fecha_desde)) ?></strong> y
      <strong><?= date('d/m/Y', strtotime($fecha_hasta)) ?></strong>
    </p>

    <?php if (empty($mensual) && empty($gastosPorCategoria)): ?>
      <div class="card" style="text-align:center; padding:50px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">📭 No hay datos en el rango seleccionado</p>
        <a href="/FinanIA/modulos/transacciones/crear.php" class="btn btn-primary">Registrar transacción</a>
      </div>
    <?php else: ?>

    <!-- Gráfica Ingresos vs Gastos -->
    <h2 style="color:#d4af37; margin:30px 0 20px;">📊 Ingresos vs Gastos por Mes</h2>
    <div class="card" style="padding:25px;">
      <canvas id="graficoMensual" height="100"></canvas>
    </div>

    <!-- Gráfica circular: Gastos por categoría -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:25px; margin-top:30px;">
      <div>
        <h2 style="color:#d4af37; margin-bottom:20px;">💸 Gastos por Categoría</h2>
        <div class="card" style="padding:25px;">
          <?php if (empty($gastosPorCategoria)): ?>
            <p style="text-align:center; color:#9fb3c8; padding:40px;">Sin gastos en el período</p>
          <?php else: ?>
            <canvas id="graficoGastos"></canvas>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <h2 style="color:#d4af37; margin-bottom:20px;">💰 Ingresos por Categoría</h2>
        <div class="card" style="padding:25px;">
          <?php if (empty($ingresosPorCategoria)): ?>
            <p style="text-align:center; color:#9fb3c8; padding:40px;">Sin ingresos en el período</p>
          <?php else: ?>
            <canvas id="graficoIngresos"></canvas>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Top 5 Gastos -->
    <h2 style="color:#d4af37; margin:30px 0 20px;">🔝 Top 5 Gastos más Altos</h2>
    <?php if (empty($topGastos)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="color:#9fb3c8;">No hay gastos en el período seleccionado</p>
      </div>
    <?php else: ?>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Fecha</th>
              <th>Categoría</th>
              <th>Descripción</th>
              <th style="text-align:right;">Monto</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topGastos as $i => $g): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= date('d/m/Y', strtotime($g['fecha'])) ?></td>
                <td><?= htmlspecialchars($g['categoria']) ?></td>
                <td><?= htmlspecialchars($g['descripcion'] ?? '—') ?></td>
                <td style="text-align:right; color:#e74c3c; font-weight:bold;">
                  -$<?= number_format($g['monto'], 2) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php endif; ?>
  </main>
</div>

<script>
// Datos desde PHP
const datosMensual = <?= json_encode($mensual) ?>;
const datosGastos = <?= json_encode($gastosPorCategoria) ?>;
const datosIngresos = <?= json_encode($ingresosPorCategoria) ?>;

// Configuración global de Chart.js
Chart.defaults.color = '#9fb3c8';
Chart.defaults.borderColor = 'rgba(212, 175, 55, 0.15)';
Chart.defaults.font.family = "'Segoe UI', Tahoma, sans-serif";

// ============================================
// GRÁFICA 1: Ingresos vs Gastos por mes
// ============================================
if (datosMensual.length > 0) {
  const ctxMensual = document.getElementById('graficoMensual').getContext('2d');
  new Chart(ctxMensual, {
    type: 'bar',
    data: {
      labels: datosMensual.map(d => d.mes_nombre),
      datasets: [
        {
          label: 'Ingresos',
          data: datosMensual.map(d => parseFloat(d.ingresos)),
          backgroundColor: 'rgba(39, 174, 96, 0.7)',
          borderColor: '#27ae60',
          borderWidth: 2,
          borderRadius: 8,
        },
        {
          label: 'Gastos',
          data: datosMensual.map(d => parseFloat(d.gastos)),
          backgroundColor: 'rgba(231, 76, 60, 0.7)',
          borderColor: '#e74c3c',
          borderWidth: 2,
          borderRadius: 8,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { position: 'top', labels: { font: { size: 13 }, padding: 20 } },
        tooltip: {
          backgroundColor: 'rgba(10, 25, 41, 0.95)',
          titleColor: '#d4af37',
          borderColor: 'rgba(212, 175, 55, 0.5)',
          borderWidth: 1,
          padding: 12,
          callbacks: {
            label: ctx => ctx.dataset.label + ': $' + ctx.parsed.y.toLocaleString('es-CO')
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { callback: v => '$' + v.toLocaleString('es-CO') },
          grid: { color: 'rgba(212, 175, 55, 0.1)' }
        },
        x: { grid: { display: false } }
      }
    }
  });
}

// ============================================
// GRÁFICA 2: Gastos por categoría (doughnut)
// ============================================
if (datosGastos.length > 0) {
  const ctxGastos = document.getElementById('graficoGastos').getContext('2d');
  new Chart(ctxGastos, {
    type: 'doughnut',
    data: {
      labels: datosGastos.map(d => d.categoria),
      datasets: [{
        data: datosGastos.map(d => parseFloat(d.total)),
        backgroundColor: datosGastos.map(d => d.color || '#3498db'),
        borderColor: '#0a1929',
        borderWidth: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { position: 'bottom', labels: { padding: 15, font: { size: 12 } } },
        tooltip: {
          backgroundColor: 'rgba(10, 25, 41, 0.95)',
          titleColor: '#d4af37',
          borderColor: 'rgba(212, 175, 55, 0.5)',
          borderWidth: 1,
          padding: 12,
          callbacks: {
            label: ctx => {
              const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
              const pct = ((ctx.parsed / total) * 100).toFixed(1);
              return ctx.label + ': $' + ctx.parsed.toLocaleString('es-CO') + ' (' + pct + '%)';
            }
          }
        }
      }
    }
  });
}

// ============================================
// GRÁFICA 3: Ingresos por categoría (doughnut)
// ============================================
if (datosIngresos.length > 0) {
  const ctxIngresos = document.getElementById('graficoIngresos').getContext('2d');
  new Chart(ctxIngresos, {
    type: 'doughnut',
    data: {
      labels: datosIngresos.map(d => d.categoria),
      datasets: [{
        data: datosIngresos.map(d => parseFloat(d.total)),
        backgroundColor: datosIngresos.map(d => d.color || '#27ae60'),
        borderColor: '#0a1929',
        borderWidth: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { position: 'bottom', labels: { padding: 15, font: { size: 12 } } },
        tooltip: {
          backgroundColor: 'rgba(10, 25, 41, 0.95)',
          titleColor: '#d4af37',
          borderColor: 'rgba(212, 175, 55, 0.5)',
          borderWidth: 1,
          padding: 12,
          callbacks: {
            label: ctx => {
              const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
              const pct = ((ctx.parsed / total) * 100).toFixed(1);
              return ctx.label + ': $' + ctx.parsed.toLocaleString('es-CO') + ' (' + pct + '%)';
            }
          }
        }
      }
    }
  });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>