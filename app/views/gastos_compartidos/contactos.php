<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>👤 Mis Contactos</h1>
      <div style="display:flex; gap:10px;">
        <a href="index.php" class="btn btn-secondary">← Gastos</a>
        <a href="index.php?accion=crearContacto" class="btn btn-primary">+ Nuevo Contacto</a>
      </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'contacto_creado' => 'Contacto creado exitosamente',
          'contacto_actualizado' => 'Contacto actualizado exitosamente',
          'contacto_eliminado' => 'Contacto eliminado exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <?php if (empty($contactos)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">👤 No tienes contactos registrados</p>
        <a href="index.php?accion=crearContacto" class="btn btn-primary">Crear primer contacto</a>
      </div>
    <?php else: ?>
      <div class="cards-grid">
        <?php foreach ($contactos as $c): ?>
          <div class="card" style="border-left:4px solid <?= e($c['color']) ?>;">
            <div style="display:flex; justify-content:space-between; align-items:start;">
              <h3 style="margin:0; font-size:1.1rem;"><?= e($c['nombre']) ?></h3>
              <?php if (!$c['activo']): ?>
                <span style="padding:2px 8px; border-radius:8px; font-size:0.7rem;
                  background:rgba(231,76,60,0.2); color: #ff8a80;">Inactivo</span>
              <?php endif; ?>
            </div>
            
            <?php if ($c['email']): ?>
              <p style="color: #9fb3c8; font-size:0.8rem; margin-top:8px;">📧 <?= e($c['email']) ?></p>
            <?php endif; ?>
            <?php if ($c['telefono']): ?>
              <p style="color: #9fb3c8; font-size:0.8rem;">📱 <?= e($c['telefono']) ?></p>
            <?php endif; ?>

            <div class="btn-group" style="margin-top:15px;">
              <a href="index.php?accion=editarContacto&id=<?= $c['id_contacto'] ?>" class="btn btn-secondary" style="font-size:0.75rem; padding:8px 12px;">✏️ Editar</a>
              <a href="index.php?accion=eliminarContacto&id=<?= $c['id_contacto'] ?>" 
                 class="btn btn-secondary" 
                 style="font-size:0.75rem; padding:8px 12px; color: #e74c3c;"
                 onclick="return confirm('¿Eliminar este contacto?')">🗑️</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>