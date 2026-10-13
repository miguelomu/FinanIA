<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>🎯 Nueva Meta</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'campos_vacios' => 'Todos los campos obligatorios deben llenarse',
          'monto_invalido' => 'El monto objetivo debe ser mayor a cero',
          'fecha_invalida' => 'La fecha límite debe ser posterior a la fecha de inicio'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <form action="index.php?accion=guardar" method="POST" style="max-width:700px;">
        <?= csrfField() ?>
      <div class="form-group">
        <label>Nombre de la meta *</label>
        <input type="text" name="nombre" required maxlength="150" 
               placeholder="Ej: Viaje a Europa, Fondo de emergencia...">
      </div>

      <div class="form-group">
        <label>Monto objetivo *</label>
        <input type="number" name="monto_objetivo" step="0.01" min="0.01" required 
               placeholder="0.00">
      </div>

      <div class="form-group">
        <label>Monto inicial ahorrado</label>
        <input type="number" name="monto_actual" step="0.01" min="0" value="0" 
               placeholder="0.00">
      </div>

      <div class="form-group">
        <label>Fecha de inicio *</label>
        <input type="date" name="fecha_inicio" value="<?= date('Y-m-d') ?>" required>
      </div>

      <div class="form-group">
        <label>Fecha límite *</label>
        <input type="date" name="fecha_limite" required>
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Crear Meta</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>