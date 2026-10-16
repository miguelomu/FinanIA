<?php include __DIR__ . '/../../../includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💼 Fuentes de Ingreso</h1>
      <a href="index.php?accion=crear" class="btn btn-primary">+ Nueva Fuente</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'creado' => 'Fuente creada exitosamente',
          'actualizado' => 'Fuente actualizada exitosamente',
          'eliminado' => 'Fuente eliminada exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <!-- Panel de estabilidad -->
    <h2 style="color: #d4af37; margin-bottom:20px;">📊 Análisis de estabilidad (últimos 6 meses)</h2>
    
    <div class="cards-grid">
      <div class="card">
        <h3>Ingreso promedio mensual</h3>
        <div class="value positivo"><?= formatoCOP($estabilidad['promedio']) ?></div>
      </div>
      <div class="card">
        <h3>Ingreso mínimo</h3>
        <div class="value negativo"><?= formatoCOP($estabilidad['minimo']) ?></div>
      </div>
      <div class="card">
        <h3>Ingreso máximo</h3>
        <div class="value positivo"><?= formatoCOP($estabilidad['maximo']) ?></div>
      </div>
      <div class="card" style="border-color: <?= $estabilidad['estabilidad'] > 70 ? '#27ae60' : ($estabilidad['estabilidad'] > 40 ? '#f39c12' : '#e74c3c') ?>;">
        <h3>Estabilidad</h3>
        <div class="value" style="color: <?= $estabilidad['estabilidad'] > 70 ? '#27ae60' : ($estabilidad['estabilidad'] > 40 ? '#f39c12' : '#e74c3c') ?>;">
          <?= number_format($estabilidad['estabilidad'], 0) ?>%
        </div>
        <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">
          <?php if ($estabilidad['estabilidad'] > 70): ?>
            ✅ Ingresos estables
          <?php elseif ($estabilidad['estabilidad'] > 40): ?>
            ⚠️ Ingresos algo variables
          <?php else: ?>
            🚨 Ingresos muy irregulares
          <?php endif; ?>
        </p>
      </div>
    </div>

    <!-- Gráfica de ingresos mensuales -->
    <?php if (!empty($estabilidad['datos_mensuales'])): ?>
      <div class="card" style="padding:25px; margin-top:25px;">
        <h3 style="color: #d4af37; margin-bottom:20px;">📈 Evolución de ingresos</h3>
        <canvas id="graficoIngresos" height="80"></canvas>
      </div>
    <?php endif; ?>

    <!-- Lista de fuentes -->
    <h2 style="color: #d4af37; margin:35px 0 20px;">💼 Mis fuentes de ingreso</h2>

    <?php if (empty($fuentes)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">💼 No has registrado fuentes de ingreso</p>
        <p style="color: #9fb3c8; margin-bottom:20px;">
          Registra de dónde viene tu dinero para que el sistema pueda proyectar tus ingresos.
        </p>
        <a href="index.php?accion=crear" class="btn btn-primary">Crear primera fuente</a>
      </div>
    <?php else: ?>
      <div class="cards-grid">
        <?php foreach ($fuentes as $f): ?>
          <div class="card" style="border-left:4px solid <?= e($f['color']) ?>;">
            <div style="display:flex; justify-content:space-between; align-items:start;">
              <h3 style="margin:0; font-size:1.1rem;"><?= e($f['nombre']) ?></h3>
              <?php if (!$f['activa']): ?>
                <span style="padding:2px 8px; border-radius:8px; font-size:0.7rem;
                  background:rgba(231,76,60,0.2); color: #ff8a80;">Inactiva</span>
              <?php endif; ?>
            </div>

            <p style="color: #9fb3c8; font-size:0.8rem; margin-top:8px;">
              <?= e(capitalizar($f['tipo'])) ?> · <?= e(capitalizar($f['frecuencia'])) ?>
            </p>

            <div class="value positivo" style="font-size:1.4rem; margin-top:10px;">
              <?= formatoCOP($f['promedio_real'] > 0 ? $f['promedio_real'] : $f['monto_promedio']) ?>
            </div>
            <p style="color: #7a8a9c; font-size:0.75rem;">
              <?= $f['total_ingresos'] ?> ingreso(s) registrado(s)
            </p>

            <div class="btn-group" style="margin-top:15px;">
              <a href="index.php?accion=editar&id=<?= $f['id_fuente'] ?>" class="btn btn-secondary" style="font-size:0.75rem; padding:8px 12px;">✏️ Editar</a>
              <a href="index.php?accion=eliminar&id=<?= $p['id_presupuesto'] ?>" 
                class="btn btn-secondary" 
                style="font-size:0.75rem; padding:8px 12px; color:#e74c3c;"
                onclick="return confirmar(event, {
                  titulo: '¿Eliminar fuente?',
                  mensaje: 'Se eliminarán todos los límites configurados.',
                  tipo: 'danger',
                  icono: '📅',
                  confirmar: 'Eliminar esta fuente'
                })">🗑️</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($estabilidad['datos_mensuales'])): ?>
      <script>
        const datos = <?= json_encode($estabilidad['datos_mensuales']) ?>;
        
        Chart.defaults.color = '#9fb3c8';
        Chart.defaults.borderColor = 'rgba(212, 175, 55, 0.15)';

        new Chart(document.getElementById('graficoIngresos'), {
          type: 'bar',
          data: {
            labels: datos.map(d => d.mes),
            datasets: [{
              label: 'Ingresos',
              data: datos.map(d => parseFloat(d.total)),
              backgroundColor: 'rgba(39,174,96,0.6)',
              borderColor: '#27ae60',
              borderWidth: 2,
              borderRadius: 8
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { display: false },
              tooltip: {
                backgroundColor: 'rgba(10, 25, 41, 0.95)',
                titleColor: '#d4af37',
                callbacks: {
                  label: ctx => '$' + ctx.parsed.y.toLocaleString('es-CO')
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: { callback: v => '$' + (v / 1000).toFixed(0) + 'k' }
              }
            }
          }
        });
      </script>
    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>