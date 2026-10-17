<?php
$usuario = usuarioActual();
$paginaActual = basename($_SERVER['PHP_SELF']);
$esPremium = $usuario['rol'] === 'premium';
$esDesarrollador = function_exists('esDesarrollador') && esDesarrollador();
?>
<aside class="sidebar">
  <h2>💰 FinanIA</h2>
  
  <div class="buscador-global">
  <span class="buscador-icono" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#d4af37;">🔍</span>
  <input type="text" 
         class="buscador-input" 
         placeholder="Buscar (Ctrl+K)..." 
         autocomplete="off">
  <div class="buscador-resultados"></div>
</div>
  
  <nav>
    <!-- Comunes a todos -->
    <a href="/FinanIA/dashboards/<?= $usuario['rol'] ?>.php" 
       class="<?= $paginaActual === $usuario['rol'] . '.php' ? 'active' : '' ?>">
      📊 Dashboard
    </a>
    <a href="/FinanIA/modulos/cuentas/index.php">🏦 Cuentas</a>
    <a href="/FinanIA/modulos/transacciones/index.php">💸 Transacciones</a>
    <a href="/FinanIA/modulos/importar/index.php">📂 Importar CSV</a>
    <a href="/FinanIA/modulos/categorias/index.php">🏷️ Categorías</a>
    <a href="/FinanIA/modulos/metas/index.php">🎯 Metas</a>
    <a href="/FinanIA/modulos/deudas/index.php">💳 Deudas</a>
    <a href="/FinanIA/modulos/presupuestos/index.php">📅 Presupuestos</a>
    <a href="/FinanIA/modulos/reportes/index.php">📈 Reportes</a>
    <a href="/FinanIA/modulos/calendario/index.php">📅 Calendario</a>
    <a href="/FinanIA/modulos/fuentes_ingreso/index.php">💼 Fuentes de Ingreso</a>
    <a href="/FinanIA/modulos/recurrentes/index.php">🔄 Recurrentes</a>
    <a href="/FinanIA/modulos/logros/index.php">🏆 Mis Logros</a>
    <a href="/FinanIA/modulos/ayuda/index.php">❓ Centro de Ayuda</a>
    
    <!-- Sección Premium -->
    <hr style="border-color:rgba(212,175,55,0.2); margin:15px 0;">
    <p style="color: #d4af37; font-size:0.7rem; text-align:center; margin-bottom:10px; letter-spacing:1px;">
      ⭐ PREMIUM
    </p>

    <?php if ($esPremium): ?>
      <a href="/FinanIA/modulos/chatbot/index.php">🤖 Asistente IA</a>
      <a href="/FinanIA/modulos/simulador/index.php">🏔️ Simulador de Deudas</a>
      <a href="/FinanIA/modulos/simulador_inversion/index.php">💰 Simulador de Inversiones</a>
      <a href="/FinanIA/modulos/configuracion/index.php">⚙️ Configuración</a>
    <?php else: ?>
      <!-- Bloqueado para Registrados -->
      <a href="/FinanIA/premium/index.php" 
         style="opacity:0.5; cursor:pointer;"
         title="Actualiza a Premium para acceder">
        🔒 🤖 Asistente IA
      </a>
      <a href="/FinanIA/premium/index.php" 
         style="opacity:0.5; cursor:pointer;"
         title="Actualiza a Premium para acceder">
        🔒 🏔️ Simulador de Deudas
      </a>
      <a href="/FinanIA/premium/index.php" 
         style="opacity:0.5; cursor:pointer;"
         title="Actualiza a Premium para acceder">
        🔒 💰 Simulador de Inversiones
      </a>
      <a href="/FinanIA/premium/index.php" 
         style="opacity:0.5; cursor:pointer;"
         title="Actualiza a Premium para acceder">
        🔒 ⚙️ Configuración
      </a>
      
      <!-- Botón destacado de upgrade -->
      <a href="/FinanIA/premium/index.php" 
         style="background: linear-gradient(135deg, #9b59b6, #6c3483); 
                color:#fff; text-align:center; font-weight:bold; margin-top:10px;">
        ⭐ HAZTE PREMIUM
      </a>
    <?php endif; ?>

    <hr style="border-color:rgba(212,175,55,0.2); margin:15px 0;">
    
    <!-- Toggle de tema -->
    <div style="text-align:center; padding:5px 0;">
      <?php include __DIR__ . '/toggle_tema.php'; ?>
    </div>

    <!-- Modo desarrollo -->
    <?php if ($esDesarrollador): ?>
      <div style="text-align:center; margin-top:10px; padding:10px; 
                  background:rgba(231,76,60,0.15); border-radius:8px;">
        <p style="color:#ff8a80; font-size:0.7rem; margin-bottom:5px;">🛠️ MODO DESARROLLO</p>
        <a href="/FinanIA/auth/cambiar_rol.php" 
           style="color:#ff8a80; font-size:0.75rem; padding:5px;">
          Cambiar a <?= $esPremium ? 'Registrado' : 'Premium' ?>
        </a>
      </div>
    <?php endif; ?>

    <a href="/FinanIA/auth/logout.php" style="color:#e74c3c; margin-top:15px;">
      🚪 Cerrar Sesión
    </a>
  </nav>
</aside>