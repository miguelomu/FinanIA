<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Fuente</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <form action="index.php?accion=actualizar" method="POST" style="max-width:700px;">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= $fuente['id_fuente'] ?>">

      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" required maxlength="100" value="<?= e($fuente['nombre']) ?>">
      </div>

      <div class="form-group">
        <label>Tipo</label>
        <select name="tipo">
          <option value="fijo" <?= $fuente['tipo'] === 'fijo' ? 'selected' : '' ?>>📌 Fijo</option>
          <option value="variable" <?= $fuente['tipo'] === 'variable' ? 'selected' : '' ?>>📊 Variable</option>
          <option value="esporadico" <?= $fuente['tipo'] === 'esporadico' ? 'selected' : '' ?>>⚡ Esporádico</option>
        </select>
      </div>

      <div class="form-group">
        <label>Frecuencia</label>
        <select name="frecuencia">
          <option value="mensual" <?= $fuente['frecuencia'] === 'mensual' ? 'selected' : '' ?>>Mensual</option>
          <option value="quincenal" <?= $fuente['frecuencia'] === 'quincenal' ? 'selected' : '' ?>>Quincenal</option>
          <option value="semanal" <?= $fuente['frecuencia'] === 'semanal' ? 'selected' : '' ?>>Semanal</option>
          <option value="irregular" <?= $fuente['frecuencia'] === 'irregular' ? 'selected' : '' ?>>Irregular</option>
        </select>
      </div>

      <div class="form-group">
        <label>Monto promedio</label>
        <input type="number" name="monto_promedio" step="0.01" min="0" value="<?= $fuente['monto_promedio'] ?>">
      </div>

      <div class="form-group">
        <label>Color</label>
        <input type="color" name="color" value="<?= e($fuente['color']) ?>" style="width:100px; height:50px; cursor:pointer;">
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" name="activa" value="1" style="width:auto;" <?= $fuente['activa'] ? 'checked' : '' ?>>
          Fuente activa
        </label>
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>