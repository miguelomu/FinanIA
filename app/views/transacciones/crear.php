<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💸 Nueva Transacción</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'campos_vacios' => 'Todos los campos obligatorios deben llenarse',
          'monto_invalido' => 'El monto debe ser mayor a cero'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <?php if (empty($categorias)): ?>
      <div class="alert alert-error">
        No tienes categorías creadas. Primero crea una categoría en el módulo de Categorías.
      </div>
    <?php else: ?>

    <form action="index.php?accion=guardar" method="POST" style="max-width:700px;">
      <div class="form-group">
        <label>Tipo de Transacción *</label>
        <select name="tipo" required>
          <option value="">Selecciona...</option>
          <option value="ingreso">💰 Ingreso</option>
          <option value="gasto">💸 Gasto</option>
        </select>
      </div>

      <div class="form-group">
        <label>Monto *</label>
        <input type="number" name="monto" step="0.01" min="0.01" required placeholder="0.00">
      </div>

      <div class="form-group">
        <label>Fecha *</label>
        <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
      </div>

      <div class="form-group">
        <label>Categoría *</label>
        <select name="id_categoria" required>
          <option value="">Selecciona...</option>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id_categoria'] ?>">
              <?= ucfirst($cat['tipo']) ?> — <?= htmlspecialchars($cat['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Descripción</label>
        <input type="text" name="descripcion" maxlength="255" placeholder="Opcional">
      </div>

      <div class="form-group">
        <label>Método de Pago</label>
        <select name="metodo_pago">
          <option value="efectivo">Efectivo</option>
          <option value="tarjeta">Tarjeta</option>
          <option value="transferencia">Transferencia</option>
        </select>
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" name="recurrente" value="1" style="width:auto;">
          Marcar como recurrente
        </label>
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar Transacción</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>

    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>