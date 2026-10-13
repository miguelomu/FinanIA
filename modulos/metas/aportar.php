<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM meta WHERE id_meta = ? AND id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
$meta = $stmt->fetch();

if (!$meta) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto = floatval($_POST['monto'] ?? 0);
    $nota = trim($_POST['nota'] ?? '');
    
    if ($monto > 0) {
        // 1. Insertar el aporte
        $stmt = $pdo->prepare("INSERT INTO aporte_meta (id_meta, id_usuario, monto, nota) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $usuario['id'], $monto, $nota]);
        
        // 2. Recalcular: monto_actual = monto_inicial + SUM(aportes)
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto),0) AS total FROM aporte_meta WHERE id_meta = ?");
        $stmt->execute([$id]);
        $totalAportes = $stmt->fetch()['total'];
        
        $montoFinal = $meta['monto_inicial'] + $totalAportes;
        $nuevoEstado = $montoFinal >= $meta['monto_objetivo'] ? 'completada' : 'activa';
        
        $stmt = $pdo->prepare("UPDATE meta SET monto_actual = ?, estado = ? WHERE id_meta = ?");
        $stmt->execute([$montoFinal, $nuevoEstado, $id]);
        
        header('Location: ver.php?id=' . $id . '&msg=aportado');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💰 Aportar a Meta</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="meta-card" style="max-width:600px;">
      <h3><?= htmlspecialchars($meta['nombre']) ?></h3>
      <div class="meta-info">
        <span>Objetivo: <strong>$<?= number_format($meta['monto_objetivo'], 2) ?></strong></span>
        <span>Monto inicial: <strong>$<?= number_format($meta['monto_inicial'], 2) ?></strong></span>
        <span>Aportes: <strong>$<?= number_format($meta['monto_actual'] - $meta['monto_inicial'], 2) ?></strong></span>
        <span>Total ahorrado: <strong>$<?= number_format($meta['monto_actual'], 2) ?></strong></span>
      </div>

      <form method="POST" style="margin-top:20px;">
        <div class="form-group">
          <label>Monto a aportar *</label>
          <input type="number" name="monto" step="0.01" min="0.01" required 
                 placeholder="0.00" autofocus>
        </div>

        <div class="form-group">
          <label>Nota (opcional)</label>
          <input type="text" name="nota" maxlength="255" 
                 placeholder="Ej: Ahorro del mes, Venta de algo...">
        </div>

        <div style="display:flex; gap:15px; margin-top:20px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">Registrar Aporte</button>
          <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>