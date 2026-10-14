<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>👤 Nuevo Contacto</h1>
      <a href="index.php?accion=contactos" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = ['campos_vacios' => 'El nombre es obligatorio', 'csrf' => 'Sesión expirada'];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <form action="index.php?accion=guardarContacto" method="POST" style="max-width:600px;">
      <?= csrfField() ?>

      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" required maxlength="100" placeholder="Ej: Juan Pérez">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" maxlength="150" placeholder="juan@email.com">
      </div>

      <div class="form-group">
        <label>Teléfono</label>
        <input type="text" name="telefono" maxlength="20" placeholder="300 123 4567">
      </div>

      <div class="form-group">
        <label>Color</label>
        <input type="color" name="color" value="#3498db" style="width:100px; height:50px; cursor:pointer;">
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Crear Contacto</button>
        <a href="index.php?accion=contactos" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>