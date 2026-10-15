<?php include __DIR__ . '/../../../includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
  .tabs-inversion {
    display: flex;
    gap: 8px;
    background: rgba(19, 47, 76, 0.5);
    padding: 8px;
    border-radius: 12px;
    margin-bottom: 25px;
    overflow-x: auto;
  }
  
  .tab-inversion {
    flex: 1;
    padding: 12px 20px;
    background: transparent;
    border: none;
    color: #9fb3c8;
    cursor: pointer;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    white-space: nowrap;
    transition: all 0.2s;
  }
  
  .tab-inversion:hover {
    background: rgba(212, 175, 55, 0.1);
    color: #d4af37;
  }
  
  .tab-inversion.active {
    background: linear-gradient(135deg, #d4af37, #b8860b);
    color: #0a1929;
  }
  
  .panel-inversion {
    display: none;
  }
  
  .panel-inversion.active {
    display: block;
  }
  
  [data-tema="claro"] .tabs-inversion {
    background: rgba(240, 240, 240, 0.9);
  }
  
  [data-tema="claro"] .tab-inversion {
    color: #4a5a6a;
  }
</style>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💰 Simulador de Inversiones</h1>
    </div>

    <!-- Tabs -->
    <div class="tabs-inversion">
      <button class="tab-inversion <?= ($tipo_activo ?? 'cdt') === 'cdt' ? 'active' : '' ?>" onclick="cambiarTab('cdt')">
        📜 CDT / Plazo fijo
      </button>
      <button class="tab-inversion <?= ($tipo_activo ?? '') === 'fic' ? 'active' : '' ?>" onclick="cambiarTab('fic')">
        📈 FIC con aportes
      </button>
      <button class="tab-inversion <?= ($tipo_activo ?? '') === 'comparador' ? 'active' : '' ?>" onclick="cambiarTab('comparador')">
        ⚖️ Comparador
      </button>
      <button class="tab-inversion <?= ($tipo_activo ?? '') === 'meta' ? 'active' : '' ?>" onclick="cambiarTab('meta')">
        🎯 Calculadora de meta
      </button>
    </div>

    <!-- ============================================ -->
    <!-- PESTAÑA 1: CDT -->
    <!-- ============================================ -->
    <div class="panel-inversion <?= ($tipo_activo ?? 'cdt') === 'cdt' ? 'active' : '' ?>" id="panel-cdt">
      
      <div class="card" style="padding:30px; max-width:800px; margin-bottom:25px;">
        <h2 style="color:#d4af37; margin-bottom:15px;">📜 CDT / Inversión a plazo fijo</h2>
        <p style="color:#9fb3c8; margin-bottom:25px;">
          Inviertes un monto único por un plazo definido. Es la opción más segura y con tasa fija.
        </p>

        <form method="POST" action="index.php?accion=simular">
          <?= csrfField() ?>
          <input type="hidden" name="tipo" value="cdt">

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="form-group">
              <label>Monto a invertir *</label>
              <input type="number" name="monto" step="0.01" min="1" required 
                     value="<?= isset($_POST['monto']) && ($tipo_activo ?? '') === 'cdt' ? $_POST['monto'] : '' ?>"
                     placeholder="Ej: 5000000">
            </div>
            <div class="form-group">
              <label>Plazo (meses) *</label>
              <input type="number" name="plazo" min="1" max="120" required 
                     value="<?= isset($_POST['plazo']) && ($tipo_activo ?? '') === 'cdt' ? $_POST['plazo'] : '' ?>"
                     placeholder="Ej: 12">
            </div>
          </div>

          <div class="form-group">
            <label>
              <input type="checkbox" name="aplicar_gravamen" value="1" style="width:auto;">
              Aplicar gravamen 4x1000 (si retiras a otra cuenta)
            </label>
          </div>

          <div style="background:rgba(19,47,76,0.5); padding:15px; border-radius:10px; margin-top:15px;">
            <p style="color:#9fb3c8; font-size:0.85rem;">
              <strong style="color:#d4af37;">Tasas actuales:</strong> 
              CDT <?= number_format($tasas['cdt'], 2) ?>% EA · 
              Inflación <?= number_format($tasas['inflacion'], 2) ?>% · 
              Retención <?= number_format($tasas['retencion_cdt'], 2) ?>%
            </p>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%; margin-top:20px;">
            📊 Calcular rendimiento
          </button>
        </form>
      </div>

      <?php if ($resultado && ($resultado['tipo'] ?? '') === 'cdt'): ?>
        <?= renderResultadoIndividual($resultado) ?>
      <?php endif; ?>
    </div>

    <!-- ============================================ -->
    <!-- PESTAÑA 2: FIC -->
    <!-- ============================================ -->
    <div class="panel-inversion <?= ($tipo_activo ?? '') === 'fic' ? 'active' : '' ?>" id="panel-fic">
      
      <div class="card" style="padding:30px; max-width:800px; margin-bottom:25px;">
        <h2 style="color:#9b59b6; margin-bottom:15px;">📈 FIC con aportes mensuales</h2>
        <p style="color:#9fb3c8; margin-bottom:25px;">
          Aportas una cantidad fija cada mes. Ideal para quienes reciben ingresos periódicos. El interés se capitaliza mensualmente.
        </p>

        <form method="POST" action="index.php?accion=simular">
          <?= csrfField() ?>
          <input type="hidden" name="tipo" value="fic">

          <div class="form-group">
            <label>Monto inicial (opcional)</label>
            <input type="number" name="monto_inicial" step="0.01" min="0"
                   value="<?= isset($_POST['monto_inicial']) && ($tipo_activo ?? '') === 'fic' ? $_POST['monto_inicial'] : '0' ?>"
                   placeholder="0">
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="form-group">
              <label>Aporte mensual *</label>
              <input type="number" name="aporte_mensual" step="0.01" min="1" required 
                     value="<?= isset($_POST['aporte_mensual']) && ($tipo_activo ?? '') === 'fic' ? $_POST['aporte_mensual'] : '' ?>"
                     placeholder="Ej: 300000">
            </div>
            <div class="form-group">
              <label>Plazo (meses) *</label>
              <input type="number" name="plazo" min="1" max="360" required 
                     value="<?= isset($_POST['plazo']) && ($tipo_activo ?? '') === 'fic' ? $_POST['plazo'] : '' ?>"
                     placeholder="Ej: 24">
            </div>
          </div>

          <div style="background:rgba(19,47,76,0.5); padding:15px; border-radius:10px; margin-top:15px;">
            <p style="color:#9fb3c8; font-size:0.85rem;">
              <strong style="color:#9b59b6;">Tasa FIC:</strong> 
              <?= number_format($tasas['fic'], 2) ?>% EA · 
              Inflación: <?= number_format($tasas['inflacion'], 2) ?>%
            </p>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%; margin-top:20px;">
            📊 Calcular rendimiento
          </button>
        </form>
      </div>

      <?php if ($resultado && ($resultado['tipo'] ?? '') === 'fic'): ?>
        <?= renderResultadoIndividual($resultado) ?>
      <?php endif; ?>
    </div>

    <!-- ============================================ -->
    <!-- PESTAÑA 3: COMPARADOR -->
    <!-- ============================================ -->
    <div class="panel-inversion <?= ($tipo_activo ?? '') === 'comparador' ? 'active' : '' ?>" id="panel-comparador">
      
      <div class="card" style="padding:30px; max-width:800px; margin-bottom:25px;">
        <h2 style="color:#d4af37; margin-bottom:15px;">⚖️ Comparador de productos</h2>
        <p style="color:#9fb3c8; margin-bottom:25px;">
          Compara los 3 productos financieros más comunes con la misma inversión. 
          El FIC se simula con aportes mensuales del mismo valor total.
        </p>

        <form method="POST" action="index.php?accion=simular">
          <?= csrfField() ?>
          <input type="hidden" name="tipo" value="comparador">

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="form-group">
              <label>Monto total a comparar *</label>
              <input type="number" name="monto" step="0.01" min="1" required 
                     value="<?= isset($_POST['monto']) && ($tipo_activo ?? '') === 'comparador' ? $_POST['monto'] : '' ?>"
                     placeholder="Ej: 5000000">
            </div>
            <div class="form-group">
              <label>Plazo (meses) *</label>
              <input type="number" name="plazo" min="1" max="120" required 
                     value="<?= isset($_POST['plazo']) && ($tipo_activo ?? '') === 'comparador' ? $_POST['plazo'] : '' ?>"
                     placeholder="Ej: 12">
            </div>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%; margin-top:20px;">
            ⚖️ Comparar los 3 productos
          </button>
        </form>
      </div>

      <?php if ($resultado && ($resultado['tipo'] ?? '') === 'comparador'): ?>
        <?= renderComparador($resultado) ?>
      <?php endif; ?>
    </div>

    <!-- ============================================ -->
    <!-- PESTAÑA 4: CALCULADORA DE META -->
    <!-- ============================================ -->
    <div class="panel-inversion <?= ($tipo_activo ?? '') === 'meta' ? 'active' : '' ?>" id="panel-meta">
      
      <div class="card" style="padding:30px; max-width:800px; margin-bottom:25px;">
        <h2 style="color:#27ae60; margin-bottom:15px;">🎯 Calculadora de meta de ahorro</h2>
        <p style="color:#9fb3c8; margin-bottom:25px;">
          Dime cuánto quieres ahorrar y en cuánto tiempo. Te diré cuánto debes aportar cada mes.
        </p>

        <form method="POST" action="index.php?accion=simular">
          <?= csrfField() ?>
          <input type="hidden" name="tipo" value="meta">

          <div class="form-group">
            <label>Monto que quieres alcanzar *</label>
            <input type="number" name="monto_meta" step="0.01" min="1" required 
                   value="<?= isset($_POST['monto_meta']) && ($tipo_activo ?? '') === 'meta' ? $_POST['monto_meta'] : '' ?>"
                   placeholder="Ej: 10000000">
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="form-group">
              <label>Plazo (meses) *</label>
              <input type="number" name="plazo" min="1" max="360" required 
                     value="<?= isset($_POST['plazo']) && ($tipo_activo ?? '') === 'meta' ? $_POST['plazo'] : '' ?>"
                     placeholder="Ej: 24">
            </div>
            <div class="form-group">
              <label>Monto inicial (opcional)</label>
              <input type="number" name="monto_inicial" step="0.01" min="0"
                     value="<?= isset($_POST['monto_inicial']) && ($tipo_activo ?? '') === 'meta' ? $_POST['monto_inicial'] : '0' ?>"
                     placeholder="0">
            </div>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%; margin-top:20px;">
            🎯 Calcular aporte mensual
          </button>
        </form>
      </div>

      <?php if ($meta_resultado): ?>
        <?= renderMeta($meta_resultado) ?>
      <?php endif; ?>
    </div>

  </main>
</div>

<script>
function cambiarTab(tab) {
  document.querySelectorAll('.tab-inversion').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.panel-inversion').forEach(p => p.classList.remove('active'));
  
  document.querySelector(`.tab-inversion[onclick*="'${tab}'"]`).classList.add('active');
  document.getElementById('panel-' + tab).classList.add('active');
}
</script>

<?php
// ============================================
// FUNCIONES DE RENDERIZADO
// ============================================

function renderResultadoIndividual($r) {
    $esFIC = ($r['tipo'] ?? '') === 'fic';
    $color = $esFIC ? '#9b59b6' : '#d4af37';
    $icono = $esFIC ? '📈' : '📜';
    
    ob_start();
    ?>
    <div class="card" style="border:2px solid <?= $color ?>; background:linear-gradient(135deg, <?= $color ?>15, rgba(19,47,76,0.6)); padding:30px; margin-bottom:25px;">
      <h2 style="color:<?= $color ?>; margin-bottom:20px;"><?= $icono ?> Resultado</h2>
      
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:20px; margin-bottom:25px;">
        <div>
          <p style="color:#9fb3c8; font-size:0.85rem;">Total aportado</p>
          <div style="color:#e6edf3; font-size:1.3rem; font-weight:bold;">
            <?= formatoCOP($r['total_aportado']) ?>
          </div>
        </div>
        <div>
          <p style="color:#9fb3c8; font-size:0.85rem;">Rendimiento bruto</p>
          <div style="color:#7ee8a8; font-size:1.3rem; font-weight:bold;">
            +<?= formatoCOP($r['rendimiento_bruto']) ?>
          </div>
        </div>
        <div>
          <p style="color:#9fb3c8; font-size:0.85rem;">Retención en la fuente</p>
          <div style="color:#ff8a80; font-size:1.3rem; font-weight:bold;">
            -<?= formatoCOP($r['retencion_fuente']) ?>
          </div>
        </div>
        <div>
          <p style="color:#9fb3c8; font-size:0.85rem;">Valor final neto</p>
          <div style="color:<?= $color ?>; font-size:1.6rem; font-weight:bold;">
            <?= formatoCOP($r['valor_final_neto']) ?>
          </div>
        </div>
      </div>

      <?php if ($esFIC && !empty($r['historial'])): ?>
        <div style="background:rgba(10,25,41,0.5); padding:20px; border-radius:12px; margin-top:20px;">
          <h3 style="color:#d4af37; margin-bottom:15px;">📊 Evolución mes a mes</h3>
          <canvas id="graficoEvolucionFIC" height="100"></canvas>
        </div>
      <?php endif; ?>

      <div style="background:rgba(39,174,96,0.1); border-left:4px solid #27ae60; padding:20px; border-radius:8px; margin-top:20px;">
        <h3 style="color:#7ee8a8; margin-bottom:15px;">💡 Análisis real</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div>
            <p style="color:#9fb3c8; font-size:0.85rem;">Tasa nominal</p>
            <div style="color:#e6edf3; font-size:1.2rem; font-weight:bold;">
              <?= number_format($r['tasa_efectiva_nominal'], 2) ?>% EA
            </div>
          </div>
          <div>
            <p style="color:#9fb3c8; font-size:0.85rem;">Tasa real (descontando inflación)</p>
            <div style="color:<?= ($r['tasa_efectiva_real'] ?? 0) >= 0 ? '#7ee8a8' : '#ff8a80' ?>; font-size:1.2rem; font-weight:bold;">
              <?= number_format($r['tasa_efectiva_real'] ?? ($r['tasa_efectiva_nominal'] - 5.2), 2) ?>% EA
            </div>
          </div>
          <div>
            <p style="color:#9fb3c8; font-size:0.85rem;">Rendimiento real</p>
            <div style="color:<?= ($r['rendimiento_real'] ?? 0) >= 0 ? '#7ee8a8' : '#ff8a80' ?>; font-size:1.2rem; font-weight:bold;">
              <?= formatoCOP($r['rendimiento_real'] ?? 0) ?>
            </div>
          </div>
          <div>
            <p style="color:#9fb3c8; font-size:0.85rem;">Inflación acumulada</p>
            <div style="color:#f39c12; font-size:1.2rem; font-weight:bold;">
              <?= number_format($r['inflacion_acumulada'], 2) ?>%
            </div>
          </div>
        </div>
      </div>

      <div style="background:rgba(243,156,18,0.1); border-left:4px solid #f39c12; padding:15px; border-radius:8px; margin-top:20px;">
        <p style="color:#f39c12; font-size:0.85rem;">
          ⚠️ <strong>Aviso educativo:</strong> Los cálculos asumen tasas constantes. 
          Las tasas reales pueden variar. No incluye comisiones ni seguros.
        </p>
      </div>
    </div>

    <?php if ($esFIC && !empty($r['historial'])): ?>
      <script>
        const historialFIC = <?= json_encode($r['historial']) ?>;
        Chart.defaults.color = '#9fb3c8';
        Chart.defaults.borderColor = 'rgba(212, 175, 55, 0.15)';

        new Chart(document.getElementById('graficoEvolucionFIC'), {
          type: 'line',
          data: {
            labels: historialFIC.map(h => 'Mes ' + h.mes),
            datasets: [
              {
                label: 'Capital acumulado',
                data: historialFIC.map(h => h.capital),
                borderColor: '#9b59b6',
                backgroundColor: 'rgba(155,89,182,0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.3
              },
              {
                label: 'Total aportado',
                data: historialFIC.map(h => h.total_aportado),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52,152,219,0.05)',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false,
                tension: 0.3
              }
            ]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { position: 'top' },
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
                ticks: { callback: v => '$' + (v / 1000).toFixed(0) + 'k' }
              }
            }
          }
        });
      </script>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

function renderComparador($r) {
    $productos = $r['productos'];
    $mejor = $productos[0];
    
    ob_start();
    ?>
    <div class="card" style="border:2px solid #d4af37; background:linear-gradient(135deg, rgba(212,175,55,0.15), rgba(19,47,76,0.6)); padding:30px; margin-bottom:25px;">
      <h2 style="color:#d4af37; margin-bottom:15px;">🏆 Mejor opción: <?= e($mejor['nombre']) ?></h2>
      <p style="color:#c9d6e3;">
        Con una inversión de <?= formatoCOP($r['monto']) ?> a <?= $r['plazo'] ?> meses, 
        esta opción te genera <?= formatoCOP($mejor['valor_final_neto'] - $mejor['total_aportado']) ?> netos.
      </p>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">
      <?php foreach ($productos as $i => $p): ?>
        <div class="card" style="border:2px solid <?= e($p['color']) ?>; <?= $i === 0 ? 'background:linear-gradient(135deg, ' . e($p['color']) . '15, rgba(19,47,76,0.6));' : '' ?>">
          <?php if ($i === 0): ?>
            <div style="background:<?= e($p['color']) ?>; color:#000; padding:5px 12px; border-radius:8px; font-size:0.75rem; font-weight:bold; display:inline-block; margin-bottom:10px;">
              🏆 RECOMENDADA
            </div>
          <?php endif; ?>

          <h3 style="color:<?= e($p['color']) ?>; font-size:1.15rem; margin-bottom:15px;">
            <?= e($p['icono']) ?> <?= e($p['nombre']) ?>
          </h3>

          <div style="margin-bottom:15px;">
            <p style="color:#9fb3c8; font-size:0.8rem;">Valor final neto</p>
            <div style="color:#27ae60; font-size:1.5rem; font-weight:bold;">
              <?= formatoCOP($p['valor_final_neto']) ?>
            </div>
          </div>

          <div style="border-top:1px solid rgba(212,175,55,0.15); padding-top:12px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
              <span style="color:#9fb3c8; font-size:0.8rem;">Total aportado</span>
              <span style="color:#e6edf3; font-size:0.85rem;"><?= formatoCOP($p['total_aportado']) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
              <span style="color:#9fb3c8; font-size:0.8rem;">Rendimiento neto</span>
              <span style="color:#7ee8a8; font-size:0.85rem;">+<?= formatoCOP($p['rendimiento_neto']) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
              <span style="color:#9fb3c8; font-size:0.8rem;">Retención</span>
              <span style="color:#ff8a80; font-size:0.85rem;">-<?= formatoCOP($p['retencion_fuente']) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between;">
              <span style="color:#9fb3c8; font-size:0.8rem;">Tasa nominal</span>
              <span style="color:#e6edf3; font-size:0.85rem;"><?= number_format($p['tasa_efectiva_nominal'], 2) ?>% EA</span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="card" style="padding:25px; margin-top:25px;">
      <h3 style="color:#d4af37; margin-bottom:20px;">📊 Comparación visual</h3>
      <canvas id="graficoComparacion" height="100"></canvas>
    </div>

    <script>
      const productos = <?= json_encode($productos) ?>;
      
      Chart.defaults.color = '#9fb3c8';
      Chart.defaults.borderColor = 'rgba(212, 175, 55, 0.15)';

      new Chart(document.getElementById('graficoComparacion'), {
        type: 'bar',
        data: {
          labels: productos.map(p => p.nombre),
          datasets: [
            {
              label: 'Total aportado',
              data: productos.map(p => p.total_aportado),
              backgroundColor: 'rgba(52,152,219,0.6)',
              borderRadius: 8
            },
            {
              label: 'Valor final neto',
              data: productos.map(p => p.valor_final_neto),
              backgroundColor: 'rgba(39,174,96,0.7)',
              borderRadius: 8
            }
          ]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'top' },
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
              ticks: { callback: v => '$' + (v / 1000).toFixed(0) + 'k' }
            }
          }
        }
      });
    </script>
    <?php
    return ob_get_clean();
}

function renderMeta($r) {
    ob_start();
    ?>
    <div class="card" style="border:2px solid #27ae60; background:linear-gradient(135deg, rgba(39,174,96,0.15), rgba(19,47,76,0.6)); padding:35px; margin-bottom:25px;">
      <div style="text-align:center; margin-bottom:25px;">
        <div style="font-size:3rem;">🎯</div>
        <h2 style="color:#7ee8a8; margin-top:10px;">Tu plan de ahorro</h2>
      </div>

      <div style="background:rgba(10,25,41,0.5); padding:25px; border-radius:12px; text-align:center; margin-bottom:25px;">
        <p style="color:#9fb3c8; font-size:0.9rem; margin-bottom:10px;">Debes aportar cada mes:</p>
        <div style="color:#27ae60; font-size:2.5rem; font-weight:bold;">
          <?= formatoCOP($r['aporte_mensual']) ?>
        </div>
        <p style="color:#9fb3c8; font-size:0.85rem; margin-top:10px;">
          durante <?= $r['plazo_meses'] ?> meses
        </p>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:20px;">
        <div>
          <p style="color:#9fb3c8; font-size:0.8rem;">Meta</p>
          <div style="color:#d4af37; font-size:1.2rem; font-weight:bold;"><?= formatoCOP($r['monto_meta']) ?></div>
        </div>
        <div>
          <p style="color:#9fb3c8; font-size:0.8rem;">Total aportado</p>
          <div style="color:#e6edf3; font-size:1.2rem; font-weight:bold;"><?= formatoCOP($r['total_aportado']) ?></div>
        </div>
        <div>
          <p style="color:#9fb3c8; font-size:0.8rem;">Valor final</p>
          <div style="color:#7ee8a8; font-size:1.2rem; font-weight:bold;"><?= formatoCOP($r['valor_final']) ?></div>
        </div>
        <div>
          <p style="color:#9fb3c8; font-size:0.8rem;">Rendimiento generado</p>
          <div style="color:#7ee8a8; font-size:1.2rem; font-weight:bold;">+<?= formatoCOP($r['rendimiento_generado']) ?></div>
        </div>
      </div>

      <div style="background:rgba(52,152,219,0.1); border-left:4px solid #3498db; padding:15px; border-radius:8px; margin-top:25px;">
        <p style="color:#6bb6ff; font-size:0.85rem;">
          💡 <strong>Tip:</strong> Programa una transferencia automática el día que recibes tu salario 
          para no gastarlo antes de ahorrarlo.
        </p>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>