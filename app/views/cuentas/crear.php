<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>🏦 Nueva Cuenta</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'campos_vacios' => 'El nombre y el tipo son obligatorios',
          'nombre_existe' => 'Ya tienes una cuenta con ese nombre'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <form action="index.php?accion=guardar" method="POST" style="max-width:600px;">
      <?= csrfField() ?>

      <div class="form-group">
        <label>Nombre de la cuenta *</label>
        <input type="text" name="nombre" required maxlength="100" 
               placeholder="Ej: Nequi, Bancolombia, Efectivo...">
      </div>

      <div class="form-group">
        <label>Tipo *</label>
        <select name="tipo" required>
          <option value="">Selecciona...</option>
          <option value="efectivo">💵 Efectivo</option>
          <option value="ahorros">🏦 Cuenta de ahorros</option>
          <option value="corriente">🏛️ Cuenta corriente</option>
          <option value="tarjeta">💳 Tarjeta de crédito</option>
          <option value="inversion">📈 Inversión</option>
          <option value="otro">📦 Otro</option>
        </select>
      </div>

      <div class="form-group">
        <label>Saldo inicial</label>
        <input type="number" name="saldo_inicial" step="0.01" value="0" 
               placeholder="0.00">
        <small style="color:#9fb3c8; font-size:0.8rem;">
          El dinero que tienes actualmente en esta cuenta.
        </small>
      </div>

      <div class="form-group">
        <label>Etiqueta (opcional)</label>
        <input type="text" name="etiqueta" maxlength="150" 
               placeholder="Ej: Para arriendo, Para viaje, Ahorro...">
      </div>

      <div class="form-group">
        <label>Color</label>
        <input type="color" name="color" value="#3498db" 
               style="width:100px; height:50px; cursor:pointer;">
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Crear Cuenta</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>