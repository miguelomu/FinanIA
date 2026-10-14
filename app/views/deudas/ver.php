<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💳 <?= htmlspecialchars($deuda['acreedor']) ?></h1>
      <div style="display:flex; gap:10px;">
        <?php if ($deuda['estado'] !== 'pagada'): ?>
          <a href="index.php?accion=pagar&id=<?= $deuda['id_deuda'] ?>" class="btn btn-primary">+ Nuevo Pago</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
      </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'pagado' => 'Pago registrado exitosamente',
          'editado' => 'Pago actualizado exitosamente',
          'eliminado' => 'Pago eliminado exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <div class="cards-grid">
      <div class="card">
        <h3>Monto Total</h3>
        <div class="value">$<?= number_format($deuda['monto_total'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Total Pagado</h3>
        <div class="value positivo">$<?= number_format($deuda['monto_pagado'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Pendiente</h3>
        <div class="value negativo">$<?= number_format($deuda['monto_total'] - $deuda['monto_pagado'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Progreso</h3>
        <div class="value"><?= number_format($progreso, 1) ?>%</div>
      </div>
    </div>

    <div class="progress" style="margin-bottom:30px;">
      <div class="progress-bar <?= $progreso >= 100 ? 'success' : '' ?>" style="width: <?= $progreso ?>%;"></div>
    </div>

    <h2 style="color:#d4af37; margin-bottom:20px;">Historial de Pagos</h2>

    <?php if (empty($pagos)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.1rem; margin-bottom:15px;">📭 Aún no has hecho pagos a esta deuda</p>
        <?php if ($deuda['estado'] !== 'pagada'): ?>
          <a href="index.php?accion=pagar&id=<?= $deuda['id_deuda'] ?>" class="btn btn-primary">Registrar primer pago</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Cuenta</th>
              <th>Nota</th>
              <th style="text-align:right;">Monto</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pagos as $p): ?>
              <tr>
                <td><?= formatoFechaHora($p['fecha']) ?></td>
                <td>
                  <?php if ($p['cuenta_nombre']): ?>
                    <span style="padding:3px 8px; border-radius:8px; font-size:0.75rem;
                      background:<?= e($p['cuenta_color']) ?>22; color:<?= e($p['cuenta_color']) ?>;">
                      <?= e($p['cuenta_nombre']) ?>
                    </span>
                  <?php else: ?>
                    <span style="color:#7a8a9c;">—</span>
                  <?php endif; ?>
                </td>
                <td><?= e($p['nota'] ?: '—') ?></td>
                <td style="text-align:right; color:#27ae60; font-weight:bold;">
                  +$<?= number_format($p['monto'], 2) ?>
                </td>
                <td style="text-align:center;">
                  <div class="acciones" style="justify-content:center;">
                    <a href="index.php?accion=editarPago&id=<?= $p['id_pago'] ?>" class="btn-sm btn-editar">✏️ Editar</a>
                    <a href="index.php?accion=eliminarPago&id=<?= $p['id_pago'] ?>" 
                       class="btn-sm btn-eliminar"
                       onclick="return confirm('¿Eliminar este pago?')">🗑️ Eliminar</a>
                  </div>
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