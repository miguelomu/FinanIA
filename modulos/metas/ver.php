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

$stmt = $pdo->prepare("SELECT * FROM aporte_meta WHERE id_meta = ? ORDER BY fecha DESC");
$stmt->execute([$id]);
$aportes = $stmt->fetchAll();

$progreso = $meta['monto_objetivo'] > 0 
    ? min(100, ($meta['monto_actual'] / $meta['monto_objetivo']) * 100) 
    : 0;

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>💰 <?= htmlspecialchars($meta['nombre']) ?></h1>
      <div style="display:flex; gap:10px;">
        <a href="aportar.php?id=<?= $meta['id_meta'] ?>" class="btn btn-primary">+ Nuevo Aporte</a>
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

        <!-- Resumen de la meta -->
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

    <!-- Historial -->
    <h2 style="color:#d4af37; margin-bottom:20px;">Historial de Aportes</h2>

    <?php if (empty($aportes)): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.1rem; margin-bottom:15px;">📭 Aún no has hecho aportes a esta meta</p>
        <a href="aportar.php?id=<?= $meta['id_meta'] ?>" class="btn btn-primary">Registrar primer aporte</a>
      </div>
    <?php else: ?>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Nota</th>
              <th style="text-align:right;">Monto</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($aportes as $a): ?>
              <tr>
                <td><?= date('d/m/Y H:i', strtotime($a['fecha'])) ?></td>
                <td><?= htmlspecialchars($a['nota'] ?: '—') ?></td>
                <td style="text-align:right; color:#27ae60; font-weight:bold;">
                  +$<?= number_format($a['monto'], 2) ?>
                </td>
                <td style="text-align:center;">
                  <div class="acciones" style="justify-content:center;">
                    <a href="editar_aporte.php?id=<?= $a['id_aporte'] ?>" class="btn-sm btn-editar">✏️ Editar</a>
                    <a href="eliminar_aporte.php?id=<?= $a['id_aporte'] ?>" 
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>