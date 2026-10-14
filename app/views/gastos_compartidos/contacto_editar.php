<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Contacto</h1>
      <a href="index.php?accion=contactos" class="btn btn-secondary">← Volver</a>
    </div>

    <form action="index.php?accion=actualizarContacto" method="POST" style="max-width:600px;">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= $contacto['id_contacto'] ?>">

      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" required maxlength="100" value="<?= e($contacto['nombre']) ?>">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" maxlength="150" value="<?= e($contacto['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Teléfono</label>
        <input type="text" name="telefono" maxlength="20" value="<?= e($contacto['telefono'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Color</label>
        <input type="color" name="color" value="<?= e($contacto['color']) ?>" style="width:100px; height:50px; cursor:pointer;">
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" name="activo" value="1" style="width:auto;" <?= $contacto['activo'] ? 'checked' : '' ?>>
          Contacto activo
        </label>
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar</button>
        <a href="index.php?accion=contactos" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>