<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Pago</h1>
      <a href="index.php?accion=ver&id=<?= $pago['id_deuda'] ?>" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="meta-card" style="max-width:600px;">
      <h3>Deuda: <?= htmlspecialchars($pago['deuda_acreedor']) ?></h3>

      <form action="index.php?accion=actualizarPago" method="POST" style="margin-top:20px;">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $pago['id_pago'] ?>">

        <div class="form-group">
          <label>Monto *</label>
          <input type="number" name="monto" step="0.01" min="0.01" required 
                 value="<?= $pago['monto'] ?>">
        </div>

        <div class="form-group">
          <label>Nota</label>
          <input type="text" name="nota" maxlength="255" 
                 value="<?= htmlspecialchars($pago['nota'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Cuenta de origen</label>
          <select name="id_cuenta">
            <option value="">Sin cuenta</option>
            <?php foreach ($cuentas as $c): ?>
              <option value="<?= $c['id_cuenta'] ?>" 
                <?= $c['id_cuenta'] == $pago['id_cuenta'] ? 'selected' : '' ?>>
                <?= e($c['nombre']) ?> — <?= formatoCOP($c['saldo_actual']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="display:flex; gap:15px; margin-top:20px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar Pago</button>
          <a href="index.php?accion=ver&id=<?= $pago['id_deuda'] ?>" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>