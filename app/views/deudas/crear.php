<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💳 Nueva Deuda</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'campos_vacios' => 'Todos los campos obligatorios deben llenarse',
          'monto_invalido' => 'El monto total debe ser mayor a cero',
          'fecha_invalida' => 'La fecha de vencimiento debe ser posterior a la de inicio'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <form action="index.php?accion=guardar" method="POST" style="max-width:700px;">
      <div class="form-group">
        <label>Acreedor *</label>
        <input type="text" name="acreedor" required maxlength="150" 
               placeholder="Ej: Banco XYZ, Tarjeta Visa...">
      </div>

      <div class="form-group">
        <label>Monto total *</label>
        <input type="number" name="monto_total" step="0.01" min="0.01" required 
               placeholder="0.00">
      </div>

      <div class="form-group">
        <label>Monto ya pagado</label>
        <input type="number" name="monto_pagado" step="0.01" min="0" value="0" 
               placeholder="0.00">
      </div>

      <div class="form-group">
        <label>Tasa de interés anual (%)</label>
        <input type="number" name="tasa_interes" step="0.01" min="0" value="0" 
               placeholder="0.00">
      </div>

      <div class="form-group">
        <label>Fecha de inicio *</label>
        <input type="date" name="fecha_inicio" value="<?= date('Y-m-d') ?>" required>
      </div>

      <div class="form-group">
        <label>Fecha de vencimiento *</label>
        <input type="date" name="fecha_vencimiento" required>
      </div>

      <div class="form-group">
        <label>Pago mínimo mensual</label>
        <input type="number" name="pago_minimo" step="0.01" min="0" value="0" 
               placeholder="0.00">
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar Deuda</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>