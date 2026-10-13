<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💸 Transacciones</h1>
      <a href="index.php?accion=crear" class="btn btn-primary">+ Nueva Transacción</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">
        <?php
        $mensajes = [
          'creado' => 'Transacción creada exitosamente',
          'actualizado' => 'Transacción actualizada exitosamente',
          'eliminado' => 'Transacción eliminada exitosamente'
        ];
        echo $mensajes[$_GET['msg']] ?? 'Operación exitosa';
        ?>
      </div>
    <?php endif; ?>

    <div class="cards-grid">
      <div class="card">
        <h3>Ingresos</h3>
        <div class="value positivo"><?= formatoCOP($totales['ingresos'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Gastos</h3>
        <div class="value negativo"><?= formatoCOP($totales['gastos'], 2) ?></div>
      </div>
      <div class="card">
        <h3>Balance</h3>
        <div class="value <?= $totales['balance'] >= 0 ? 'positivo' : 'negativo' ?>">
          <?= formatoCOP($totales['balance'], 2) ?>
        </div>
      </div>
      <div class="card">
        <h3>Total Registros</h3>
        <div class="value"><?= count($transacciones) ?></div>
      </div>
    </div>

    <div style="background:rgba(19,47,76,0.5); padding:20px; border-radius:12px; margin-bottom:20px;">
      <form method="GET" style="display:flex; gap:15px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="margin:0; flex:1; min-width:200px;">
          <label>Buscar</label>
          <input type="text" name="buscar" value="<?= htmlspecialchars($buscar) ?>" placeholder="Descripción o categoría...">
        </div>
        <div class="form-group" style="margin:0; min-width:150px;">
          <label>Tipo</label>
          <select name="tipo">
            <option value="">Todos</option>
            <option value="ingreso" <?= $tipo === 'ingreso' ? 'selected' : '' ?>>Ingresos</option>
            <option value="gasto" <?= $tipo === 'gasto' ? 'selected' : '' ?>>Gastos</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="index.php" class="btn btn-secondary">Limpiar</a>
      </form>
    </div>

    <div class="table-container">
      <?php if (empty($transacciones)): ?>
        <div style="padding:40px; text-align:center; color:#9fb3c8;">
          <p style="font-size:1.2rem; margin-bottom:10px;">📭 No hay transacciones registradas</p>
          <a href="index.php?accion=crear" class="btn btn-primary">Registrar primera transacción</a>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Tipo</th>
              <th>Categoría</th>
              <th>Descripción</th>
              <th>Método</th>
              <th style="text-align:right;">Monto</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($transacciones as $t): ?>
              <tr>
                <td><?= date('d/m/Y', strtotime($t['fecha'])) ?></td>
                <td>
                  <span style="padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:bold;
                    background: <?= $t['tipo'] === 'ingreso' ? 'rgba(39,174,96,0.2)' : 'rgba(231,76,60,0.2)' ?>;
                    color: <?= $t['tipo'] === 'ingreso' ? '#7ee8a8' : '#ff8a80' ?>;">
                    <?= ucfirst($t['tipo']) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($t['categoria_nombre']) ?></td>
                <td><?= htmlspecialchars($t['descripcion'] ?? '—') ?></td>
                <td><?= ucfirst($t['metodo_pago']) ?></td>
                <td style="text-align:right; font-weight:bold;
                  color: <?= $t['tipo'] === 'ingreso' ? '#27ae60' : '#e74c3c' ?>;">
                  <?= $t['tipo'] === 'ingreso' ? '+' : '-' ?><?= formatoCOP($t['monto'], 2) ?>
                </td>
                <td style="text-align:center;">
                  <div class="acciones" style="justify-content:center;">
                    <a href="index.php?accion=editar&id=<?= $t['id_transaccion'] ?>" class="btn-sm btn-editar">✏️ Editar</a>
                    <a href="index.php?accion=eliminar&id=<?= $t['id_transaccion'] ?>" 
                       class="btn-sm btn-eliminar"
                       onclick="return confirm('¿Seguro que quieres eliminar esta transacción?')">🗑️ Eliminar</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>