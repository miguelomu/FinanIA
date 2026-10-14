<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>👥 Nuevo Gasto Compartido</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'campos_vacios' => 'Todos los campos obligatorios deben llenarse',
          'csrf' => 'Sesión expirada. Recarga la página.',
          'sin_contactos' => 'Primero agrega al menos un contacto'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <form action="index.php?accion=guardar" method="POST" style="max-width:800px;">
      <?= csrfField() ?>

      <div class="form-group">
        <label>Concepto *</label>
        <input type="text" name="concepto" required maxlength="200" 
               placeholder="Ej: Pizza, Uber, Mercado del apartamento...">
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        <div class="form-group">
          <label>Monto total *</label>
          <input type="number" name="monto_total" id="monto_total" step="0.01" min="0.01" required 
                 placeholder="0.00">
        </div>

        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label>¿Quién pagó? *</label>
        <select name="id_pagador" required>
          <option value="">Selecciona...</option>
          <?php foreach ($contactos as $c): ?>
            <option value="<?= $c['id_contacto'] ?>"><?= e($c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
        <small style="color: #9fb3c8; font-size:0.8rem;">
          Incluyéndote a ti, si fuiste quien pagó (regístrate como contacto).
        </small>
      </div>

      <div class="form-group">
        <label>Cuenta (opcional)</label>
        <select name="id_cuenta">
          <option value="">Sin cuenta</option>
          <?php foreach ($cuentas as $c): ?>
            <option value="<?= $c['id_cuenta'] ?>"><?= e($c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <h3 style="color: #d4af37; margin:25px 0 15px;">Participantes</h3>
      <div style="background:rgba(19,47,76,0.5); padding:20px; border-radius:12px;">
        <p style="color: #9fb3c8; font-size:0.85rem; margin-bottom:15px;">
          Marca quiénes participaron en el gasto. El monto se dividirá en partes iguales.
        </p>
        <?php foreach ($contactos as $c): ?>
          <div style="padding:10px 0; border-bottom:1px solid rgba(212,175,55,0.1);">
            <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
              <input type="checkbox" name="participantes[]" value="<?= $c['id_contacto'] ?>" 
                     class="participante-check" style="width:auto;">
              <span style="color: #c9d6e3;"><?= e($c['nombre']) ?></span>
            </label>
          </div>
        <?php endforeach; ?>
      </div>

      <div id="resumenDivision" style="background:rgba(39,174,96,0.15); border-left:4px solid #27ae60; padding:15px; border-radius:8px; margin-top:20px; display:none;">
        <strong style="color: #7ee8a8;">📊 División:</strong>
        <span id="textoDivision" style="color: #c9d6e3;"></span>
      </div>

      <div class="form-group" style="margin-top:20px;">
        <label>Notas (opcional)</label>
        <textarea name="notas" rows="3" placeholder="Ej: Pedido de pizza el viernes en la noche"></textarea>
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar Gasto</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>

    <script>
      const montoInput = document.getElementById('monto_total');
      const checks = document.querySelectorAll('.participante-check');
      const resumen = document.getElementById('resumenDivision');
      const texto = document.getElementById('textoDivision');

      function actualizarResumen() {
        const monto = parseFloat(montoInput.value) || 0;
        const seleccionados = Array.from(checks).filter(c => c.checked).length;
        
        if (monto > 0 && seleccionados > 0) {
          const porPersona = (monto / seleccionados).toLocaleString('es-CO');
          texto.textContent = ` ${seleccionados} personas × $${porPersona} cada una`;
          resumen.style.display = 'block';
        } else {
          resumen.style.display = 'none';
        }
      }

      montoInput.addEventListener('input', actualizarResumen);
      checks.forEach(c => c.addEventListener('change', actualizarResumen));
    </script>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>