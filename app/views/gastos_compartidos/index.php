<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>👥 Gastos Compartidos</h1>
      <div style="display:flex; gap:10px;">
        <a href="index.php?accion=contactos" class="btn btn-secondary">👤 Contactos</a>
        <a href="index.php?accion=crear" class="btn btn-primary">+ Nuevo Gasto</a>
      </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'creado' => 'Gasto compartido registrado exitosamente',
          'eliminado' => 'Gasto eliminado exitosamente',
          'pagado' => 'Pago registrado exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <!-- Resumen de saldos -->
    <div class="cards-grid">
      <div class="card" style="border:2px solid #27ae60;">
        <h3>💚 Me deben</h3>
        <div class="value positivo"><?= formatoCOP($resumen['me_deben']) ?></div>
      </div>
      <div class="card" style="border:2px solid #e74c3c;">
        <h3>💔 Yo debo</h3>
        <div class="value negativo"><?= formatoCOP($resumen['yo_debo']) ?></div>
      </div>
      <div class="card" style="border:2px solid #d4af37;">
        <h3>💰 Balance</h3>
        <div class="value <?= $resumen['neto'] >= 0 ? 'positivo' : 'negativo' ?>">
          <?= formatoCOP($resumen['neto']) ?>
        </div>
      </div>
      <div class="card">
        <h3>📋 Gastos registrados</h3>
        <div class="value"><?= count($gastos) ?></div>
      </div>
    </div>

    <!-- Saldos por contacto -->
    <?php if (!empty($saldos)): ?>
      <h2 style="color: #d4af37; margin:30px 0 20px;">💸 Saldos con contactos</h2>
      <div class="cards-grid">
        <?php foreach ($saldos as $s): 
          if (abs($s['neto']) < 0.01 && $s['me_debe'] == 0 && $s['yo_le_debo'] == 0) continue;
        ?>
          <div class="card" style="border-left:4px solid <?= e($s['contacto']['color']) ?>;">
            <h3 style="font-size:1rem;"><?= e($s['contacto']['nombre']) ?></h3>
            <?php if ($s['neto'] > 0): ?>
              <div class="value positivo" style="font-size:1.3rem;">
                +<?= formatoCOP($s['neto']) ?>
              </div>
              <p style="color: #7ee8a8; font-size:0.8rem; margin-top:5px;">Te debe</p>
            <?php elseif ($s['neto'] < 0): ?>
              <div class="value negativo" style="font-size:1.3rem;">
                <?= formatoCOP($s['neto']) ?>
              </div>
              <p style="color: #ff8a80; font-size:0.8rem; margin-top:5px;">Le debes</p>
            <?php else: ?>
              <div class="value" style="font-size:1.3rem; color: #27ae60;">✓ Saldado</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Lista de gastos -->
    <h2 style="color: #d4af37; margin:35px 0 20px;">📋 Historial de gastos</h2>

    <?php if (empty($gastos)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.2rem; margin-bottom:15px;">👥 Aún no tienes gastos compartidos</p>
        <a href="index.php?accion=contactos" class="btn btn-secondary">1. Agregar contactos</a>
        <a href="index.php?accion=crear" class="btn btn-primary">2. Registrar gasto</a>
      </div>
    <?php else: ?>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Concepto</th>
              <th>Pagó</th>
              <th>Cuenta</th>
              <th style="text-align:right;">Total</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($gastos as $g): ?>
              <tr>
                <td><?= formatoFecha($g['fecha']) ?></td>
                <td><?= e($g['concepto']) ?></td>
                <td>
                  <span style="padding:3px 8px; border-radius:8px; font-size:0.75rem;
                    background:<?= e($g['pagador_color']) ?>22; color:<?= e($g['pagador_color']) ?>;">
                    <?= e($g['pagador_nombre']) ?>
                  </span>
                </td>
                <td><?= e($g['cuenta_nombre'] ?? '—') ?></td>
                <td style="text-align:right; font-weight:bold;">
                  <?= formatoCOP($g['monto_total']) ?>
                </td>
                <td style="text-align:center;">
                  <div class="acciones" style="justify-content:center;">
                    <a href="index.php?accion=ver&id=<?= $g['id_gasto'] ?>" class="btn-sm btn-ver">Ver</a>
                    <a href="index.php?accion=eliminar&id=<?= $g['id_gasto'] ?>" 
                       class="btn-sm btn-eliminar"
                       onclick="return confirm('¿Eliminar este gasto?')">🗑️</a>
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