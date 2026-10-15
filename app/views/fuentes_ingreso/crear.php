<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💼 Nueva Fuente de Ingreso</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'campos_vacios' => 'El nombre es obligatorio',
          'nombre_existe' => 'Ya tienes una fuente con ese nombre',
          'csrf' => 'Sesión expirada'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <form action="index.php?accion=guardar" method="POST" style="max-width:700px;">
      <?= csrfField() ?>

      <div class="form-group">
        <label>Nombre de la fuente *</label>
        <input type="text" name="nombre" required maxlength="100" 
               placeholder="Ej: Freelance diseño, Trabajos puntuales, Mesada...">
      </div>

      <div class="form-group">
        <label>Tipo *</label>
        <select name="tipo" required>
          <option value="fijo">📌 Fijo (llega siempre igual)</option>
          <option value="variable" selected>📊 Variable (cambia cada mes)</option>
          <option value="esporadico">⚡ Esporádico (ocasional)</option>
        </select>
      </div>

      <div class="form-group">
        <label>Frecuencia *</label>
        <select name="frecuencia" required>
          <option value="mensual">Mensual</option>
          <option value="quincenal">Quincenal</option>
          <option value="semanal">Semanal</option>
          <option value="irregular" selected>Irregular</option>
        </select>
      </div>

      <div class="form-group">
        <label>Monto promedio estimado</label>
        <input type="number" name="monto_promedio" step="0.01" min="0" value="0" 
               placeholder="0.00">
        <small style=" 9fb3c8; font-size:0.8rem;">
          Es un valor de referencia. El sistema calculará el promedio real con base en tus transacciones.
        </small>
      </div>

      <div class="form-group">
        <label>Color</label>
        <input type="color" name="color" value="#27ae60" 
               style="width:100px; height:50px; cursor:pointer;">
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Crear Fuente</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>