<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💵 Registrar Pago</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="meta-card" style="max-width:600px;">
      <h3><?= htmlspecialchars($deuda['acreedor']) ?></h3>
      <div class="meta-info">
        <span>Deuda total: <strong>$<?= number_format($deuda['monto_total'], 2) ?></strong></span>
        <span>Pagado: <strong>$<?= number_format($deuda['monto_pagado'], 2) ?></strong></span>
        <span>Pendiente: <strong>$<?= number_format($deuda['monto_total'] - $deuda['monto_pagado'], 2) ?></strong></span>
      </div>

      <form action="index.php?accion=pagar&id=<?= $deuda['id_deuda'] ?>" method="POST" style="margin-top:20px;">
        <?= csrfField() ?>
        <div class="form-group">
          <label>Monto a pagar *</label>
          <input type="number" name="monto" step="0.01" min="0.01" required 
                 placeholder="0.00" autofocus>
        </div>

        <div class="form-group">
          <label>Nota (opcional)</label>
          <input type="text" name="nota" maxlength="255" 
                 placeholder="Ej: Pago mensual, Abono extra...">
        </div>

        <div class="form-group">
          <label>Cuenta de origen *</label>
          <select name="id_cuenta" required>
            <option value="">Selecciona...</option>
            <?php foreach ($cuentas as $c): ?>
              <option value="<?= $c['id_cuenta'] ?>">
                <?= e($c['nombre']) ?> — <?= formatoCOP($c['saldo_actual']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small style="color:#9fb3c8; font-size:0.8rem;">
            ¿De qué cuenta sale el dinero?
          </small>
        </div>

        <div style="display:flex; gap:15px; margin-top:20px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">Registrar Pago</button>
          <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>