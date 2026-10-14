<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Transacción</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'campos_vacios' => 'Todos los campos obligatorios deben llenarse',
          'monto_invalido' => 'El monto debe ser mayor a cero',
          'csrf' => 'Sesión expirada. Recarga la página.'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <form action="index.php?accion=actualizar" method="POST" style="max-width:700px;">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= $transaccion['id_transaccion'] ?>">

      <div class="form-group">
        <label>Tipo *</label>
        <select name="tipo" id="tipo" required>
          <option value="ingreso" <?= $transaccion['tipo'] === 'ingreso' ? 'selected' : '' ?>>💰 Ingreso</option>
          <option value="gasto" <?= $transaccion['tipo'] === 'gasto' ? 'selected' : '' ?>>💸 Gasto</option>
        </select>
      </div>

      <div class="form-group">
        <label>Monto *</label>
        <input type="number" name="monto" step="0.01" min="0.01" required 
               value="<?= $transaccion['monto'] ?>">
      </div>

      <div class="form-group">
        <label>Fecha *</label>
        <input type="date" name="fecha" required 
               value="<?= $transaccion['fecha'] ?>">
      </div>

      <div class="form-group">
        <label>Categoría *</label>
        <select name="id_categoria" id="categoria" required>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id_categoria'] ?>" 
                    data-tipo="<?= $cat['tipo'] ?>"
                    <?= $cat['id_categoria'] == $transaccion['id_categoria'] ? 'selected' : '' ?>>
              <?= e($cat['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Cuenta *</label>
        <select name="id_cuenta" required>
          <option value="">Selecciona la cuenta...</option>
          <?php foreach ($cuentas as $c): ?>
            <option value="<?= $c['id_cuenta'] ?>" 
              <?= $c['id_cuenta'] == $transaccion['id_cuenta'] ? 'selected' : '' ?>>
              <?= e($c['nombre']) ?> — <?= formatoCOP($c['saldo_actual']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Descripción</label>
        <input type="text" name="descripcion" maxlength="255" 
               value="<?= e($transaccion['descripcion'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" name="recurrente" value="1" style="width:auto;"
            <?= $transaccion['recurrente'] ? 'checked' : '' ?>>
          Marcar como recurrente
        </label>
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>

    <script>
      const selectTipo = document.getElementById('tipo');
      const selectCategoria = document.getElementById('categoria');
      const opcionesCategoria = Array.from(selectCategoria.querySelectorAll('option'));

      function filtrarCategorias() {
        const tipo = selectTipo.value;
        const valorActual = selectCategoria.value;
        
        let valorSigueVisible = false;
        
        opcionesCategoria.forEach(op => {
          if (op.value === '') {
            op.style.display = 'none';
          } else if (op.dataset.tipo === tipo) {
            op.style.display = '';
            if (op.value === valorActual) valorSigueVisible = true;
          } else {
            op.style.display = 'none';
          }
        });
        
        if (!valorSigueVisible) {
          selectCategoria.value = '';
        }
      }

      selectTipo.addEventListener('change', filtrarCategorias);
      filtrarCategorias();
    </script>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>