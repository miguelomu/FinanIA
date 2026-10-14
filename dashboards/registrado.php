<?php
require_once __DIR__ . '/../includes/auth.php';
requiereRol(['registrado', 'premium']);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Cuenta.php';
require_once __DIR__ . '/../app/models/SaludFinanciera.php';
require_once __DIR__ . '/../app/models/Logro.php';

$usuario = usuarioActual();

// Estadísticas de transacciones
$stmt = $pdo->prepare("SELECT 
    COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END),0) AS ingresos,
    COALESCE(SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END),0) AS gastos
    FROM transaccion WHERE id_usuario = ?");
$stmt->execute([$usuario['id']]);
$stats = $stmt->fetch();
$balance = $stats['ingresos'] - $stats['gastos'];

// Patrimonio
$modelCuenta = new Cuenta($pdo);
$patrimonio = $modelCuenta->calcularPatrimonio($usuario['id']);

// Metas activas
$stmt = $pdo->prepare("SELECT 
    COUNT(*) AS total,
    COALESCE(SUM(monto_actual),0) AS ahorrado,
    COALESCE(SUM(monto_objetivo),0) AS objetivo
    FROM meta WHERE id_usuario = ? AND estado = 'activa'");
$stmt->execute([$usuario['id']]);
$metas = $stmt->fetch();

// Salud financiera
$modelSalud = new SaludFinanciera($pdo, $usuario['id']);
$salud = $modelSalud->calcular();

// Alertas de presupuesto
$stmt = $pdo->prepare(
    "SELECT c.nombre AS categoria, dp.limite,
        COALESCE((
            SELECT SUM(monto) FROM transaccion t
            WHERE t.id_usuario = ?
              AND t.id_categoria = dp.id_categoria
              AND t.tipo = 'gasto'
              AND MONTH(t.fecha) = ?
              AND YEAR(t.fecha) = ?
        ), 0) AS gastado
    FROM presupuesto p
    INNER JOIN detalle_presupuesto dp ON p.id_presupuesto = dp.id_presupuesto
    INNER JOIN categoria c ON dp.id_categoria = c.id_categoria
    WHERE p.id_usuario = ? AND p.mes = ? AND p.anio = ?
    HAVING gastado >= (limite * 0.8)"
);
$stmt->execute([
    $usuario['id'],
    date('n'), date('Y'),
    $usuario['id'], date('n'), date('Y')
]);
$alertasPresupuesto = $stmt->fetchAll();

// Logros
$modelLogro = new Logro($pdo, $usuario['id']);
$modelLogro->verificarTodos();   // Verificar logros nuevos
$logrosRecientes = $modelLogro->resumen();

// 8. Tema del usuario (para el toggle)
if (!isset($_SESSION['tema'])) {
    $stmt = $pdo->prepare("SELECT tema FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$usuario['id']]);
    $t = $stmt->fetch();
    $_SESSION['tema'] = $t['tema'] ?? 'oscuro';
}

include __DIR__ . '/../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>Dashboard</h1>
      <div>
        <span>Bienvenido, <strong><?= e($usuario['nombre']) ?></strong></span>
        <span class="badge badge-<?= e($usuario['rol']) ?>"><?= e($usuario['rol']) ?></span>
      </div>
    </div>

    <?php if (!empty($logrosRecientes['recientes'])): ?>
      <div class="card" style="margin-bottom:20px; border-left:4px solid #d4af37;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div>
            <strong style="color:#d4af37;">🏆 Logros recientes</strong>
            <div style="display:flex; gap:10px; margin-top:10px;">
              <?php foreach ($logrosRecientes['recientes'] as $l): ?>
                <div style="background:rgba(212,175,55,0.15); padding:8px 15px; border-radius:20px; font-size:0.85rem;">
                  <?= $l['icono'] ?> <?= e($l['nombre']) ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <a href="/FinanIA/modulos/logros/index.php" class="btn btn-secondary" style="font-size:0.8rem;">
            Ver todos (<?= $logrosRecientes['desbloqueados'] ?>/<?= $logrosRecientes['total'] ?>)
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Alertas de presupuesto -->
    <?php foreach ($alertasPresupuesto as $alerta): 
        $porcentaje = $alerta['limite'] > 0 ? ($alerta['gastado'] / $alerta['limite']) * 100 : 0;
        $excedido = $porcentaje >= 100;
    ?>
      <div style="background:<?= $excedido ? 'rgba(231,76,60,0.15)' : 'rgba(243,156,18,0.15)' ?>; 
                  border-left:4px solid <?= $excedido ? '#e74c3c' : '#f39c12' ?>; 
                  padding:15px; border-radius:8px; margin-bottom:15px;">
        <strong style="color:<?= $excedido ? '#ff8a80' : '#f39c12' ?>;">
          <?= $excedido ? '🚨' : '⚠️' ?> 
          Presupuesto de <?= e($alerta['categoria']) ?>: <?= number_format($porcentaje, 0) ?>%
        </strong>
        <p style="color:#c9d6e3; font-size:0.85rem; margin-top:5px;">
          Gastado: <?= formatoCOP($alerta['gastado']) ?> de <?= formatoCOP($alerta['limite']) ?>
          <?= $excedido ? ' — ¡EXCEDIDO!' : ' — Cerca del límite' ?>
        </p>
      </div>
    <?php endforeach; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cambio_rol'): ?>
      <div class="alert alert-success">
        👤 Has vuelto al rol Registrado.
      </div>
    <?php endif; ?>

    <!-- 🏆 PATRIMONIO NETO - Número principal -->
    <div class="card" style="border:2px solid #d4af37; background: linear-gradient(135deg, rgba(212,175,55,0.1), rgba(19,47,76,0.6)); text-align:center; padding:35px;">
      <h3 style="font-size:1rem; letter-spacing:2px;">💰 PATRIMONIO NETO</h3>
      <div class="value <?= $patrimonio['neto'] >= 0 ? 'positivo' : 'negativo' ?>" style="font-size:3rem; margin:15px 0;">
        <?= formatoCOP($patrimonio['neto']) ?>
      </div>
      <p style="color: #9fb3c8; font-size:0.9rem;">
        Activos <strong style="color: #27ae60;"><?= formatoCOP($patrimonio['activos']) ?></strong>
        − 
        Pasivos <strong style="color: #e74c3c;"><?= formatoCOP($patrimonio['pasivos']) ?></strong>
      </p>
    </div>

        <!-- 🏆 SALUD FINANCIERA -->
    <div class="card" style="margin-top:30px; border:2px solid <?= $salud['categoria']['color'] ?>; padding:30px;">
      <h2 style="color:<?= $salud['categoria']['color'] ?>; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
        <span style="font-size:2rem;"><?= $salud['categoria']['emoji'] ?></span>
        Salud Financiera: <?= $salud['categoria']['nombre'] ?>
      </h2>

      <!-- Número grande -->
      <div style="text-align:center; padding:20px 0;">
        <div style="font-size:4rem; font-weight:bold; color:<?= $salud['categoria']['color'] ?>;">
          <?= $salud['total'] ?><span style="font-size:2rem; color:#9fb3c8;">/100</span>
        </div>
        
        <!-- Barra de progreso -->
        <div style="max-width:500px; margin:20px auto;">
          <div class="progress" style="height:15px;">
            <div class="progress-bar" 
                 style="width: <?= $salud['total'] ?>%; background: <?= $salud['categoria']['color'] ?>;"></div>
          </div>
        </div>
      </div>

      <!-- Desglose por componente -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:15px; margin-top:25px;">
        <?php
        $componentes = [
          'fondo_emergencia' => ['nombre' => '🛡️ Fondo de emergencia', 'max' => 25],
          'nivel_deuda' => ['nombre' => '💳 Nivel de deuda', 'max' => 25],
          'tasa_ahorro' => ['nombre' => '💰 Tasa de ahorro', 'max' => 20],
          'diversificacion' => ['nombre' => '📊 Diversificación', 'max' => 15],
          'estabilidad' => ['nombre' => '📈 Estabilidad', 'max' => 15]
        ];
        
        foreach ($componentes as $key => $info):
          $c = $salud['componentes'][$key];
          $colorEstado = [
            'excelente' => '#27ae60',
            'bueno' => '#2ecc71',
            'regular' => '#f39c12',
            'alerta' => '#e67e22',
            'critico' => '#e74c3c',
            'neutral' => '#7a8a9c'
          ][$c['estado']];
        ?>
          <div style="background:rgba(10,25,41,0.6); padding:15px; border-radius:10px; border-left:4px solid <?= $colorEstado ?>;">
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
              <strong style="color:#e6edf3; font-size:0.9rem;"><?= $info['nombre'] ?></strong>
              <strong style="color:<?= $colorEstado ?>;">
                <?= $c['puntos'] ?>/<?= $info['max'] ?>
              </strong>
            </div>
            <p style="color:#9fb3c8; font-size:0.8rem; margin-bottom:8px;">
              <?= $c['descripcion'] ?>
            </p>
            <p style="color:<?= $colorEstado ?>; font-size:0.8rem;">
              💡 <?= $c['recomendacion'] ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Recomendación general -->
      <?php if ($salud['total'] < 70): ?>
        <div style="background:rgba(231,76,60,0.1); border-left:4px solid #e74c3c; padding:15px; border-radius:8px; margin-top:25px;">
          <strong style="color:#ff8a80;">🎯 Tu prioridad ahora:</strong>
          <p style="color:#ff8a80; margin-top:8px;">
            <?php
            // Encontrar el componente con menor puntaje relativo
            $peor = null;
            $peorRatio = 1;
            foreach ($componentes as $key => $info) {
              $ratio = $salud['componentes'][$key]['puntos'] / $info['max'];
              if ($ratio < $peorRatio) {
                $peorRatio = $ratio;
                $peor = $info['nombre'];
              }
            }
            echo "Enfócate en mejorar: <strong>$peor</strong>. Es el área con más oportunidad de mejora.";
            ?>
          </p>
        </div>
      <?php else: ?>
        <div style="background:rgba(39,174,96,0.1); border-left:4px solid #27ae60; padding:15px; border-radius:8px; margin-top:25px;">
          <strong style="color:#7ee8a8;">🌟 ¡Felicidades!</strong>
          <p style="color:#7ee8a8; margin-top:8px;">
            Tu salud financiera está en buen nivel. Sigue así y considera aumentar tus metas de ahorro.
          </p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="cards-grid" style="margin-top:25px;">
      <div class="card">
        <h3>📈 Activos</h3>
        <div class="value positivo"><?= formatoCOP($patrimonio['activos']) ?></div>
        <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">En <?= count($patrimonio['cuentas']) ?> cuenta(s)</p>
      </div>
      <div class="card">
        <h3>📉 Pasivos</h3>
        <div class="value negativo"><?= formatoCOP($patrimonio['pasivos']) ?></div>
        <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">Deudas pendientes</p>
      </div>
      <div class="card">
        <h3>💵 Balance del mes</h3>
        <div class="value <?= $balance >= 0 ? 'positivo' : 'negativo' ?>">
          <?= formatoCOP($balance) ?>
        </div>
        <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">Ingresos − Gastos totales</p>
      </div>
      <div class="card">
        <h3>🎯 Metas activas</h3>
        <div class="value"><?= $metas['total'] ?></div>
        <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">
          <?= formatoCOP($metas['ahorrado']) ?> de <?= formatoCOP($metas['objetivo']) ?>
        </p>
      </div>
    </div>

    <!-- 🏦 Desglose por cuenta -->
    <h2 style="color #d4af37; margin:35px 0 20px;">🏦 Distribución de tu dinero</h2>

    <?php if (empty($patrimonio['cuentas'])): ?>
      <div class="card" style="text-align:center; padding:40px;">
        <p style="font-size:1.1rem; margin-bottom:15px;">🏦 No tienes cuentas creadas</p>
        <a href="/FinanIA/modulos/cuentas/index.php?accion=crear" class="btn btn-primary">Crear primera cuenta</a>
      </div>
    <?php else: ?>
      <div class="cards-grid">
        <?php foreach ($patrimonio['cuentas'] as $c): 
          $porcentaje = $patrimonio['activos'] > 0 
            ? ($c['saldo_actual'] / $patrimonio['activos']) * 100 
            : 0;
        ?>
          <div class="card" style="border-left:4px solid <?= e($c['color']) ?>;">
            <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
              <h3 style="margin:0; font-size:1rem;"><?= e($c['nombre']) ?></h3>
              <span style="font-size:0.75rem; color: #9fb3c8;"><?= number_format($porcentaje, 1) ?>%</span>
            </div>
            <div class="value <?= $c['saldo_actual'] >= 0 ? 'positivo' : 'negativo' ?>" style="font-size:1.4rem;">
              <?= formatoCOP($c['saldo_actual']) ?>
            </div>
            <p style="color: #9fb3c8; font-size:0.75rem; margin-top:8px;">
              <?= e(capitalizar($c['tipo'])) ?>
              <?php if ($c['etiqueta']): ?>
                · <?= e($c['etiqueta']) ?>
              <?php endif; ?>
            </p>
            <div class="progress" style="margin-top:10px; height:6px;">
              <div class="progress-bar" style="width: <?= $porcentaje ?>%; background: <?= e($c['color']) ?>;"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Acciones rápidas -->
    <!-- 🌟 Upgrade a Premium -->
    <?php
    require_once __DIR__ . '/../config/dev.php';
    if (esDesarrollador()): 
    ?>

    <!-- 🛠️ Herramienta de desarrollo: cambiar a Premium -->
    <div class="card" style="border:2px solid #9b59b6; background: linear-gradient(135deg, rgba(155,89,182,0.15), rgba(19,47,76,0.6)); text-align:center; padding:30px; margin-top:30px;">
      <h3 style="color:#d5a6ff; letter-spacing:2px;">🛠️ MODO DESARROLLO</h3>
      <p style="color:#c9d6e3; margin:15px 0; font-size:0.95rem;">
        Cambiar a rol <strong>Premium</strong> para probar las funciones avanzadas.
      </p>
      <a href="/FinanIA/auth/cambiar_rol.php" 
        class="btn btn-primary" 
        style="background: linear-gradient(135deg, #9b59b6, #6c3483); color:#fff; padding:12px 30px;">
        🌟 Cambiar a Premium
      </a>
    </div>
    <?php endif; ?>
    <h2 style="color: #d4af37; margin:35px 0 20px;">⚡ Acciones rápidas</h2>
    <div style="display:flex; gap:15px; flex-wrap:wrap;">
      <a href="/FinanIA/modulos/transacciones/index.php?accion=crear" class="btn btn-primary">+ Nueva Transacción</a>
      <a href="/FinanIA/modulos/metas/index.php?accion=crear" class="btn btn-primary">+ Nueva Meta</a>
      <a href="/FinanIA/modulos/deudas/index.php?accion=crear" class="btn btn-primary">+ Nueva Deuda</a>
      <a href="/FinanIA/modulos/cuentas/index.php?accion=crear" class="btn btn-primary">+ Nueva Cuenta</a>
      <a href="/FinanIA/modulos/reportes/index.php" class="btn btn-secondary">📈 Ver Reportes</a>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>