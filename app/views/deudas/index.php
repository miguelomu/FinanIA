<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💳 Mis Deudas</h1>
      <a href="index.php?accion=crear" class="btn btn-primary">+ Nueva Deuda</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'creado' => 'Deuda registrada exitosamente',
          'actualizado' => 'Deuda actualizada exitosamente',
          'eliminado' => 'Deuda eliminada exitosamente',
          'pagado' => 'Pago registrado exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <div class="cards-grid">
      <div class="card">
        <h3>Deuda Total</h3>
        <div class="value negativo">$<?= number_format($totales['total'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Total Pagado</h3>
        <div class="value positivo">$<?= number_format($totales['pagado'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Saldo Pendiente</h3>
        <div class="value negativo">$<?= number_format($totales['pendiente'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Deudas Activas</h3>
        <div class="value"><?= count(array_filter($deudas, fn($d) => $d['estado'] === 'activa')) ?></div>
      </div>
    </div>

    <div style="background:rgba(19,47,76,0.5); padding:20px; border-radius:12px; margin-bottom:20px;">
      <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="index.php" class="btn <?= empty($estado) ? 'btn-primary' : 'btn-secondary' ?>">Todas</a>
        <a href="index.php?estado=activa" class="btn <?= $estado === 'activa' ? 'btn-primary' : 'btn-secondary' ?>">Activas</a>
        <a href="index.php?estado=pagada" class="btn <?= $estado === 'pagada' ? 'btn-primary' : 'btn-secondary' ?>">Pagadas</a>
        <a href="index.php?estado=vencida" class="btn <?= $estado === 'vencida' ? 'btn-primary' : 'btn-secondary' ?>">Vencidas</a>
      </div>
    </div>

    <?php if (empty($deudas)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">💳 No tienes deudas registradas</p>
        <a href="index.php?accion=crear" class="btn btn-primary">Registrar primera deuda</a>
      </div>
    <?php else: ?>
      <?php foreach ($deudas as $d): 
        $progreso = $d['monto_total'] > 0 
            ? min(100, ($d['monto_pagado'] / $d['monto_total']) * 100) 
            : 0;
        $claseBarra = $progreso >= 100 ? 'success' : ($progreso >= 50 ? '' : 'warning');
        $estadoColor = [
            'activa' => '#f39c12',
            'pagada' => '#27ae60',
            'vencida' => '#e74c3c'
        ][$d['estado']] ?? '#3498db';
        
        $diasRestantes = (strtotime($d['fecha_vencimiento']) - time()) / 86400;
      ?>
        <div class="meta-card" style="border-left:4px solid <?= $estadoColor ?>;">
          <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:15px;">
            <h3 style="margin:0;"><?= htmlspecialchars($d['acreedor']) ?></h3>
            <span style="padding:4px 12px; border-radius:12px; font-size:0.75rem; font-weight:bold;
              background: <?= $estadoColor ?>22; color: <?= $estadoColor ?>;">
              <?= ucfirst($d['estado']) ?>
            </span>
          </div>

          <div class="meta-info">
            <span>Monto total: <strong>$<?= number_format($d['monto_total'], 2) ?></strong></span>
            <span>Pagado: <strong>$<?= number_format($d['monto_pagado'], 2) ?></strong></span>
            <span>Pendiente: <strong>$<?= number_format($d['monto_total'] - $d['monto_pagado'], 2) ?></strong></span>
          </div>

          <div class="meta-info">
            <span>Tasa interés: <strong><?= number_format($d['tasa_interes'], 2) ?>%</strong></span>
            <span>Pago mínimo: <strong>$<?= number_format($d['pago_minimo'], 2) ?></strong></span>
            <span>Vence: <strong><?= date('d/m/Y', strtotime($d['fecha_vencimiento'])) ?></strong></span>
          </div>

          <?php if ($d['estado'] === 'activa' && $diasRestantes > 0 && $diasRestantes <= 30): ?>
            <div style="background:rgba(243,156,18,0.15); border-left:3px solid #f39c12; padding:10px; border-radius:6px; margin:10px 0;">
              ⚠️ <strong>Vence en <?= floor($diasRestantes) ?> días</strong>
            </div>
          <?php endif; ?>

          <?php if ($d['tasa_interes'] > $tasaUsura): ?>
            <div style="background:rgba(231,76,60,0.2); border-left:4px solid #e74c3c; padding:12px; border-radius:6px; margin:10px 0;">
              <strong style="color: #ff8a80;">🚨 ¡ALERTA! Tasa de usura</strong>
              <p style="color: #ff8a80; font-size:0.85rem; margin-top:5px;">
                Esta deuda cobra <strong><?= number_format($d['tasa_interes'], 2) ?>% EA</strong>, 
                superando la tasa de usura vigente (<strong><?= number_format($tasaUsura, 2) ?>% EA</strong>).
                Esto es ilegal en Colombia. Puedes denunciar ante la Superintendencia Financiera.
              </p>
            </div>
          <?php elseif ($d['tasa_interes'] > $tasaUsura * 0.85): ?>
            <div style="background:rgba(243,156,18,0.15); border-left:4px solid #f39c12; padding:12px; border-radius:6px; margin:10px 0;">
              <strong style="color: #f39c12;">⚠️ Tasa alta</strong>
              <p style="color: #f39c12; font-size:0.85rem; margin-top:5px;">
                Esta deuda cobra <strong><?= number_format($d['tasa_interes'], 2) ?>% EA</strong>, 
                cerca del límite de usura (<strong><?= number_format($tasaUsura, 2) ?>%</strong>).
                Considera renegociar o buscar alternativas.
              </p>
            </div>
          <?php endif; ?>

          <div class="progress">
            <div class="progress-bar <?= $claseBarra ?>" style="width: <?= $progreso ?>%;"></div>
          </div>

          <div class="btn-group" style="margin-top:15px;">
            <?php if ($d['estado'] !== 'pagada'): ?>
              <a href="index.php?accion=pagar&id=<?= $d['id_deuda'] ?>" class="btn btn-primary">+ Registrar Pago</a>
            <?php endif; ?>
            <a href="index.php?accion=ver&id=<?= $d['id_deuda'] ?>" class="btn btn-secondary">📋 Ver Pagos</a>
            <a href="index.php?accion=editar&id=<?= $d['id_deuda'] ?>" class="btn btn-secondary">✏️ Editar</a>
            <a href="index.php?accion=eliminar&id=<?= $d['id_deuda'] ?>" 
               class="btn btn-secondary"
               style="color: #e74c3c;"
               onclick="return confirm('¿Eliminar esta deuda? Se eliminarán también sus pagos.')">🗑️ Eliminar</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>