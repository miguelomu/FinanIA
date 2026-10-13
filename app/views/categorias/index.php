<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>🏷️ Categorías</h1>
      <a href="index.php?accion=crear" class="btn btn-primary">+ Nueva Categoría</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'creado' => 'Categoría creada exitosamente',
          'actualizado' => 'Categoría actualizada exitosamente',
          'eliminado' => 'Categoría eliminada exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
        $errores = [
          'con_transacciones' => 'No se puede eliminar: la categoría tiene transacciones o presupuestos asociados'
        ];
        echo $errores[$_GET['error']] ?? 'Error al procesar';
        ?>
      </div>
    <?php endif; ?>

    <div style="background:rgba(19,47,76,0.5); padding:20px; border-radius:12px; margin-bottom:20px;">
      <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="index.php" class="btn <?= empty($tipo) ? 'btn-primary' : 'btn-secondary' ?>">Todas</a>
        <a href="index.php?tipo=ingreso" class="btn <?= $tipo === 'ingreso' ? 'btn-primary' : 'btn-secondary' ?>">💰 Ingresos</a>
        <a href="index.php?tipo=gasto" class="btn <?= $tipo === 'gasto' ? 'btn-primary' : 'btn-secondary' ?>">💸 Gastos</a>
      </div>
    </div>

    <?php if (empty($categorias)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">🏷️ No tienes categorías creadas</p>
        <a href="index.php?accion=crear" class="btn btn-primary">Crear primera categoría</a>
      </div>
    <?php else: ?>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Color</th>
              <th>Nombre</th>
              <th>Tipo</th>
              <th style="text-align:center;">Transacciones</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categorias as $c): ?>
              <tr>
                <td>
                  <div style="width:30px; height:30px; border-radius:8px; background:<?= htmlspecialchars($c['color']) ?>; border:1px solid rgba(255,255,255,0.2);"></div>
                </td>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td>
                  <span style="padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:bold;
                    background: <?= $c['tipo'] === 'ingreso' ? 'rgba(39,174,96,0.2)' : 'rgba(231,76,60,0.2)' ?>;
                    color: <?= $c['tipo'] === 'ingreso' ? '#7ee8a8' : '#ff8a80' ?>;">
                    <?= ucfirst($c['tipo']) ?>
                  </span>
                </td>
                <td style="text-align:center;"><?= $c['total_transacciones'] ?></td>
                <td style="text-align:center;">
                  <div class="acciones" style="justify-content:center;">
                    <a href="index.php?accion=editar&id=<?= $c['id_categoria'] ?>" class="btn-sm btn-editar">✏️ Editar</a>
                    <a href="index.php?accion=eliminar&id=<?= $c['id_categoria'] ?>" 
                       class="btn-sm btn-eliminar"
                       onclick="return confirm('¿Eliminar esta categoría?')">🗑️ Eliminar</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>