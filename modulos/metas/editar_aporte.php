<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT a.*, m.nombre AS meta_nombre, m.id_meta, m.monto_objetivo 
    FROM aporte_meta a
    INNER JOIN meta m ON a.id_meta = m.id_meta
    WHERE a.id_aporte = ? AND a.id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
$aporte = $stmt->fetch();

if (!$aporte) {
    header('Location: index.php');
    exit;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Aporte</h1>
      <a href="ver.php?id=<?= $aporte['id_meta'] ?>" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="meta-card" style="max-width:600px;">
      <h3>Meta: <?= htmlspecialchars($aporte['meta_nombre']) ?></h3>

      <form action="actualizar_aporte.php" method="POST" style="margin-top:20px;">
        <input type="hidden" name="id" value="<?= $aporte['id_aporte'] ?>">

        <div class="form-group">
          <label>Monto *</label>
          <input type="number" name="monto" step="0.01" min="0.01" required 
                 value="<?= $aporte['monto'] ?>">
        </div>

        <div class="form-group">
          <label>Nota</label>
          <input type="text" name="nota" maxlength="255" 
                 value="<?= htmlspecialchars($aporte['nota'] ?? '') ?>">
        </div>

        <div style="display:flex; gap:15px; margin-top:20px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar Aporte</button>
          <a href="ver.php?id=<?= $aporte['id_meta'] ?>" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>