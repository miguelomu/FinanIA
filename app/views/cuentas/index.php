<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>🏦 Mis Cuentas</h1>
      <a href="index.php?accion=crear" class="btn btn-primary">+ Nueva Cuenta</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'creado' => 'Cuenta creada exitosamente',
          'actualizado' => 'Cuenta actualizada exitosamente',
          'eliminado' => 'Cuenta eliminada exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'con_movimientos'): ?>
      <div class="alert alert-error">
        ⚠️ No puedes eliminar esta cuenta porque tiene transacciones asociadas.
      </div>
    <?php endif; ?>

    <!-- Panel de Patrimonio -->
    <div class="cards-grid">
      <div class="card" style="border-color: #d4af37;">
        <h3>💰 Patrimonio Neto</h3>
        <div class="value <?= $patrimonio['neto'] >= 0 ? 'positivo' : 'negativo' ?>" style="font-size:2.2rem;">
          <?= formatoCOP($patrimonio['neto']) ?>
        </div>
        <p style="color: #9fb3c8; font-size:0.8rem; margin-top:10px;">
          Activos − Pasivos
        </p>
      </div>
      <div class="card">
        <h3>📈 Activos Totales</h3>
        <div class="value positivo"><?= formatoCOP($patrimonio['activos']) ?></div>
      </div>
      <div class="card">
        <h3>📉 Pasivos Totales</h3>
        <div class="value negativo"><?= formatoCOP($patrimonio['pasivos']) ?></div>
      </div>
      <div class="card">
        <h3>🏦 Cuentas Activas</h3>
        <div class="value"><?= count(array_filter($cuentas, fn($c) => $c['activa'])) ?></div>
      </div>
    </div>

    <!-- Lista de cuentas -->
    <h2 style="color: #d4af37; margin:30px 0 20px;">Mis Cuentas</h2>

    <?php if (empty($cuentas)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">🏦 No tienes cuentas creadas</p>
        <a href="index.php?accion=crear" class="btn btn-primary">Crear primera cuenta</a>
      </div>
    <?php else: ?>
      <div class="cards-grid">
        <?php foreach ($cuentas as $c): ?>
          <div class="card" style="border-left:4px solid <?= e($c['color']) ?>;">
            <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:15px;">
              <h3 style="margin:0; font-size:1.1rem;"><?= e($c['nombre']) ?></h3>
              <?php if (!$c['activa']): ?>
                <span style="padding:2px 8px; border-radius:8px; font-size:0.7rem;
                  background:rgba(231,76,60,0.2); color: #ff8a80;">Inactiva</span>
              <?php endif; ?>
            </div>

            <div class="value <?= $c['saldo_actual'] >= 0 ? 'positivo' : 'negativo' ?>" style="font-size:1.6rem;">
              <?= formatoCOP($c['saldo_actual']) ?>
            </div>

            <p style="color: #9fb3c8; font-size:0.8rem; margin-top:10px;">
              <?= e(capitalizar($c['tipo'])) ?>
              <?php if ($c['etiqueta']): ?>
                · <?= e($c['etiqueta']) ?>
              <?php endif; ?>
            </p>

            <div class="btn-group" style="margin-top:15px;">
              <a href="index.php?accion=editar&id=<?= $c['id_cuenta'] ?>" class="btn btn-secondary" style="font-size:0.75rem; padding:8px 12px;">✏️ Editar</a>
              <a href="index.php?accion=eliminar&id=<?= $c['id_cuenta'] ?>" 
                 class="btn btn-secondary" 
                 style="font-size:0.75rem; padding:8px 12px; color: #e74c3c;"
                 onclick="return confirm('¿Eliminar esta cuenta?')">🗑️ Eliminar</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>