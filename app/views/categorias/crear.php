<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>🏷️ Nueva Categoría</h1>
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

    <form action="index.php?accion=guardar" method="POST"  style="max-width:600px;">
        <?= csrfField() ?>
      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" required maxlength="100" 
               placeholder="Ej: Comida, Salario, Transporte...">
      </div>

      <div class="form-group">
        <label>Tipo *</label>
        <select name="tipo" required>
          <option value="">Selecciona...</option>
          <option value="ingreso">💰 Ingreso</option>
          <option value="gasto">💸 Gasto</option>
        </select>
      </div>

      <div class="form-group">
        <label>Color</label>
        <input type="color" name="color" value="#3498db" 
               style="width:100px; height:50px; cursor:pointer;">
      </div>

      <div class="form-group">
        <label>Icono (opcional)</label>
        <input type="text" name="icono" maxlength="50" 
               placeholder="Ej: 🍔 💼 🚗">
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Crear Categoría</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>