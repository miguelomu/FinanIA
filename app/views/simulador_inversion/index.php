<?php include __DIR__ . '/../../../includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💰 Simulador de Inversiones</h1>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">Por favor completa todos los campos.</div>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card" style="padding:30px; max-width:800px; margin-bottom:30px;">
      <h2 style="color:#d4af37; margin-bottom:20px;">💵 Simula tu inversión</h2>
      
      <form method="POST" action="index.php?accion=simular">
        <?= csrfField() ?>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div class="form-group">
            <label>Monto a invertir *</label>
            <input type="number" name="monto" step="0.01" min="1" required 
                   value="<?= $monto > 0 ? $monto : '' ?>"
                   placeholder="Ej: 1000000">
          </div>

          <div class="form-group">
            <label>Plazo (meses) *</label>
            <input type="number" name="plazo" min="1" max="120" required 
                   value="<?= $plazo > 0 ? $plazo : '' ?>"
                   placeholder="Ej: 12">
          </div>
        </div>

        <div style="background:rgba(19,47,76,0.5); padding:15px; border-radius:10px; margin-top:10px;">
          <p style="color:#9fb3c8; font-size:0.85rem;">
            <strong style="color:#d4af37;">Tasas actuales del mercado:</strong> 
            Ahorros <?= number_format($tasas['ahorros'], 2) ?>% EA · 
            CDT <?= number_format($tasas['cdt'], 2) ?>% EA · 
            Fondo <?= number_format($tasas['fondo'], 2) ?>% EA · 
            Inflación <?= number_format($tasas['inflacion'], 2) ?>%
          </p>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:20px;">
          📊 Comparar alternativas
        </button>
      </form>
    </div>

    <!-- Resultados -->
    <?php if ($resultados): ?>

      <!-- Recomendación -->
      <div class="card" style="border:2px solid #d4af37; background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(19,47,76,0.6)); padding:30px; margin-bottom:30px;">
        <div style="display:flex; align-items:center; gap:20px; margin-bottom:15px;">
          <span style="font-size:3rem;">🏆</span>
          <div>
            <h2 style="color:#d4af37; margin:0; font-size:1.4rem;">
              Mejor opción: <?= e($recomendacion['mejor']['nombre']) ?>
            </h2>
            <p style="color:#c9d6e3; margin-top:8px;"><?= e($recomendacion['motivo']) ?></p>
          </div>
        </div>
      </div>

      <!-- Comparación lado a lado -->
      <h2 style="color:#d4af37; margin:30px 0 20px;">📊 Comparación de alternativas</h2>
      
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">
        <?php foreach ($resultados as $i => $r): ?>
          <div class="card" style="border:2px solid <?= e($r['color']) ?>; <?= $i === 0 ? 'background: linear-gradient(135deg, ' . e($r['color']) . '22, rgba(19,47,76,0.6));' : '' ?>">
            <?php if ($i === 0): ?>
              <div style="background:<?= e($r['color']) ?>; color:#000; padding:5px 12px; border-radius:8px; font-size:0.75rem; font-weight:bold; display:inline-block; margin-bottom:10px;">
                🏆 RECOMENDADA
              </div>
            <?php endif; ?>

            <h3 style="color:<?= e($r['color']) ?>; font-size:1.2rem; margin-bottom:15px;">
              <?= e($r['nombre']) ?>
            </h3>

            <div style="margin-bottom:15px;">
              <span style="color:#9fb3c8; font-size:0.85rem;">Valor final (neto):</span>
              <div style="color:#27ae60; font-size:1.8rem; font-weight:bold;">
                <?= formatoCOP($r['valor_final_neto']) ?>
              </div>
            </div>

            <div style="border-top:1px solid rgba(212,175,55,0.15); padding-top:15px;">
              <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:#9fb3c8; font-size:0.85rem;">Tasa nominal:</span>
                <span style="color:#e6edf3;"><?= number_format($r['tasa_efectiva_nominal'], 2) ?>% EA</span>
              </div>
              <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:#9fb3c8; font-size:0.85rem;">Rendimiento bruto:</span>
                <span style="color:#7ee8a8;">+<?= formatoCOP($r['rendimiento_bruto']) ?></span>
              </div>
              
              <?php if ($r['retencion_fuente'] > 0): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                  <span style="color:#9fb3c8; font-size:0.85rem;">Retención en la fuente:</span>
                  <span style="color:#ff8a80;">-<?= formatoCOP($r['retencion_fuente']) ?></span>
                </div>
              <?php endif; ?>
              
              <?php if ($r['gravamen'] > 0): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                  <span style="color:#9fb3c8; font-size:0.85rem;">Gravamen 4x1000:</span>
                  <span style="color:#ff8a80;">-<?= formatoCOP($r['gravamen']) ?></span>
                </div>
              <?php endif; ?>

              <div style="display:flex; justify-content:space-between; margin-bottom:8px; padding-top:8px; border-top:1px solid rgba(212,175,55,0.1);">
                <span style="color:#9fb3c8; font-size:0.85rem;">Rendimiento neto:</span>
                <span style="color:#7ee8a8; font-weight:bold;">+<?= formatoCOP($r['rendimiento_neto']) ?></span>
              </div>

              <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:#9fb3c8; font-size:0.85rem;">Inflación:</span>
                <span style="color:#f39c12;"><?= number_format($r['inflacion_acumulada'], 2) ?>%</span>
              </div>

              <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:#9fb3c8; font-size:0.85rem;">Rendimiento real:</span>
                <span style="color:<?= $r['rendimiento_real'] >= 0 ? '#7ee8a8' : '#ff8a80' ?>; font-weight:bold;">
                  <?= $r['rendimiento_real'] >= 0 ? '+' : '' ?><?= formatoCOP($r['rendimiento_real']) ?>
                </span>
              </div>

              <div style="display:flex; justify-content:space-between;">
                <span style="color:#9fb3c8; font-size:0.85rem;">Tasa real:</span>
                <span style="color:<?= $r['tasa_efectiva_real'] >= 0 ? '#7ee8a8' : '#ff8a80' ?>; font-weight:bold;">
                  <?= number_format($r['tasa_efectiva_real'], 2) ?>% EA
                </span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Gráfica comparativa -->
      <div class="card" style="padding:25px; margin-top:30px;">
        <h3 style="color:#d4af37; margin-bottom:20px;">📊 Comparación visual</h3>
        <canvas id="graficoComparacion" height="100"></canvas>
      </div>

      <!-- Explicación educativa -->
      <div class="card" style="margin-top:30px; padding:25px; border-left:4px solid #3498db;">
        <h3 style="color:#6bb6ff;">📚 ¿Qué significan estos números?</h3>
        <ul style="color:#c9d6e3; margin-top:15px; padding-left:25px; line-height:2;">
          <li><strong>Valor final neto:</strong> cuánto tendrás al final después de impuestos y gravámenes.</li>
          <li><strong>Retención en la fuente:</strong> el gobierno retiene un % de tus rendimientos (solo CDT).</li>
          <li><strong>Gravamen 4x1000:</strong> impuesto sobre movimientos financieros (aplica si sacas el dinero).</li>
          <li><strong>Rendimiento real:</strong> es lo que realmente ganas DESPUÉS de descontar la inflación. Este número es el más importante.</li>
          <li><strong>Tasa real:</strong> si es negativa, aunque ganes dinero nominalmente, pierdes poder adquisitivo.</li>
        </ul>
      </div>

      <!-- Aviso legal -->
      <div style="background:rgba(243,156,18,0.1); border-left:4px solid #f39c12; padding:15px; border-radius:8px; margin-top:25px;">
        <p style="color:#f39c12; font-size:0.9rem;">
          ⚠️ <strong>Aviso educativo:</strong> Esta simulación es una herramienta de orientación. 
          Las tasas son promedios del mercado y pueden variar. No incluye comisiones, 
          seguros de depósito u otros cargos. Consulta con un asesor financiero antes de invertir.
        </p>
      </div>

      <script>
        const datos = <?= json_encode($resultados) ?>;
        
        Chart.defaults.color = '#9fb3c8';
        Chart.defaults.borderColor = 'rgba(212, 175, 55, 0.15)';

        new Chart(document.getElementById('graficoComparacion'), {
          type: 'bar',
          data: {
            labels: datos.map(d => d.nombre),
            datasets: [
              {
                label: 'Rendimiento neto',
                data: datos.map(d => d.rendimiento_neto),
                backgroundColor: 'rgba(39,174,96,0.7)',
                borderColor: '#27ae60',
                borderWidth: 2,
                borderRadius: 8
              },
              {
                label: 'Rendimiento real (descontando inflación)',
                data: datos.map(d => d.rendimiento_real),
                backgroundColor: 'rgba(155,89,182,0.7)',
                borderColor: '#9b59b6',
                borderWidth: 2,
                borderRadius: 8
              }
            ]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { position: 'top', labels: { padding: 15 } },
              tooltip: {
                backgroundColor: 'rgba(10, 25, 41, 0.95)',
                titleColor: '#d4af37',
                callbacks: {
                  label: ctx => ctx.dataset.label + ': ' + 
                    ctx.parsed.y.toLocaleString('es-CO', {style:'currency', currency:'COP', maximumFractionDigits:0})
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