<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Presupuesto</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'campos_vacios' => 'Todos los campos deben llenarse',
          'ya_existe' => 'Ya existe un presupuesto para ese mes y año'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <?php 
    $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    ?>

    <form action="index.php?accion=actualizar" method="POST" style="max-width:900px;">
        <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= $presupuesto['id_presupuesto'] ?>">

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        <div class="form-group">
          <label>Mes *</label>
          <select name="mes" required>
            <?php for ($i = 1; $i <= 12; $i++): ?>
              <option value="<?= $i ?>" <?= $i == $presupuesto['mes'] ? 'selected' : '' ?>>
                <?= $meses[$i] ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Año *</label>
          <select name="anio" required>
            <?php for ($i = date('Y') - 1; $i <= date('Y') + 2; $i++): ?>
              <option value="<?= $i ?>" <?= $i == $presupuesto['anio'] ? 'selected' : '' ?>>
                <?= $i ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>
      </div>

      <h3 style="color: #d4af37; margin:25px 0 15px;">Límites por Categoría</h3>
      <div style="background:rgba(19,47,76,0.5); padding:20px; border-radius:12px;">
        <?php foreach ($categorias as $cat): 
          $valorActual = $limitesActuales[$cat['id_categoria']] ?? 0;
        ?>
          <div style="display:grid; grid-template-columns:1fr 200px; gap:15px; align-items:center; padding:10px 0; border-bottom:1px solid rgba(212,175,55,0.1);">
            <span style="color: #c9d6e3;"><?= htmlspecialchars($cat['nombre']) ?></span>
            <input type="number" 
                   name="limites[<?= $cat['id_categoria'] ?>]" 
                   step="0.01" min="0" value="<?= $valorActual ?>"
                   style="padding:8px 12px; background:rgba(10,25,41,0.7); border:1px solid rgba(212,175,55,0.3); border-radius:8px; color: #e6edf3; width:100%;">
          </div>
        <?php endforeach; ?>
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar Presupuesto</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>