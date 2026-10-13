<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM deuda WHERE id_deuda = ? AND id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
$deuda = $stmt->fetch();

if (!$deuda) {
    header('Location: index.php');
    exit;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Deuda</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <form action="actualizar.php" method="POST" style="max-width:700px;">
      <input type="hidden" name="id" value="<?= $deuda['id_deuda'] ?>">

      <div class="form-group">
        <label>Acreedor *</label>
        <input type="text" name="acreedor" required maxlength="150" 
               value="<?= htmlspecialchars($deuda['acreedor']) ?>">
      </div>

      <div class="form-group">
        <label>Monto total *</label>
        <input type="number" name="monto_total" step="0.01" min="0.01" required 
               value="<?= $deuda['monto_total'] ?>">
      </div>

      <div class="form-group">
        <label>Monto ya pagado</label>
        <input type="number" name="monto_pagado" step="0.01" min="0" 
               value="<?= $deuda['monto_pagado'] ?>">
      </div>

      <div class="form-group">
        <label>Tasa de interés anual (%)</label>
        <input type="number" name="tasa_interes" step="0.01" min="0" 
               value="<?= $deuda['tasa_interes'] ?>">
      </div>

      <div class="form-group">
        <label>Fecha de inicio *</label>
        <input type="date" name="fecha_inicio" required 
               value="<?= $deuda['fecha_inicio'] ?>">
      </div>

      <div class="form-group">
        <label>Fecha de vencimiento *</label>
        <input type="date" name="fecha_vencimiento" required 
               value="<?= $deuda['fecha_vencimiento'] ?>">
      </div>

      <div class="form-group">
        <label>Pago mínimo mensual</label>
        <input type="number" name="pago_minimo" step="0.01" min="0" 
               value="<?= $deuda['pago_minimo'] ?>">
      </div>

      <div class="form-group">
        <label>Estado</label>
        <select name="estado">
          <option value="activa" <?= $deuda['estado'] === 'activa' ? 'selected' : '' ?>>Activa</option>
          <option value="pagada" <?= $deuda['estado'] === 'pagada' ? 'selected' : '' ?>>Pagada</option>
          <option value="vencida" <?= $deuda['estado'] === 'vencida' ? 'selected' : '' ?>>Vencida</option>
        </select>
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>