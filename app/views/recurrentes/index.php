<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>🔄 Gastos Recurrentes</h1>
    </div>

    <!-- Impacto anual -->
    <div class="cards-grid">
      <div class="card" style="border:2px solid #e74c3c;">
        <h3>💸 Gastos recurrentes mensuales</h3>
        <div class="value negativo" style="font-size:1.8rem;">
          <?= formatoCOP($impacto['gastos_mensual']) ?>
        </div>
      </div>
      <div class="card" style="border:2px solid #e74c3c;">
        <h3>📅 Impacto ANUAL</h3>
        <div class="value negativo" style="font-size:1.8rem;">
          <?= formatoCOP($impacto['gastos_anual']) ?>
        </div>
        <p style="color:#9fb3c8; font-size:0.75rem; margin-top:8px;">
          Es lo que gastas en un año completo
        </p>
      </div>
      <div class="card" style="border:2px solid #27ae60;">
        <h3>💰 Ingresos recurrentes</h3>
        <div class="value positivo" style="font-size:1.8rem;">
          <?= formatoCOP($impacto['ingresos_mensual']) ?>
        </div>
      </div>
      <div class="card">
        <h3>📊 Balance recurrente</h3>
        <div class="value <?= $impacto['balance_mensual'] >= 0 ? 'positivo' : 'negativo' ?>">
          <?= formatoCOP($impacto['balance_mensual']) ?>
        </div>
      </div>
    </div>

    <!-- Gastos recurrentes activos -->
    <h2 style="color:#d4af37; margin:35px 0 20px;">🔄 Tus movimientos recurrentes</h2>

    <?php if (empty($gastos)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.1rem; margin-bottom:15px;">🔄 No tienes movimientos recurrentes registrados</p>
        <p style="color:#9fb3c8;">
          Marca la casilla "recurrente" al crear una transacción para que aparezca aquí.
        </p>
      </div>
    <?php else: ?>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Descripción</th>
              <th>Categoría</th>
              <th>Cuenta</th>
              <th style="text-align:right;">Monto</th>
              <th style="text-align:right;">Anual</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($gastos as $g): ?>
              <tr>
                <td>
                  <span style="padding:3px 8px; border-radius:8px; font-size:0.75rem; font-weight:bold;
                    background: <?= $g['tipo'] === 'ingreso' ? 'rgba(39,174,96,0.2)' : 'rgba(231,76,60,0.2)' ?>;
                    color: <?= $g['tipo'] === 'ingreso' ? '#7ee8a8' : '#ff8a80' ?>;">
                    <?= ucfirst($g['tipo']) ?>
                  </span>
                </td>
                <td><?= e($g['descripcion'] ?: '—') ?></td>
                <td>
                  <?php if ($g['categoria_nombre']): ?>
                    <span style="padding:3px 8px; border-radius:8px; font-size:0.75rem;
                      background:<?= e($g['categoria_color']) ?>22; color:<?= e($g['categoria_color']) ?>;">
                      <?= e($g['categoria_nombre']) ?>
                    </span>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td><?= e($g['cuenta_nombre'] ?? '—') ?></td>
                <td style="text-align:right; font-weight:bold;">
                  <?= formatoCOP($g['monto']) ?>
                </td>
                <td style="text-align:right; color:#f39c12; font-weight:bold;">
                  <?= formatoCOP($g['monto'] * 12) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <!-- Suscripciones detectadas -->
    <?php if (!empty($suscripciones)): ?>
      <h2 style="color:#d4af37; margin:40px 0 20px;">🔍 Posibles suscripciones detectadas</h2>
      <p style="color:#9fb3c8; margin-bottom:20px; font-size:0.9rem;">
        Estos son gastos pequeños que se repiten varias veces. Pueden ser suscripciones 
        o gastos hormiga. Revísalos.
      </p>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Descripción</th>
              <th>Categoría</th>
              <th style="text-align:center;">Veces</th>
              <th style="text-align:right;">Promedio</th>
              <th style="text-align:right;">Total gastado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($suscripciones as $s): ?>
              <tr>
                <td><?= e($s['descripcion']) ?></td>
                <td><?= e($s['categoria'] ?? '—') ?></td>
                <td style="text-align:center;"><?= $s['veces'] ?> veces</td>
                <td style="text-align:right;"><?= formatoCOP($s['monto_promedio']) ?></td>
                <td style="text-align:right; color:#e74c3c; font-weight:bold;">
                  <?= formatoCOP($s['monto_total']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>