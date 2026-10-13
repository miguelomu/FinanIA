<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT p.*, d.acreedor AS deuda_acreedor, d.id_deuda 
    FROM pago_deuda p
    INNER JOIN deuda d ON p.id_deuda = d.id_deuda
    WHERE p.id_pago = ? AND p.id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
$pago = $stmt->fetch();

if (!$pago) {
    header('Location: index.php');
    exit;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Pago</h1>
      <a href="ver.php?id=<?= $pago['id_deuda'] ?>" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="meta-card" style="max-width:600px;">
      <h3>Deuda: <?= htmlspecialchars($pago['deuda_acreedor']) ?></h3>

      <form action="actualizar_pago.php" method="POST" style="margin-top:20px;">
        <input type="hidden" name="id" value="<?= $pago['id_pago'] ?>">

        <div class="form-group">
          <label>Monto *</label>
          <input type="number" name="monto" step="0.01" min="0.01" required 
                 value="<?= $pago['monto'] ?>">
        </div>

        <div class="form-group">
          <label>Nota</label>
          <input type="text" name="nota" maxlength="255" 
                 value="<?= htmlspecialchars($pago['nota'] ?? '') ?>">
        </div>

        <div style="display:flex; gap:15px; margin-top:20px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar Pago</button>
          <a href="ver.php?id=<?= $pago['id_deuda'] ?>" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>