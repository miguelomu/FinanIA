<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

$stmt = $pdo->prepare("SELECT p.*, 
    (SELECT COUNT(*) FROM detalle_presupuesto WHERE id_presupuesto = p.id_presupuesto) AS total_categorias
    FROM presupuesto p
    WHERE p.id_usuario = ?
    ORDER BY p.anio DESC, p.mes DESC");
$stmt->execute([$usuario['id']]);
$presupuestos = $stmt->fetchAll();

$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>📅 Presupuestos</h1>
      <a href="crear.php" class="btn btn-primary">+ Nuevo Presupuesto</a>
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

    <?php if (empty($presupuestos)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">📅 No tienes presupuestos creados</p>
        <a href="crear.php" class="btn btn-primary">Crear primer presupuesto</a>
      </div>
    <?php else: ?>
      <div class="cards-grid">
        <?php foreach ($presupuestos as $p): ?>
          <div class="card" style="cursor:pointer;" onclick="location.href='ver.php?id=<?= $p['id_presupuesto'] ?>'">
            <h3><?= $meses[$p['mes']] ?> <?= $p['anio'] ?></h3>
            <div class="value">$<?= number_format($p['monto_total'], 2) ?></div>
            <p style="color:#9fb3c8; font-size:0.85rem; margin-top:10px;">
              <?= $p['total_categorias'] ?> categoría(s)
            </p>
            <div class="btn-group" style="margin-top:15px;" onclick="event.stopPropagation();">
              <a href="ver.php?id=<?= $p['id_presupuesto'] ?>" class="btn btn-primary" style="font-size:0.75rem; padding:8px 12px;">Ver</a>
              <a href="editar.php?id=<?= $p['id_presupuesto'] ?>" class="btn btn-secondary" style="font-size:0.75rem; padding:8px 12px;">✏️</a>
              <a href="eliminar.php?id=<?= $p['id_presupuesto'] ?>" 
                 class="btn btn-secondary" 
                 style="font-size:0.75rem; padding:8px 12px; color:#e74c3c;"
                 onclick="return confirm('¿Eliminar este presupuesto?')">🗑️</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>