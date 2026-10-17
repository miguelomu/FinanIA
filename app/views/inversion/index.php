<?php include __DIR__ . '/../../../includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💡 Ideas de Inversión</h1>
      <p style="color:#9fb3c8;">Recomendaciones personalizadas según tu perfil: <strong><?= ucfirst($perfil) ?></strong></p>
    </div>

    <!-- Filtro de presupuesto (AJAX) -->
    <div class="card" style="padding:20px; margin-bottom:25px;">
      <div style="display:flex; gap:15px; align-items:flex-end; flex-wrap:wrap;">
        <div class="form-group" style="margin:0; flex:1; min-width:200px;">
          <label>¿Cuánto tienes para invertir?</label>
          <input type="number" id="presupuestoInput" step="0.01" min="0" 
                 value="<?= $presupuesto > 0 ? $presupuesto : '' ?>" 
                 placeholder="Ej: 3000000">
        </div>
        <button type="button" class="btn btn-primary" onclick="actualizarProductos()">Calcular</button>
        <a href="index.php" class="btn btn-secondary">Limpiar</a>
      </div>
      <p id="estadoCalculo" style="color:#9fb3c8; font-size:0.85rem; margin-top:10px; display:none;">Actualizando proyección...</p>
    </div>

    <!-- SECCIÓN 1: INVERSIÓN INTANGIBLE (Acordeones) -->
    <h2 style="color:#d4af37; margin-bottom:20px;">📈 Inversión Intangible</h2>
    <p style="color:#9fb3c8; margin-bottom:15px;">Haz clic en cada opción para ver los detalles.</p>

    <?php foreach ($opcionesIntangibles as $index => $opcion): ?>
      <div class="card" style="margin-bottom:12px; border-left:4px solid <?= $index === 0 ? '#27ae60' : 'rgba(212,175,55,0.3)' ?>;">
        <div onclick="toggleAcordeon(this)" style="cursor:pointer; display:flex; justify-content:space-between; align-items:center; padding:5px 0;">
          <div style="display:flex; align-items:center; gap:12px;">
            <?php if ($index === 0): ?>
              <span style="background:#27ae60; color:#fff; padding:2px 8px; border-radius:12px; font-size:0.7rem; font-weight:bold;">RECOMENDADA</span>
            <?php endif; ?>
            <h3 style="color:<?= $index === 0 ? '#7ee8a8' : '#d4af37' ?>; font-size:1.1rem; margin:0;"><?= e($opcion['titulo']) ?></h3>
          </div>
          <span class="icono" style="font-size:1.5rem; color:#d4af37; transition:transform 0.3s;">▾</span>
        </div>

        <div class="contenido-acordeon" style="display:none; margin-top:15px; padding-top:15px; border-top:1px solid rgba(212,175,55,0.15);">
          <p style="color:#c9d6e3; margin-bottom:15px;"><?= e($opcion['descripcion']) ?></p>
          
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
            <div style="background:rgba(10,25,41,0.5); padding:12px; border-radius:8px;">
              <span style="color:#9fb3c8; font-size:0.85rem;">Riesgo:</span>
              <strong style="color:#f39c12; margin-left:8px;"><?= e($opcion['riesgo']) ?></strong>
            </div>
            <div style="background:rgba(10,25,41,0.5); padding:12px; border-radius:8px;">
              <span style="color:#9fb3c8; font-size:0.85rem;">Liquidez:</span>
              <strong style="color:#e6edf3; margin-left:8px;"><?= e($opcion['liquidez']) ?></strong>
            </div>
          </div>

          <?php if (isset($opcion['proyeccion']['rendimiento_real'])): ?>
            <div style="background:rgba(39,174,96,0.1); border-left:3px solid #27ae60; padding:12px; border-radius:6px; margin-bottom:15px;">
              <p style="color:#7ee8a8; font-size:0.9rem; margin-bottom:5px;"><strong>Proyección para <?= formatoCOP($opcion['proyeccion']['inversion']) ?>:</strong></p>
              <p style="color:#c9d6e3; font-size:0.85rem;">
                Rendimiento bruto: <strong style="color:#7ee8a8;"><?= formatoCOP($opcion['proyeccion']['rendimiento_bruto']) ?></strong><br>
                Inflación: <strong style="color:#f39c12;"><?= number_format($opcion['proyeccion']['inflacion'], 2) ?>%</strong><br>
                <strong style="color:#d4af37;">Rendimiento real: <?= formatoCOP($opcion['proyeccion']['rendimiento_real']) ?></strong>
              </p>
              <p style="color:#9fb3c8; font-size:0.8rem; margin-top:8px; font-style:italic;"><?= e($opcion['proyeccion']['nota']) ?></p>
            </div>
          <?php else: ?>
            <div style="background:rgba(52,152,219,0.1); border-left:3px solid #3498db; padding:12px; border-radius:6px; margin-bottom:15px;">
              <p style="color:#6bb6ff; font-size:0.85rem;"><?= e($opcion['proyeccion']['nota']) ?></p>
            </div>
          <?php endif; ?>

          <div style="background:rgba(19,47,76,0.5); padding:12px; border-radius:6px;">
            <strong style="color:#d4af37; font-size:0.85rem;">📍 Dónde invertir:</strong>
            <span style="color:#c9d6e3; font-size:0.85rem;"><?= e($opcion['donde']) ?></span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- SECCIÓN 2: INVERSIÓN TANGIBLE -->
    <h2 style="color:#d4af37; margin:35px 0 20px;">📦 Inversión Tangible (Compra y Reventa)</h2>
    <p style="color:#9fb3c8; margin-bottom:15px;">Proyección según tu presupuesto. Haz clic en cada producto para ver el gráfico.</p>

    <div id="productosTangiblesContainer">
      <!-- Aquí se cargan los productos vía AJAX -->
      <?php if (empty($productosTangibles)): ?>
        <div class="card" style="text-align:center; padding:40px;">
          <p style="color:#9fb3c8;">No hay productos que se ajusten a tu presupuesto actual.</p>
        </div>
      <?php else: ?>
        <?php foreach ($productosTangibles as $index => $p): ?>
          <div class="card producto-tangible" style="margin-bottom:12px; border-left:4px solid #9b59b6;">
            <div onclick="toggleAcordeonProducto(this)" style="cursor:pointer; display:flex; justify-content:space-between; align-items:center; padding:5px 0;">
              <div>
                <h3 style="color:#d5a6ff; font-size:1.1rem; margin:0;"><?= e($p['nombre']) ?></h3>
                <p style="color:#9fb3c8; font-size:0.8rem; margin-top:3px;"><?= ucfirst($p['categoria']) ?> · ROI: <?= $p['roi'] ?>%</p>
              </div>
              <span class="icono" style="font-size:1.5rem; color:#d5a6ff; transition:transform 0.3s;">▾</span>
            </div>

            <div class="contenido-acordeon" style="display:none; margin-top:15px; padding-top:15px; border-top:1px solid rgba(155,89,182,0.2);">
              <!-- Proyección numérica -->
              <div style="background:rgba(10,25,41,0.6); padding:15px; border-radius:10px; margin-bottom:15px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:0.9rem;">
                  <div><span style="color:#9fb3c8;">Inversión total:</span> <strong style="color:#e6edf3;"><?= formatoCOP($p['inversion_total']) ?></strong></div>
                  <div><span style="color:#9fb3c8;">Ingreso por venta:</span> <strong style="color:#e6edf3;"><?= formatoCOP($p['ingreso_total']) ?></strong></div>
                  <div><span style="color:#9fb3c8;">Unidades compradas:</span> <strong style="color:#e6edf3;"><?= $p['unidades_compradas'] ?></strong></div>
                  <div><span style="color:#9fb3c8;">Ganancia total:</span> <strong style="color:#27ae60;">+<?= formatoCOP($p['ganancia_total']) ?></strong></div>
                  <div><span style="color:#9fb3c8;">ROI:</span> <strong style="color:#f39c12;"><?= $p['roi'] ?>%</strong></div>
                  <div><span style="color:#9fb3c8;">Margen:</span> <strong style="color:#f39c12;"><?= $p['margen'] ?>%</strong></div>
                </div>
              </div>

              <!-- Gráfico -->
              <div style="background:rgba(10,25,41,0.5); padding:15px; border-radius:10px; margin-bottom:15px;">
                <canvas id="graficoProducto<?= $index ?>" height="100"></canvas>
              </div>

              <!-- Proveedor -->
              <div style="font-size:0.85rem;">
                <p style="color:#9fb3c8; margin-bottom:5px;"><strong style="color:#d4af37;">Proveedor:</strong> <?= e($p['proveedor'] ?? 'No especificado') ?></p>
                <p style="color:#9fb3c8;"><strong style="color:#d4af37;">Dónde:</strong> <?= e($p['donde'] ?? 'Consultar') ?></p>
              </div>

              <?php if ($p['notas']): ?>
                <div style="background:rgba(243,156,18,0.1); border-left:3px solid #f39c12; padding:10px; border-radius:6px; margin-top:12px; font-size:0.8rem; color:#f39c12;">
                  💡 <?= e($p['notas']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Aviso -->
    <div style="background:rgba(243,156,18,0.1); border-left:4px solid #f39c12; padding:15px; border-radius:8px; margin-top:30px;">
      <p style="color:#f39c12; font-size:0.9rem;">
        ⚠️ <strong>Aviso:</strong> Estas recomendaciones son educativas y no constituyen asesoría financiera regulada. 
        Los rendimientos de la reventa dependen de tu capacidad de venta. Verifica siempre los precios con los proveedores.
      </p>
    </div>

  </main>
</div>

<style>
  .contenido-acordeon { transition: all 0.3s ease; }
  .icono.abierto { transform: rotate(180deg); }
</style>

<script>
// ============================================
// FUNCIONES DE ACORDEÓN
// ============================================
function toggleAcordeon(elemento) {
  const contenido = elemento.nextElementSibling;
  const icono = elemento.querySelector('.icono');
  if (contenido.style.display === 'none' || contenido.style.display === '') {
    contenido.style.display = 'block';
    icono.classList.add('abierto');
  } else {
    contenido.style.display = 'none';
    icono.classList.remove('abierto');
  }
}

function toggleAcordeonProducto(elemento) {
  const contenido = elemento.nextElementSibling;
  const icono = elemento.querySelector('.icono');
  if (contenido.style.display === 'none' || contenido.style.display === '') {
    contenido.style.display = 'block';
    icono.classList.add('abierto');
    // Inicializar gráfico si no se ha hecho
    const canvas = contenido.querySelector('canvas');
    if (canvas && !canvas.dataset.initialized) {
      inicializarGrafico(canvas);
      canvas.dataset.initialized = 'true';
    }
  } else {
    contenido.style.display = 'none';
    icono.classList.remove('abierto');
  }
}

// ============================================
// GRÁFICOS
// ============================================
function inicializarGrafico(canvas) {
  const ctx = canvas.getContext('2d');
  const id = canvas.id;
  const index = parseInt(id.replace('graficoProducto', ''));
  
  // Obtener los datos del producto desde el DOM (data attributes)
  const card = canvas.closest('.producto-tangible');
  const inversion = parseFloat(card.dataset.inversion || 0);
  const ganancia = parseFloat(card.dataset.ganancia || 0);
  const ingreso = parseFloat(card.dataset.ingreso || 0);

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Inversión', 'Ingreso total', 'Ganancia'],
      datasets: [{
        label: 'Proyección Financiera',
        data: [inversion, ingreso, ganancia],
        backgroundColor: ['rgba(231,76,60,0.7)', 'rgba(52,152,219,0.7)', 'rgba(39,174,96,0.7)'],
        borderColor: ['#e74c3c', '#3498db', '#27ae60'],
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
}

// ============================================
// ACTUALIZAR PRODUCTOS VÍA AJAX
// ============================================
async function actualizarProductos() {
  const presupuesto = document.getElementById('presupuestoInput').value;
  const estado = document.getElementById('estadoCalculo');
  const container = document.getElementById('productosTangiblesContainer');

  if (!presupuesto || presupuesto <= 0) {
    toast('warning', 'Ingresa un monto', 'Escribe cuánto quieres invertir para ver la proyección');
    return;
  }

  estado.style.display = 'block';
  container.style.opacity = '0.5';

  try {
    const response = await fetch(`index.php?accion=productosAPI&presupuesto=${presupuesto}`);
    const data = await response.json();

    if (!data.success) {
      toast('error', 'Error', 'No se pudieron calcular los productos');
      return;
    }

    // Renderizar los productos
    if (data.productos.length === 0) {
      container.innerHTML = `<div class="card" style="text-align:center; padding:40px;">
        <p style="color:#9fb3c8;">No hay productos que se ajusten a un presupuesto de ${formatoCOP(presupuesto)}.</p>
      </div>`;
    } else {
      let html = '';
      data.productos.forEach((p, index) => {
        html += `
          <div class="card producto-tangible" style="margin-bottom:12px; border-left:4px solid #9b59b6;" 
               data-inversion="${p.inversion_total}" data-ganancia="${p.ganancia_total}" data-ingreso="${p.ingreso_total}">
            <div onclick="toggleAcordeonProducto(this)" style="cursor:pointer; display:flex; justify-content:space-between; align-items:center; padding:5px 0;">
              <div>
                <h3 style="color:#d5a6ff; font-size:1.1rem; margin:0;">${escapeHtml(p.nombre)}</h3>
                <p style="color:#9fb3c8; font-size:0.8rem; margin-top:3px;">${capitalizar(p.categoria)} · ROI: ${p.roi}%</p>
              </div>
              <span class="icono" style="font-size:1.5rem; color:#d5a6ff; transition:transform 0.3s;">▾</span>
            </div>
            <div class="contenido-acordeon" style="display:none; margin-top:15px; padding-top:15px; border-top:1px solid rgba(155,89,182,0.2);">
              <div style="background:rgba(10,25,41,0.6); padding:15px; border-radius:10px; margin-bottom:15px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:0.9rem;">
                  <div><span style="color:#9fb3c8;">Inversión total:</span> <strong style="color:#e6edf3;">${formatoCOP(p.inversion_total)}</strong></div>
                  <div><span style="color:#9fb3c8;">Ingreso por venta:</span> <strong style="color:#e6edf3;">${formatoCOP(p.ingreso_total)}</strong></div>
                  <div><span style="color:#9fb3c8;">Unidades compradas:</span> <strong style="color:#e6edf3;">${p.unidades_compradas}</strong></div>
                  <div><span style="color:#9fb3c8;">Ganancia total:</span> <strong style="color:#27ae60;">+${formatoCOP(p.ganancia_total)}</strong></div>
                  <div><span style="color:#9fb3c8;">ROI:</span> <strong style="color:#f39c12;">${p.roi}%</strong></div>
                  <div><span style="color:#9fb3c8;">Margen:</span> <strong style="color:#f39c12;">${p.margen}%</strong></div>
                </div>
              </div>
              <div style="background:rgba(10,25,41,0.5); padding:15px; border-radius:10px; margin-bottom:15px;">
                <canvas id="graficoProducto${index}" height="100"></canvas>
              </div>
              <div style="font-size:0.85rem;">
                <p style="color:#9fb3c8; margin-bottom:5px;"><strong style="color:#d4af37;">Proveedor:</strong> ${escapeHtml(p.proveedor || 'No especificado')}</p>
                <p style="color:#9fb3c8;"><strong style="color:#d4af37;">Dónde:</strong> ${escapeHtml(p.donde || 'Consultar')}</p>
              </div>
              ${p.notas ? `<div style="background:rgba(243,156,18,0.1); border-left:3px solid #f39c12; padding:10px; border-radius:6px; margin-top:12px; font-size:0.8rem; color:#f39c12;">💡 ${escapeHtml(p.notas)}</div>` : ''}
            </div>
          </div>
        `;
      });
      container.innerHTML = html;
    }

    toast('success', 'Proyección actualizada', 'Los productos se recalcularon según tu presupuesto');
  } catch (e) {
    toast('error', 'Error', 'No se pudo conectar con el servidor');
  } finally {
    estado.style.display = 'none';
    container.style.opacity = '1';
  }
}

// Utilidades
function formatoCOP(valor) {
  return '$' + parseFloat(valor).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}
function capitalizar(texto) {
  return texto ? texto.charAt(0).toUpperCase() + texto.slice(1) : '';
}
function escapeHtml(texto) {
  const div = document.createElement('div');
  div.textContent = texto || '';
  return div.innerHTML;
}
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>