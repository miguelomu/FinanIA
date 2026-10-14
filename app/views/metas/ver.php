<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💰 <?= htmlspecialchars($meta['nombre']) ?></h1>
      <div style="display:flex; gap:10px;">
        <a href="index.php?accion=aportar&id=<?= $meta['id_meta'] ?>" class="btn btn-primary">+ Nuevo Aporte</a>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
      </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'aportado' => 'Aporte registrado exitosamente',
          'editado' => 'Aporte actualizado exitosamente',
          'eliminado' => 'Aporte eliminado exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <div class="cards-grid">
      <div class="card">
        <h3>Objetivo</h3>
        <div class="value">$<?= number_format($meta['monto_objetivo'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Monto Inicial</h3>
        <div class="value">$<?= number_format($meta['monto_inicial'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Aportes</h3>
        <div class="value positivo">$<?= number_format($meta['monto_actual'] - $meta['monto_inicial'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Total Ahorrado</h3>
        <div class="value positivo">$<?= number_format($meta['monto_actual'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Falta</h3>
        <div class="value negativo">$<?= number_format($meta['monto_objetivo'] - $meta['monto_actual'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Progreso</h3>
        <div class="value"><?= number_format($progreso, 1) ?>%</div>
      </div>
    </div>

    <div class="progress" style="margin-bottom:30px;">
      <div class="progress-bar <?= $progreso >= 100 ? 'success' : '' ?>" style="width: <?= $progreso ?>%;"></div>
    </div>

    <h2 style="color: #d4af37; margin-bottom:20px;">Historial de Aportes</h2>

    <?php if (empty($aportes)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.1rem; margin-bottom:15px;">📭 Aún no has hecho aportes a esta meta</p>
        <a href="index.php?accion=aportar&id=<?= $meta['id_meta'] ?>" class="btn btn-primary">Registrar primer aporte</a>
      </div>
    <?php else: ?>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Cuenta</th>
              <th>Nota</th>
              <th style="text-align:right;">Monto</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($aportes as $a): ?>
              <tr>
                <td><?= formatoFechaHora($a['fecha']) ?></td>
                <td>
                  <?php if ($a['cuenta_nombre']): ?>
                    <span style="padding:3px 8px; border-radius:8px; font-size:0.75rem;
                      background:<?= e($a['cuenta_color']) ?>22; color:<?= e($a['cuenta_color']) ?>;">
                      <?= e($a['cuenta_nombre']) ?>
                    </span>
                  <?php else: ?>
                    <span style="color: #7a8a9c;">—</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($a['nota'] ?: '—') ?></td>
                <td style="text-align:right; color: #27ae60; font-weight:bold;">
                  +$<?= number_format($a['monto'], 2) ?>
                </td>
                <td style="text-align:center;">
                  <div class="acciones" style="justify-content:center;">
                    <a href="index.php?accion=editarAporte&id=<?= $a['id_aporte'] ?>" class="btn-sm btn-editar">✏️ Editar</a>
                    <a href="index.php?accion=eliminarAporte&id=<?= $a['id_aporte'] ?>" 
                       class="btn-sm btn-eliminar"
                       onclick="return confirm('¿Eliminar este aporte? El monto se restará de la meta.')">🗑️ Eliminar</a>
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