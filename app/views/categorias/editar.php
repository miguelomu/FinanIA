<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Categoría</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'campos_vacios' => 'El nombre y el tipo son obligatorios',
          'nombre_existe' => 'Ya tienes una categoría con ese nombre'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <form action="index.php?accion=actualizar" method="POST" style="max-width:600px;">
      <input type="hidden" name="id" value="<?= $categoria['id_categoria'] ?>">

      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" required maxlength="100" 
               value="<?= htmlspecialchars($categoria['nombre']) ?>">
      </div>

      <div class="form-group">
        <label>Tipo *</label>
        <select name="tipo" required>
          <option value="ingreso" <?= $categoria['tipo'] === 'ingreso' ? 'selected' : '' ?>>💰 Ingreso</option>
          <option value="gasto" <?= $categoria['tipo'] === 'gasto' ? 'selected' : '' ?>>💸 Gasto</option>
        </select>
      </div>

      <div class="form-group">
        <label>Color</label>
        <input type="color" name="color" value="<?= htmlspecialchars($categoria['color']) ?>" 
               style="width:100px; height:50px; cursor:pointer;">
      </div>

      <div class="form-group">
        <label>Icono</label>
        <input type="text" name="icono" maxlength="50" 
               value="<?= htmlspecialchars($categoria['icono'] ?? '') ?>">
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>