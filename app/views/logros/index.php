<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>🏆 Mis Logros</h1>
    </div>

    <?php if (!empty($nuevos)): ?>
      <div class="alert alert-success">
        🎉 ¡Has desbloqueado <?= count($nuevos) ?> nuevo(s) logro(s)!
      </div>
    <?php endif; ?>

    <!-- Resumen -->
    <div class="cards-grid">
      <div class="card" style="border:2px solid #d4af37; text-align:center;">
        <h3>Logros desbloqueados</h3>
        <div class="value" style="font-size:2.5rem; color: #d4af37;">
          <?= $resumen['desbloqueados'] ?> / <?= $resumen['total'] ?>
        </div>
      </div>
      <div class="card" style="text-align:center;">
        <h3>Puntos totales</h3>
        <div class="value" style="font-size:2.5rem; color: #27ae60;">
          <?= $resumen['puntos'] ?>
        </div>
      </div>
      <div class="card" style="text-align:center;">
        <h3>Progreso</h3>
        <div class="value" style="font-size:2.5rem;">
          <?= $resumen['total'] > 0 ? round(($resumen['desbloqueados'] / $resumen['total']) * 100) : 0 ?>%
        </div>
      </div>
      <div class="card" style="text-align:center;">
        <h3>Categorías</h3>
        <div class="value" style="font-size:1rem; color: #9fb3c8;">
          🎯 Ahorro · 💳 Deuda · 📊 Disciplina · 🌟 Maestría
        </div>
      </div>
    </div>

    <!-- Grid de logros -->
    <h2 style="color: #d4af37; margin:35px 0 20px;">Todos los logros</h2>

    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:20px;">
      <?php foreach ($resumen['logros'] as $l): ?>
        <div class="card" style="
          border:2px solid <?= $l['desbloqueado'] ? $l['color'] : 'rgba(122,138,156,0.3)' ?>;
          <?= !$l['desbloqueado'] ? 'opacity:0.4; filter:grayscale(1);' : '' ?>
          text-align:center;
          padding:25px 20px;
          position:relative;
        ">
          <div style="font-size:3rem; margin-bottom:10px;">
            <?= $l['desbloqueado'] ? $l['icono'] : '🔒' ?>
          </div>
          <h3 style="color:<?= $l['desbloqueado'] ? $l['color'] : '#7a8a9c' ?>; font-size:1rem; margin-bottom:8px;">
            <?= e($l['nombre']) ?>
          </h3>
          <p style="color: #9fb3c8; font-size:0.8rem; line-height:1.5;">
            <?= e($l['descripcion']) ?>
          </p>
          <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
            <span style="color: #d4af37; font-weight:bold; font-size:0.85rem;">
              +<?= $l['puntos'] ?> pts
            </span>
            <?php if ($l['desbloqueado']): ?>
              <span style="color: #27ae60; font-size:0.75rem;">
                ✓ <?= formatoFecha($l['fecha_desbloqueo']) ?>
              </span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>