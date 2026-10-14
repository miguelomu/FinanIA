<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>👥 <?= e($gasto['concepto']) ?></h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'pagado'): ?>
      <div class="alert alert-success">✅ Pago registrado</div>
    <?php endif; ?>

    <div class="cards-grid">
      <div class="card">
        <h3>Monto total</h3>
        <div class="value"><?= formatoCOP($gasto['monto_total']) ?></div>
      </div>
      <div class="card">
        <h3>Fecha</h3>
        <div class="value" style="font-size:1.3rem;"><?= formatoFecha($gasto['fecha']) ?></div>
      </div>
      <div class="card">
        <h3>Pagó</h3>
        <div class="value" style="font-size:1.3rem;"><?= e($gasto['pagador_nombre']) ?></div>
      </div>
      <div class="card">
        <h3>Cuenta</h3>
        <div class="value" style="font-size:1.3rem;"><?= e($gasto['cuenta_nombre'] ?? '—') ?></div>
      </div>
    </div>

    <?php if ($gasto['notas']): ?>
      <div class="card" style="margin-top:20px;">
        <h3>Notas</h3>
        <p style="color: #c9d6e3; margin-top:10px;"><?= nl2br(e($gasto['notas'])) ?></p>
      </div>
    <?php endif; ?>

    <h2 style="color: #d4af37; margin:30px 0 20px;">👤 Participantes</h2>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Persona</th>
            <th style="text-align:right;">Le corresponde</th>
            <th style="text-align:center;">Estado</th>
            <th style="text-align:center;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($participantes as $p): ?>
            <tr>
              <td>
                <span style="padding:3px 8px; border-radius:8px; font-size:0.8rem;
                  background:<?= e($p['contacto_color']) ?>22; color:<?= e($p['contacto_color']) ?>;">
                  <?= e($p['contacto_nombre']) ?>
                </span>
              </td>
              <td style="text-align:right; font-weight:bold;">
                <?= formatoCOP($p['monto_corresponde']) ?>
              </td>
              <td style="text-align:center;">
                <?php if ($p['pagado']): ?>
                  <span style="color: #27ae60; font-weight:bold;">✓ Pagado</span>
                <?php else: ?>
                  <span style="color: #f39c12; font-weight:bold;">⏳ Pendiente</span>
                <?php endif; ?>
              </td>
              <td style="text-align:center;">
                <?php if (!$p['pagado']): ?>
                  <a href="index.php?accion=marcarPagado&id=<?= $p['id_participante'] ?>&gasto_id=<?= $gasto['id_gasto'] ?>" 
                     class="btn-sm btn-ver"
                     onclick="return confirm('¿Marcar como pagado?')">💵 Marcar Pagado</a>
                <?php else: ?>
                  <small style="color: #7a8a9c;"><?= formatoFechaHora($p['fecha_pago']) ?></small>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>