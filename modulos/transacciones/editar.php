<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();
$id = intval($_GET['id'] ?? 0);

// Cargar transacción
$stmt = $pdo->prepare("SELECT * FROM transaccion WHERE id_transaccion = ? AND id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
$transaccion = $stmt->fetch();

if (!$transaccion) {
    header('Location: index.php');
    exit;
}

// Cargar categorías
$stmt = $pdo->prepare("SELECT * FROM categoria WHERE id_usuario = ? ORDER BY tipo, nombre");
$stmt->execute([$usuario['id']]);
$categorias = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>✏️ Editar Transacción</h1>
      <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <form action="actualizar.php" method="POST" style="max-width:700px;">
      <input type="hidden" name="id" value="<?= $transaccion['id_transaccion'] ?>">

      <div class="form-group">
        <label>Tipo *</label>
        <select name="tipo" required>
          <option value="ingreso" <?= $transaccion['tipo'] === 'ingreso' ? 'selected' : '' ?>>💰 Ingreso</option>
          <option value="gasto" <?= $transaccion['tipo'] === 'gasto' ? 'selected' : '' ?>>💸 Gasto</option>
        </select>
      </div>

      <div class="form-group">
        <label>Monto *</label>
        <input type="number" name="monto" step="0.01" min="0.01" required 
               value="<?= $transaccion['monto'] ?>">
      </div>

      <div class="form-group">
        <label>Fecha *</label>
        <input type="date" name="fecha" required 
               value="<?= $transaccion['fecha'] ?>">
      </div>

      <div class="form-group">
        <label>Categoría *</label>
        <select name="id_categoria" required>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id_categoria'] ?>" 
              <?= $cat['id_categoria'] == $transaccion['id_categoria'] ? 'selected' : '' ?>>
              <?= ucfirst($cat['tipo']) ?> — <?= htmlspecialchars($cat['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Descripción</label>
        <input type="text" name="descripcion" maxlength="255" 
               value="<?= htmlspecialchars($transaccion['descripcion'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Método de Pago</label>
        <select name="metodo_pago">
          <option value="efectivo" <?= $transaccion['metodo_pago'] === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
          <option value="tarjeta" <?= $transaccion['metodo_pago'] === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
          <option value="transferencia" <?= $transaccion['metodo_pago'] === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
        </select>
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" name="recurrente" value="1" style="width:auto;"
            <?= $transaccion['recurrente'] ? 'checked' : '' ?>>
          Marcar como recurrente
        </label>
      </div>

      <div style="display:flex; gap:15px; margin-top:25px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">Actualizar</button>
        <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancelar</a>
      </div>
    </form>
  </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>