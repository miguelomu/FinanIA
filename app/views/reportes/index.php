<?php include __DIR__ . '/../../../includes/header.php'; ?>

<!-- Chart.js para las gráficas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

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
          <input type="date" name="desde" value="<?= htmlspecialchars($fechaDesde) ?>">
        </div>
        <div class="form-group" style="margin:0; flex:1; min-width:150px;">
          <label>Hasta</label>
          <input type="date" name="hasta" value="<?= htmlspecialchars($fechaHasta) ?>">
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
        <div class="value <?= $resumen['balance'] >= 0 ? 'positivo' : 'negativo' ?>">
          $<?= number_format($resumen['balance'], 2) ?>
        </div>
      </div>
      <div class="card">
        <h3>Tasa de Ahorro</h3>
        <div class="value"><?= number_format($resumen['tasa_ahorro'], 1) ?>%</div>
      </div>
    </div>

    <p style="color: #9fb3c8; text-align:center; margin-bottom:30px;">
      <strong><?= $resumen['total_transacciones'] ?></strong> transacciones entre
      <strong><?= date('d/m/Y', strtotime($fechaDesde)) ?></strong> y
      <strong><?= date('d/m/Y', strtotime($fechaHasta)) ?></strong>
    </p>

    <?php if (empty($mensual) && empty($gastosPorCategoria)): ?>
      <div class="card" style="text-align:center; padding:50px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">📭 No hay datos en el rango seleccionado</p>
        <a href="/FinanIA/modulos/transacciones/index.php?accion=crear" class="btn btn-primary">Registrar transacción</a>
      </div>
    <?php else: ?>

    <!-- Gráfica Ingresos vs Gastos -->
    <h2 style="color: #d4af37; margin:30px 0 20px;">📊 Ingresos vs Gastos por Mes</h2>
    <div class="card" style="padding:25px;">
      <canvas id="graficoMensual" height="100"></canvas>
    </div>

    <!-- Gráficas circulares -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:25px; margin-top:30px;">
      <div>
        <h2 style="color: #d4af37; margin-bottom:20px;">💸 Gastos por Categoría</h2>
        <div class="card" style="padding:25px;">
          <?php if (empty($gastosPorCategoria)): ?>
            <p style="text-align:center; color: #9fb3c8; padding:40px;">Sin gastos en el período</p>
          <?php else: ?>
            <canvas id="graficoGastos"></canvas>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <h2 style="color: #d4af37; margin-bottom:20px;">💰 Ingresos por Categoría</h2>
        <div class="card" style="padding:25px;">
          <?php if (empty($ingresosPorCategoria)): ?>
            <p style="text-align:center; color: #9fb3c8; padding:40px;">Sin ingresos en el período</p>
          <?php else: ?>
            <canvas id="graficoIngresos"></canvas>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Top 5 Gastos -->
    <h2 style="color: #d4af37; margin:30px 0 20px;">🔝 Top 5 Gastos más Altos</h2>
    <?php if (empty($topGastos)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="color: #9fb3c8;">No hay gastos en el período seleccionado</p>
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
                <td style="text-align:right; color: #e74c3c; font-weight:bold;">
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
const datosMensual = <?= json_encode($mensual) ?>;
const datosGastos = <?= json_encode($gastosPorCategoria) ?>;
const datosIngresos = <?= json_encode($ingresosPorCategoria) ?>;

Chart.defaults.color = '#9fb3c8';
Chart.defaults.borderColor = 'rgba(212, 175, 55, 0.15)';
Chart.defaults.font.family = "'Segoe UI', Tahoma, sans-serif";

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
          callbacks: {
            label: ctx => ctx.dataset.label + ': $' + ctx.parsed.y.toLocaleString('es-CO')
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { callback: v => '$' + v.toLocaleString('es-CO') }
        },
        x: { grid: { display: false } }
      }
    }
  });
}

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
      plugins: {
        legend: { position: 'bottom', labels: { padding: 15 } },
        tooltip: {
          backgroundColor: 'rgba(10, 25, 41, 0.95)',
          titleColor: '#d4af37',
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
      plugins: {
        legend: { position: 'bottom', labels: { padding: 15 } },
        tooltip: {
          backgroundColor: 'rgba(10, 25, 41, 0.95)',
          titleColor: '#d4af37',
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

<?php include __DIR__ . '/../../../includes/footer.php'; ?>