<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Aporte</h1>
      <a href="index.php?accion=ver&id=<?= $aporte['id_meta'] ?>" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="meta-card" style="max-width:600px;">
      <h3>Meta: <?= htmlspecialchars($aporte['meta_nombre']) ?></h3>

      <form action="index.php?accion=actualizarAporte" method="POST" style="margin-top:20px;">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $aporte['id_aporte'] ?>">

        <div class="form-group">
          <label>Monto *</label>
          <input type="number" name="monto" step="0.01" min="0.01" required 
                 value="<?= $aporte['monto'] ?>">
        </div>

        <div class="form-group">
          <label>Nota</label>
          <input type="text" name="nota" maxlength="255" 
                 value="<?= htmlspecialchars($aporte['nota'] ?? '') ?>">
        </div>

        <div style="display:flex; gap:15px; margin-top:20px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar Aporte</button>
          <a href="index.php?accion=ver&id=<?= $aporte['id_meta'] ?>" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>