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
          'monto_invalido' => 'El monto debe ser mayor a cero',
          'csrf' => 'Sesión expirada. Recarga la página.'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <?php if (empty($categorias)): ?>
      <div class="alert alert-error">
        No tienes categorías creadas. Primero crea una categoría en el módulo de Categorías.
      </div>
    <?php elseif (empty($cuentas)): ?>
      <div class="alert alert-error">
        No tienes cuentas creadas. Primero crea una cuenta en el módulo de Cuentas.
      </div>
    <?php else: ?>

    <form action="index.php?accion=guardar" method="POST" style="max-width:700px;">
      <?= csrfField() ?>

      <div class="form-group">
        <label>Tipo de Transacción *</label>
        <select name="tipo" id="tipo" required>
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
        <select name="id_categoria" id="categoria" required>
          <option value="">Primero selecciona el tipo</option>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id_categoria'] ?>" data-tipo="<?= $cat['tipo'] ?>" style="display:none;">
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
            <option value="<?= $c['id_cuenta'] ?>">
              <?= e($c['nombre']) ?> — <?= formatoCOP($c['saldo_actual']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small style="color:#9fb3c8; font-size:0.8rem;">
          ¿De dónde sale o entra el dinero?
        </small>
      </div>

      <div class="form-group">
        <label>Descripción</label>
        <input type="text" name="descripcion" maxlength="255" placeholder="Opcional">
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

    <script>
      const selectTipo = document.getElementById('tipo');
      const selectCategoria = document.getElementById('categoria');
      const opcionesCategoria = Array.from(selectCategoria.querySelectorAll('option'));

      function filtrarCategorias() {
        const tipo = selectTipo.value;
        
        // Resetear selección
        selectCategoria.value = '';
        
        // Si no hay tipo, mostrar mensaje
        if (!tipo) {
          opcionesCategoria.forEach(op => {
            if (op.value === '') {
              op.textContent = 'Primero selecciona el tipo';
              op.style.display = '';
            } else {
              op.style.display = 'none';
            }
          });
          return;
        }
        
        // Filtrar por tipo
        opcionesCategoria.forEach(op => {
          if (op.value === '') {
            op.textContent = 'Selecciona una categoría de ' + (tipo === 'ingreso' ? 'ingreso' : 'gasto');
            op.style.display = '';
          } else if (op.dataset.tipo === tipo) {
            op.style.display = '';
          } else {
            op.style.display = 'none';
          }
        });
      }

      selectTipo.addEventListener('change', filtrarCategorias);
      
      // Ejecutar al cargar por si ya hay un tipo seleccionado
      filtrarCategorias();
    </script>

    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>