<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Meta</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <form action="index.php?accion=actualizar" method="POST" style="max-width:700px;">
        <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= $meta['id_meta'] ?>">

      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" required maxlength="150" 
               value="<?= htmlspecialchars($meta['nombre']) ?>">
      </div>

      <div class="form-group">
        <label>Monto objetivo *</label>
        <input type="number" name="monto_objetivo" step="0.01" min="0.01" required 
               value="<?= $meta['monto_objetivo'] ?>">
      </div>

      <div class="form-group">
        <label>Monto inicial (no incluye aportes)</label>
        <input type="number" name="monto_actual" step="0.01" min="0" 
               value="<?= $meta['monto_inicial'] ?>">
        <small style="color: #9fb3c8; font-size:0.8rem;">
          Los aportes registrados se suman automáticamente al total.
        </small>
      </div>

      <div class="form-group">
        <label>Fecha de inicio *</label>
        <input type="date" name="fecha_inicio" required 
               value="<?= $meta['fecha_inicio'] ?>">
      </div>

      <div class="form-group">
        <label>Fecha límite *</label>
        <input type="date" name="fecha_limite" required 
               value="<?= $meta['fecha_limite'] ?>">
      </div>

      <div class="form-group">
        <label>Estado</label>
        <select name="estado">
          <option value="activa" <?= $meta['estado'] === 'activa' ? 'selected' : '' ?>>Activa</option>
          <option value="completada" <?= $meta['estado'] === 'completada' ? 'selected' : '' ?>>Completada</option>
          <option value="cancelada" <?= $meta['estado'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
        </select>
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>