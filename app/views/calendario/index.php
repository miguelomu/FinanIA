<?php include __DIR__ . '/../../../includes/header.php'; ?>

<style>
  .calendario-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
  }
  
  .calendario-titulo {
    color: #d4af37;
    font-size: 1.8rem;
    font-weight: 700;
  }
  
  .calendario-nav {
    display: flex;
    gap: 10px;
    align-items: center;
  }
  
  .calendario-nav .btn {
    padding: 10px 18px;
  }
  
  .calendario-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    background: rgba(19, 47, 76, 0.5);
    padding: 15px;
    border-radius: 15px;
    border: 1px solid rgba(212, 175, 55, 0.2);
  }
  
  .dia-semana {
    text-align: center;
    color: #d4af37;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2);
  }
  
  .dia {
    min-height: 100px;
    background: rgba(10, 25, 41, 0.5);
    border: 1px solid rgba(212, 175, 55, 0.1);
    border-radius: 10px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.25s;
    position: relative;
    overflow: hidden;
  }
  
  .dia:hover {
    border-color: rgba(212, 175, 55, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.15);
  }
  
  .dia.vacio {
    background: transparent;
    border: none;
    cursor: default;
  }
  
  .dia.vacio:hover {
    transform: none;
    box-shadow: none;
  }
  
  .dia.hoy {
    border: 2px solid #d4af37;
    background: rgba(212, 175, 55, 0.1);
  }
  
  .dia-numero {
    font-weight: 700;
    color: #e6edf3;
    font-size: 1rem;
    margin-bottom: 6px;
  }
  
  .dia.hoy .dia-numero {
    color: #d4af37;
  }
  
  .dia-indicadores {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  
  .dia-indicador {
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .dia-indicador.ingreso {
    background: rgba(39, 174, 96, 0.25);
    color: #7ee8a8;
  }
  
  .dia-indicador.gasto {
    background: rgba(231, 76, 60, 0.25);
    color: #ff8a80;
  }
  
  .dia-indicador.deuda {
    background: rgba(243, 156, 18, 0.25);
    color: #f39c12;
  }
  
  .dia-indicador.meta {
    background: rgba(155, 89, 182, 0.25);
    color: #d5a6ff;
  }
  
  .resumen-mes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
  }
  
  /* Leyenda */
  .leyenda {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 20px;
    padding: 15px;
    background: rgba(19, 47, 76, 0.3);
    border-radius: 10px;
    justify-content: center;
  }
  
  .leyenda-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #9fb3c8;
    font-size: 0.85rem;
  }
  
  .leyenda-color {
    width: 14px;
    height: 14px;
    border-radius: 4px;
  }
  
  /* Modo claro */
  [data-tema="claro"] .calendario-grid {
    background: #f8f9fa;
    border-color: rgba(212, 175, 55, 0.3);
  }
  
  [data-tema="claro"] .dia {
    background: #ffffff;
    border-color: rgba(212, 175, 55, 0.2);
  }
  
  [data-tema="claro"] .dia-numero {
    color: #1a1a1a;
  }
  
  [data-tema="claro"] .leyenda {
    background: #f8f9fa;
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .calendario-titulo {
      font-size: 1.3rem;
    }
    .dia {
      min-height: 70px;
      padding: 6px;
    }
    .dia-indicador {
      font-size: 0.6rem;
      padding: 1px 3px;
    }
  }

    .dia-indicador.recurrente {
    background: rgba(52, 152, 219, 0.25);
    color: #6bb6ff;
  }
  
  .dia-con-recurrentes {
    border-style: dashed !important;
  }
</style>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>📅 Mi Calendario Financiero</h1>
      <a href="index.php" class="btn btn-primary">
        📅 Ir al mes actual
      </a>
    </div>

    <!-- Resumen del mes -->
    <div class="resumen-mes">
      <div class="card">
        <h3>💰 Ingresos del mes</h3>
        <div class="value positivo"><?= formatoCOP($resumen['ingresos']) ?></div>
      </div>
      <div class="card">
        <h3>💸 Gastos del mes</h3>
        <div class="value negativo"><?= formatoCOP($resumen['gastos']) ?></div>
      </div>
      <div class="card">
        <h3>📊 Balance</h3>
        <div class="value <?= $resumen['balance'] >= 0 ? 'positivo' : 'negativo' ?>">
          <?= formatoCOP($resumen['balance']) ?>
        </div>
      </div>
      <div class="card">
        <h3>📝 Movimientos</h3>
        <div class="value"><?= $resumen['total'] ?></div>
      </div>
    </div>

    <!-- Navegación del mes -->
    <?php
    $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    ?>
    <div class="calendario-header">
      <h2 class="calendario-titulo">
        <?= $meses[$mes] ?> <?= $anio ?>
      </h2>
      <div class="calendario-nav">
        <a href="?mes=<?= $mesAnterior ?>&anio=<?= $anioAnterior ?>" class="btn btn-secondary">
          ← Anterior
        </a>
        <a href="index.php" class="btn btn-secondary">
          Hoy
        </a>
        <a href="?mes=<?= $mesSiguiente ?>&anio=<?= $anioSiguiente ?>" class="btn btn-secondary">
          Siguiente →
        </a>
      </div>
    </div>

    <!-- Calendario -->
    <div class="calendario-grid">
      <!-- Días de la semana -->
      <div class="dia-semana">Lun</div>
      <div class="dia-semana">Mar</div>
      <div class="dia-semana">Mié</div>
      <div class="dia-semana">Jue</div>
      <div class="dia-semana">Vie</div>
      <div class="dia-semana">Sáb</div>
      <div class="dia-semana">Dom</div>

      <?php
      $primerDia = mktime(0, 0, 0, $mes, 1, $anio);
      $diasEnMes = intval(date('t', $primerDia));
      $diaSemanaInicio = intval(date('N', $primerDia)) - 1; // 0=lunes, 6=domingo
      $hoy = date('Y-m-d');

      // Celdas vacías al inicio
      for ($i = 0; $i < $diaSemanaInicio; $i++) {
          echo '<div class="dia vacio"></div>';
      }

      // Días del mes
      for ($dia = 1; $dia <= $diasEnMes; $dia++) {
          $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
          $esHoy = $fecha === $hoy;
          $eventosDia = $eventos[$fecha] ?? null;

          $clases = 'dia';
          if ($esHoy) $clases .= ' hoy';
      ?>
        <div class="<?= $clases ?>" onclick="verDia('<?= $fecha ?>')">
          <div class="dia-numero"><?= $dia ?></div>
          <div class="dia-indicadores">
            <?php if ($eventosDia): 
              // Mostrar indicadores
              $numTrans = count($eventosDia['transacciones']);
              $numDeudas = count($eventosDia['deudas']);
              $numMetas = count($eventosDia['metas']);
              $numRecurrentes = count($eventosDia['recurrentes'] ?? []);

              // Totales incluyendo recurrentes proyectadas
              $totIngresos = $eventosDia['totales']['ingresos'];
              $totGastos = $eventosDia['totales']['gastos'];
            ?>
              <?php if ($totIngresos > 0): ?>
                <div class="dia-indicador ingreso">💰 +<?= number_format($totIngresos / 1000, 0) ?>k</div>
              <?php endif; ?>
              <?php if ($totGastos > 0): ?>
                <div class="dia-indicador gasto">💸 -<?= number_format($totGastos / 1000, 0) ?>k</div>
              <?php endif; ?>

              <?php if ($numRecurrentes > 0): ?>
                <div class="dia-indicador recurrente" title="<?= $numRecurrentes ?> pago(s) recurrente(s) proyectado(s)">🔄 <?= $numRecurrentes ?> recurrente<?= $numRecurrentes > 1 ? 's' : '' ?></div>
              <?php endif; ?>

              <?php if ($numDeudas > 0): ?>
                <div class="dia-indicador deuda">💳 <?= $numDeudas ?> venc.</div>
              <?php endif; ?>

              <?php if ($numMetas > 0): ?>
                <div class="dia-indicador meta">🎯 meta</div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php } ?>
    </div>

    <!-- Leyenda -->
    <div class="leyenda">
      <div class="leyenda-item">
        <div class="leyenda-color" style="background:rgba(39,174,96,0.5);"></div>
        <span>💰 Ingresos</span>
      </div>
      <div class="leyenda-item">
        <div class="leyenda-color" style="background:rgba(231,76,60,0.5);"></div>
        <span>💸 Gastos</span>
      </div>
      <div class="leyenda-item">
        <div class="leyenda-color" style="background:rgba(243,156,18,0.5);"></div>
        <span>💳 Vencimiento de deuda</span>
      </div>
      <div class="leyenda-item">
        <div class="leyenda-color" style="background:rgba(155,89,182,0.5);"></div>
        <span>🎯 Meta con fecha límite</span>
      </div>
    </div>

  </main>
</div>

<!-- Modal de detalle del día -->
<div class="modal-overlay" id="modalDia">
  <div class="modal-confirm" style="max-width: 600px; text-align: left;">
    <h3 class="modal-titulo" id="modalDiaTitulo">Detalle del día</h3>
    <div id="modalDiaContenido" style="margin-top: 20px;"></div>
    <div class="modal-botones" style="margin-top: 25px;">
      <button class="btn btn-secondary" onclick="cerrarModalDia()">Cerrar</button>
    </div>
  </div>
</div>

<script>
async function verDia(fecha) {
  const modal = document.getElementById('modalDia');
  const titulo = document.getElementById('modalDiaTitulo');
  const contenido = document.getElementById('modalDiaContenido');
  
  contenido.innerHTML = '<p style="color:#9fb3c8; text-align:center;">Cargando...</p>';
  modal.classList.add('activo');

  try {
    const response = await fetch(`index.php?accion=dia&fecha=${fecha}`);
    const data = await response.json();

    if (!data.success) {
      contenido.innerHTML = '<p style="color:#e74c3c;">Error al cargar</p>';
      return;
    }

    titulo.textContent = `📅 ${data.fecha_formateada}`;

    let html = '';

    // Resumen del día
    html += `
      <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:20px;">
        <div style="background:rgba(39,174,96,0.15); padding:12px; border-radius:8px; text-align:center;">
          <div style="color:#7ee8a8; font-size:0.75rem; margin-bottom:4px;">Ingresos</div>
          <div style="color:#7ee8a8; font-weight:bold;">${data.ingresos_formateado}</div>
        </div>
        <div style="background:rgba(231,76,60,0.15); padding:12px; border-radius:8px; text-align:center;">
          <div style="color:#ff8a80; font-size:0.75rem; margin-bottom:4px;">Gastos</div>
          <div style="color:#ff8a80; font-weight:bold;">${data.gastos_formateado}</div>
        </div>
        <div style="background:rgba(212,175,55,0.15); padding:12px; border-radius:8px; text-align:center;">
          <div style="color:#d4af37; font-size:0.75rem; margin-bottom:4px;">Balance</div>
          <div style="color:#d4af37; font-weight:bold;">${data.balance_formateado}</div>
        </div>
      </div>
    `;

    // Lista de transacciones
    const todasLasTrans = [
      ...data.detalle.transacciones.map(t => ({...t, esRecurrente: false})),
      ...(data.detalle.recurrentes || []).map(r => ({...r, esRecurrente: true, monto_formateado: formatCOP(r.monto)}))
    ];

    if (todasLasTrans.length > 0) {
      html += '<div style="display:flex; flex-direction:column; gap:10px;">';
      todasLasTrans.forEach(t => {
        const signo = t.tipo === 'ingreso' ? '+' : '-';
        const color = t.tipo === 'ingreso' ? '#7ee8a8' : '#ff8a80';
        const badge = t.esRecurrente ? '<span style="background:rgba(52,152,219,0.3); color:#6bb6ff; padding:2px 6px; border-radius:4px; font-size:0.65rem; margin-left:6px;">🔄 PROYECTADO</span>' : '';
        
        html += `
          <div class="fila-transaccion" style="${t.esRecurrente ? 'border:1px dashed rgba(52,152,219,0.4);' : ''}">
            <div class="fila-transaccion-info">
              <div class="fila-transaccion-titulo">${escapeHtml(t.descripcion || 'Sin descripción')}${badge}</div>
              <div class="fila-transaccion-meta">
                ${escapeHtml(t.categoria_nombre || t.categoria || 'Sin categoría')}${t.cuenta_nombre ? ' · ' + escapeHtml(t.cuenta_nombre) : ''}
              </div>
            </div>
            <div class="fila-transaccion-monto" style="color:${color};">
              ${signo}${t.monto_formateado}
            </div>
          </div>
        `;
      });
      html += '</div>';
    } else {
      html += '<p style="color:#9fb3c8; text-align:center; padding:20px;">No hay transacciones este día</p>';
    }

    contenido.innerHTML = html;

  } catch (e) {
    contenido.innerHTML = '<p style="color:#e74c3c;">Error de conexión</p>';
  }
}

function cerrarModalDia() {
  document.getElementById('modalDia').classList.remove('activo');
}

function escapeHtml(texto) {
  const div = document.createElement('div');
  div.textContent = texto || '';
  return div.innerHTML;
}

// Cerrar con ESC
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') cerrarModalDia();
});

// Cerrar al hacer clic fuera
document.getElementById('modalDia').addEventListener('click', function(e) {
  if (e.target === this) cerrarModalDia();
});

function formatCOP(valor) {
  return '$' + parseFloat(valor).toLocaleString('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  });
}
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>