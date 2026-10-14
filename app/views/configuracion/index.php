<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>⚙️ Configuración Financiera</h1>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success">✅ Configuración actualizada</div>
    <?php endif; ?>

    <div class="card" style="padding:30px; max-width:700px;">
      <p style="color:#9fb3c8; margin-bottom:25px;">
        Estos valores se usan en las alertas y simuladores. Actualízalos cuando el gobierno 
        los publique (la tasa de usura cambia cada mes).
      </p>

      <form method="POST" action="index.php?accion=actualizar">
        <?= csrfField() ?>

        <?php foreach ($config as $c): ?>
          <div class="form-group">
            <label>
              <?= e(capitalizar(str_replace('_', ' ', $c['clave']))) ?>
              <small style="color:#7a8a9c; font-weight:normal;">
                (<?= e($c['descripcion'] ?? '') ?>)
              </small>
            </label>
            <input type="number" name="<?= e($c['clave']) ?>" step="0.01" min="0" 
                   value="<?= $c['valor'] ?>">
            <small style="color:#7a8a9c; font-size:0.75rem;">
              Última actualización: <?= formatoFechaHora($c['fecha_actualizacion']) ?>
            </small>
          </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:20px;">
          💾 Guardar cambios
        </button>
      </form>

      <p style="color:#9fb3c8; font-size:0.85rem; margin-top:25px;">
        📌 <strong>Fuente oficial:</strong> Superintendencia Financiera de Colombia — 
        <a href="https://www.superfinanciera.gov.co" target="_blank" style="color:#d4af37;">www.superfinanciera.gov.co</a>
      </p>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>