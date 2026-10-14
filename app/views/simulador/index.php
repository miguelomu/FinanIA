<?php include __DIR__ . '/../../../includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>🏔️ Simulador de Deudas</h1>
    </div>

    <?php if (empty($deudas)): ?>
      <div class="card" style="text-align:center; padding:50px;">
        <p style="font-size:1.3rem; margin-bottom:15px;">🎉 ¡No tienes deudas activas!</p>
        <p style="color:#9fb3c8;">No hay nada que simular. Sigue así.</p>
      </div>
    <?php else: ?>

      <!-- Resumen actual de deudas -->
      <div class="cards-grid">
        <div class="card" style="border:2px solid #e74c3c;">
          <h3>💳 Deuda total</h3>
          <div class="value negativo" style="font-size:1.8rem;"><?= formatoCOP($totalDeuda) ?></div>
        </div>
        <div class="card">
          <h3>📊 Deudas activas</h3>
          <div class="value"><?= count($deudas) ?></div>
        </div>
        <div class="card">
          <h3>💵 Pago mínimo mensual</h3>
          <div class="value"><?= formatoCOP($totalMinimo) ?></div>
        </div>
        <div class="card">
          <h3>📈 Tasa promedio</h3>
          <div class="value"><?= number_format($tasaPromedio, 2) ?>% EA</div>
        </div>
      </div>

      <!-- Lista de deudas -->
      <h2 style="color:#d4af37; margin:30px 0 20px;">Tus deudas</h2>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Acreedor</th>
              <th style="text-align:right;">Saldo pendiente</th>
              <th style="text-align:right;">Tasa EA</th>
              <th style="text-align:right;">Pago mínimo</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($deudas as $d): 
              $saldo = floatval($d['monto_total']) - floatval($d['monto_pagado']);
            ?>
              <tr>
                <td><?= e($d['acreedor']) ?></td>
                <td style="text-align:right; color:#e74c3c; font-weight:bold;">
                  <?= formatoCOP($saldo) ?>
                </td>
                <td style="text-align:right;">
                  <span style="padding:3px 8px; border-radius:8px; font-size:0.8rem;
                    background:<?= $d['tasa_interes'] > 25 ? 'rgba(231,76,60,0.2)' : 'rgba(243,156,18,0.2)' ?>;
                    color:<?= $d['tasa_interes'] > 25 ? '#ff8a80' : '#f39c12' ?>;">
                    <?= number_format($d['tasa_interes'], 2) ?>%
                  </span>
                </td>
                <td style="text-align:right;"><?= formatoCOP($d['pago_minimo']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Formulario de simulación -->
      <h2 style="color:#d4af37; margin:35px 0 20px;">🎯 Simular estrategia de pago</h2>
      <div class="card" style="padding:30px;">
        <form method="POST" action="index.php?accion=simular">
          <?= csrfField() ?>
          
          <div class="form-group">
            <label>¿Cuánto dinero extra puedes pagar al mes? (además de los mínimos)</label>
            <input type="number" name="pago_extra" step="0.01" min="0" required 
                   value="<?= $pagoExtra > 0 ? $pagoExtra : '' ?>"
                   placeholder="Ej: 200000">
            <small style="color:#9fb3c8; font-size:0.85rem;">
              Cualquier monto extra acelera el pago de tus deudas y reduce los intereses.
            </small>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;">
            🚀 Comparar estrategias
          </button>
        </form>
      </div>

      <!-- Resultados de la comparación -->
      <?php if ($resultado): ?>
        <h2 style="color:#d4af37; margin:40px 0 20px;">📊 Comparación de estrategias</h2>

        <!-- Recomendación destacada -->
        <div class="card" style="border:2px solid #d4af37; background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(19,47,76,0.6)); padding:30px;">
          <div style="display:flex; align-items:center; gap:15px; margin-bottom:15px;">
            <span style="font-size:2.5rem;">🏆</span>
            <div>
              <h3 style="color:#d4af37; margin:0; font-size:1.3rem;">
                Recomendación: <?= $resultado['recomendacion'] === 'avalancha' ? 'Avalancha' : 'Bola de nieve' ?>
              </h3>
              <p style="color:#c9d6e3; margin-top:5px; font-size:0.95rem;">
                <?= e($resultado['motivo']) ?>
              </p>
            </div>
          </div>
        </div>

        <!-- Comparación lado a lado -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:25px; margin-top:25px;">
          <!-- Avalancha -->
          <div class="card" style="border:2px solid #3498db;">
            <h3 style="color:#6bb6ff; font-size:1.2rem;">🏔️ Avalancha</h3>
            <p style="color:#9fb3c8; font-size:0.85rem; margin-bottom:20px;">
              Paga primero la deuda con mayor tasa de interés.
            </p>
            
            <div style="margin-bottom:15px;">
              <span style="color:#9fb3c8; font-size:0.85rem;">Tiempo para liquidar todo:</span>
              <div style="color:#d4af37; font-size:1.8rem; font-weight:bold;">
                <?= $resultado['avalancha']['meses'] ?> meses
              </div>
              <span style="color:#7a8a9c; font-size:0.8rem;">
                (<?= round($resultado['avalancha']['meses'] / 12, 1) ?> años)
              </span>
            </div>
            
            <div style="margin-bottom:15px;">
              <span style="color:#9fb3c8; font-size:0.85rem;">Intereses totales:</span>
              <div style="color:#e74c3c; font-size:1.4rem; font-weight:bold;">
                <?= formatoCOP($resultado['avalancha']['total_intereses']) ?>
              </div>
            </div>

            <div>
              <span style="color:#9fb3c8; font-size:0.85rem;">Total a pagar:</span>
              <div style="color:#fff; font-size:1.2rem; font-weight:bold;">
                <?= formatoCOP($resultado['avalancha']['total_pagado']) ?>
              </div>
            </div>
          </div>

          <!-- Bola de nieve -->
          <div class="card" style="border:2px solid #e67e22;">
            <h3 style="color:#f39c12; font-size:1.2rem;">⛄ Bola de nieve</h3>
            <p style="color:#9fb3c8; font-size:0.85rem; margin-bottom:20px;">
              Paga primero la deuda más pequeña.
            </p>
            
            <div style="margin-bottom:15px;">
              <span style="color:#9fb3c8; font-size:0.85rem;">Tiempo para liquidar todo:</span>
              <div style="color:#d4af37; font-size:1.8rem; font-weight:bold;">
                <?= $resultado['bolanieve']['meses'] ?> meses
              </div>
              <span style="color:#7a8a9c; font-size:0.8rem;">
                (<?= round($resultado['bolanieve']['meses'] / 12, 1) ?> años)
              </span>
            </div>
            
            <div style="margin-bottom:15px;">
              <span style="color:#9fb3c8; font-size:0.85rem;">Intereses totales:</span>
              <div style="color:#e74c3c; font-size:1.4rem; font-weight:bold;">
                <?= formatoCOP($resultado['bolanieve']['total_intereses']) ?>
              </div>
            </div>

            <div>
              <span style="color:#9fb3c8; font-size:0.85rem;">Total a pagar:</span>
              <div style="color:#fff; font-size:1.2rem; font-weight:bold;">
                <?= formatoCOP($resultado['bolanieve']['total_pagado']) ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Diferencia -->
        <div class="card" style="margin-top:25px; border:2px solid #27ae60; background: rgba(39,174,96,0.1);">
          <h3 style="color:#7ee8a8;">💡 Diferencia clave</h3>
          <div style="display:flex; gap:40px; flex-wrap:wrap; margin-top:15px;">
            <div>
              <span style="color:#9fb3c8; font-size:0.85rem;">Ahorro en intereses con avalancha:</span>
              <div style="color:#27ae60; font-size:1.5rem; font-weight:bold;">
                <?= formatoCOP($resultado['ahorro_intereses']) ?>
              </div>
            </div>
            <div>
              <span style="color:#9fb3c8; font-size:0.85rem;">Diferencia en tiempo:</span>
              <div style="color:#d4af37; font-size:1.5rem; font-weight:bold;">
                <?= abs($resultado['diferencia_meses']) ?> meses
              </div>
            </div>
          </div>
        </div>

        <!-- Gráfica de evolución -->
        <div class="card" style="margin-top:25px; padding:25px;">
          <h3 style="color:#d4af37; margin-bottom:20px;">📉 Evolución del saldo total</h3>
          <canvas id="graficoEvolucion" height="100"></canvas>
        </div>

        <script>
          const historialAvalancha = <?= json_encode($resultado['avalancha']['historial']) ?>;
          const historialBolaNieve = <?= json_encode($resultado['bolanieve']['historial']) ?>;

          Chart.defaults.color = '#9fb3c8';
          Chart.defaults.borderColor = 'rgba(212, 175, 55, 0.15)';

          const maxMeses = Math.max(historialAvalancha.length, historialBolaNieve.length);
          const labels = Array.from({length: maxMeses}, (_, i) => 'Mes ' + (i + 1));

          const dataAvalancha = labels.map((_, i) => 
            historialAvalancha[i] ? historialAvalancha[i].saldo_total : 0
          );
          const dataBolaNieve = labels.map((_, i) => 
            historialBolaNieve[i] ? historialBolaNieve[i].saldo_total : 0
          );

          new Chart(document.getElementById('graficoEvolucion'), {
            type: 'line',
            data: {
              labels: labels,
              datasets: [
                {
                  label: 'Avalancha',
                  data: dataAvalancha,
                  borderColor: '#3498db',
                  backgroundColor: 'rgba(52,152,219,0.1)',
                  borderWidth: 3,
                  fill: true,
                  tension: 0.3
                },
                {
                  label: 'Bola de nieve',
                  data: dataBolaNieve,
                  borderColor: '#e67e22',
                  backgroundColor: 'rgba(230,126,34,0.1)',
                  borderWidth: 3,
                  fill: true,
                  tension: 0.3
                }
              ]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { position: 'top', labels: { padding: 20 } },
                tooltip: {
                  backgroundColor: 'rgba(10, 25, 41, 0.95)',
                  titleColor: '#d4af37',
                  callbacks: {
                    label: ctx => ctx.dataset.label + ': ' + 
                      ctx.parsed.y.toLocaleString('es-CO', {style: 'currency', currency: 'COP', maximumFractionDigits: 0})
                  }
                }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  ticks: {
                    callback: v => '$' + (v / 1000).toFixed(0) + 'k'
                  }
                }
              }
            }
          });
        </script>

        <!-- Aviso educativo -->
        <div style="background:rgba(243,156,18,0.1); border-left:4px solid #f39c12; padding:15px; border-radius:8px; margin-top:25px;">
          <p style="color:#f39c12; font-size:0.9rem;">
            ⚠️ <strong>Aviso educativo:</strong> Esta simulación es una herramienta de orientación. 
            Los cálculos asumen tasas constantes y no incluyen comisiones, seguros u otros cargos. 
            Consulta con un asesor financiero antes de tomar decisiones importantes.
          </p>
        </div>

      <?php endif; ?>

    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>