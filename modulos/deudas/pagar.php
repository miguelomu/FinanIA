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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto = floatval($_POST['monto'] ?? 0);
    $nota = trim($_POST['nota'] ?? '');
    
    if ($monto > 0) {
        $stmt = $pdo->prepare("INSERT INTO pago_deuda (id_deuda, id_usuario, monto, nota) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $usuario['id'], $monto, $nota]);
        
        // Recalcular monto_pagado = monto_inicial_pagado + SUM(pagos)
        // Nota: monto_pagado inicial ya está en deuda.monto_pagado, pero lo simplificamos
        // sumando todos los pagos nuevos
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto),0) AS total FROM pago_deuda WHERE id_deuda = ?");
        $stmt->execute([$id]);
        $totalPagos = $stmt->fetch()['total'];
        
        $montoPagadoFinal = $deuda['monto_inicial_pagado'] ?? 0;
        $montoPagadoFinal += $totalPagos;
        
        // Si no hay monto_inicial_pagado, usamos el monto_pagado original
        if ($montoPagadoFinal == 0 && $deuda['monto_pagado'] > 0) {
            $montoPagadoFinal = $deuda['monto_pagado'] - ($deuda['monto_pagado'] - $totalPagos);
        }
        
        // Simplificación: monto_pagado = monto_pagado original + pagos nuevos
        $nuevoPagado = $deuda['monto_pagado'] + $monto;
        $nuevoEstado = $nuevoPagado >= $deuda['monto_total'] ? 'pagada' : 'activa';
        
        $stmt = $pdo->prepare("UPDATE deuda SET monto_pagado = ?, estado = ? WHERE id_deuda = ?");
        $stmt->execute([$nuevoPagado, $nuevoEstado, $id]);
        
        header('Location: ver.php?id=' . $id . '&msg=pagado');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💵 Registrar Pago</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="meta-card" style="max-width:600px;">
      <h3><?= htmlspecialchars($deuda['acreedor']) ?></h3>
      <div class="meta-info">
        <span>Deuda total: <strong>$<?= number_format($deuda['monto_total'], 2) ?></strong></span>
        <span>Pagado: <strong>$<?= number_format($deuda['monto_pagado'], 2) ?></strong></span>
        <span>Pendiente: <strong>$<?= number_format($deuda['monto_total'] - $deuda['monto_pagado'], 2) ?></strong></span>
      </div>

      <form method="POST" style="margin-top:20px;">
        <div class="form-group">
          <label>Monto a pagar *</label>
          <input type="number" name="monto" step="0.01" min="0.01" required 
                 placeholder="0.00" autofocus>
        </div>

        <div class="form-group">
          <label>Nota (opcional)</label>
          <input type="text" name="nota" maxlength="255" 
                 placeholder="Ej: Pago mensual, Abono extra...">
        </div>

        <div style="display:flex; gap:15px; margin-top:20px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">Registrar Pago</button>
          <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>