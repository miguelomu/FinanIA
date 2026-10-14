<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Cuenta</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'campos_vacios' => 'El nombre y el tipo son obligatorios',
          'nombre_existe' => 'Ya tienes otra cuenta con ese nombre'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <form action="index.php?accion=actualizar" method="POST" style="max-width:600px;">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= $cuenta['id_cuenta'] ?>">

      <div class="form-group">
        <label>Nombre de la cuenta *</label>
        <input type="text" name="nombre" required maxlength="100" 
               value="<?= e($cuenta['nombre']) ?>">
      </div>

      <div class="form-group">
        <label>Tipo *</label>
        <select name="tipo" required>
          <option value="efectivo" <?= $cuenta['tipo'] === 'efectivo' ? 'selected' : '' ?>>💵 Efectivo</option>
          <option value="ahorros" <?= $cuenta['tipo'] === 'ahorros' ? 'selected' : '' ?>>🏦 Cuenta de ahorros</option>
          <option value="corriente" <?= $cuenta['tipo'] === 'corriente' ? 'selected' : '' ?>>🏛️ Cuenta corriente</option>
          <option value="tarjeta" <?= $cuenta['tipo'] === 'tarjeta' ? 'selected' : '' ?>>💳 Tarjeta de crédito</option>
          <option value="inversion" <?= $cuenta['tipo'] === 'inversion' ? 'selected' : '' ?>>📈 Inversión</option>
          <option value="otro" <?= $cuenta['tipo'] === 'otro' ? 'selected' : '' ?>>📦 Otro</option>
        </select>
      </div>

      <div class="form-group">
        <label>Saldo inicial</label>
        <input type="number" name="saldo_inicial" step="0.01" 
               value="<?= $cuenta['saldo_inicial'] ?>">
      </div>

      <div class="form-group">
        <label>Etiqueta</label>
        <input type="text" name="etiqueta" maxlength="150" 
               value="<?= e($cuenta['etiqueta'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Color</label>
        <input type="color" name="color" value="<?= e($cuenta['color']) ?>" 
               style="width:100px; height:50px; cursor:pointer;">
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" name="activa" value="1" style="width:auto;"
            <?= $cuenta['activa'] ? 'checked' : '' ?>>
          Cuenta activa
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