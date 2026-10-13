<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💰 Aportar a Meta</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="meta-card" style="max-width:600px;">
      <h3><?= htmlspecialchars($meta['nombre']) ?></h3>
      <div class="meta-info">
        <span>Objetivo: <strong>$<?= number_format($meta['monto_objetivo'], 2) ?></strong></span>
        <span>Monto inicial: <strong>$<?= number_format($meta['monto_inicial'], 2) ?></strong></span>
        <span>Aportes: <strong>$<?= number_format($meta['monto_actual'] - $meta['monto_inicial'], 2) ?></strong></span>
        <span>Total ahorrado: <strong>$<?= number_format($meta['monto_actual'], 2) ?></strong></span>
      </div>

      <form action="index.php?accion=aportar&id=<?= $meta['id_meta'] ?>" method="POST" style="margin-top:20px;">
        <div class="form-group">
          <label>Monto a aportar *</label>
          <input type="number" name="monto" step="0.01" min="0.01" required 
                 placeholder="0.00" autofocus>
        </div>

        <div class="form-group">
          <label>Nota (opcional)</label>
          <input type="text" name="nota" maxlength="255" 
                 placeholder="Ej: Ahorro del mes, Venta de algo...">
        </div>

        <div style="display:flex; gap:15px; margin-top:20px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">Registrar Aporte</button>
          <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>