<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>📅 Presupuestos</h1>
      <a href="index.php?accion=crear" class="btn btn-primary">+ Nuevo Presupuesto</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'creado' => 'Presupuesto creado exitosamente',
          'actualizado' => 'Presupuesto actualizado exitosamente',
          'eliminado' => 'Presupuesto eliminado exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <?php 
    $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    ?>

    <?php if (empty($presupuestos)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">📅 No tienes presupuestos creados</p>
        <a href="index.php?accion=crear" class="btn btn-primary">Crear primer presupuesto</a>
      </div>
    <?php else: ?>
      <div class="cards-grid">
        <?php foreach ($presupuestos as $p): ?>
          <div class="card">
            <h3><?= $meses[$p['mes']] ?> <?= $p['anio'] ?></h3>
            <div class="value">$<?= number_format($p['monto_total'], 2) ?></div>
            <p style="color: #9fb3c8; font-size:0.85rem; margin-top:10px;">
              <?= $p['total_categorias'] ?> categoría(s)
            </p>
            <div class="btn-group" style="margin-top:15px;">
              <a href="index.php?accion=ver&id=<?= $p['id_presupuesto'] ?>" class="btn btn-primary" style="font-size:0.75rem; padding:8px 12px;">Ver</a>
              <a href="index.php?accion=editar&id=<?= $p['id_presupuesto'] ?>" class="btn btn-secondary" style="font-size:0.75rem; padding:8px 12px;">✏️</a>
              <a href="index.php?accion=eliminar&id=<?= $p['id_presupuesto'] ?>" 
                class="btn btn-secondary" 
                style="font-size:0.75rem; padding:8px 12px; color:#e74c3c;"
                onclick="return confirmar(event, {
                  titulo: '¿Eliminar presupuesto?',
                  mensaje: 'Se eliminarán todos los límites configurados.',
                  tipo: 'danger',
                  icono: '📅',
                  confirmar: 'Eliminar presupuesto'
                })">🗑️</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>