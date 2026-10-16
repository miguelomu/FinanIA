<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

// Verificar si ya vio la bienvenida
$stmt = $pdo->prepare("SELECT bienvenida_vista FROM usuario WHERE id_usuario = ?");
$stmt->execute([$usuario['id']]);
$u = $stmt->fetch();

// Si ya la vio, redirigir al dashboard
if ($u && $u['bienvenida_vista']) {
    header('Location: /FinanIA/dashboards/' . $usuario['rol'] . '.php');
    exit;
}

// Marcar como vista
$stmt = $pdo->prepare("UPDATE usuario SET bienvenida_vista = TRUE WHERE id_usuario = ?");
$stmt->execute([$usuario['id']]);

// Renderizar la vista
include __DIR__ . '/../../includes/header.php';
?>

<!-- Confeti animado -->
<canvas id="confeti" style="position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:1;"></canvas>

<div class="layout" style="justify-content:center; align-items:center; min-height:100vh; padding:20px; position:relative; z-index:2;">
  <main class="main-content" style="margin-left:0; max-width:900px; text-align:center;">
    
    <!-- Ícono principal -->
    <div style="font-size:5rem; margin-bottom:20px; animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);">
      🎉
    </div>

    <!-- Título -->
    <h1 style="color:#d4af37; font-size:2.5rem; margin-bottom:15px; animation: fadeInUp 0.6s ease-out 0.2s backwards;">
      ¡Bienvenido a FinanIA!
    </h1>

    <!-- Nombre -->
    <p style="color:#c9d6e3; font-size:1.3rem; margin-bottom:40px; animation: fadeInUp 0.6s ease-out 0.4s backwards;">
      Hola, <strong style="color:#d4af37;"><?= e($usuario['nombre']) ?></strong> 👋
    </p>

    <!-- Subtítulo -->
    <p style="color:#9fb3c8; font-size:1.1rem; max-width:600px; margin:0 auto 50px auto; line-height:1.7; animation: fadeInUp 0.6s ease-out 0.6s backwards;">
      Tu cuenta está lista. Esto es lo que puedes hacer con FinanIA:
    </p>

    <!-- 3 funciones clave -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:25px; margin-bottom:50px;">
      
      <div class="card" style="padding:30px; text-align:center; animation: fadeInUp 0.6s ease-out 0.8s backwards; border-top:4px solid #3498db;">
        <div style="font-size:3rem; margin-bottom:15px;">📊</div>
        <h3 style="color:#6bb6ff; font-size:1.2rem; margin-bottom:12px;">Visualiza tu patrimonio</h3>
        <p style="color:#9fb3c8; font-size:0.9rem; line-height:1.6;">
          Mira en tiempo real cuánto tienes, cuánto debes y cuál es tu salud financiera.
        </p>
      </div>

      <div class="card" style="padding:30px; text-align:center; animation: fadeInUp 0.6s ease-out 1.0s backwards; border-top:4px solid #27ae60;">
        <div style="font-size:3rem; margin-bottom:15px;">🎯</div>
        <h3 style="color:#7ee8a8; font-size:1.2rem; margin-bottom:12px;">Cumple tus metas</h3>
        <p style="color:#9fb3c8; font-size:0.9rem; line-height:1.6;">
          Ahorra para lo que quieras: un viaje, un carro, tu fondo de emergencia.
        </p>
      </div>

      <div class="card" style="padding:30px; text-align:center; animation: fadeInUp 0.6s ease-out 1.2s backwards; border-top:4px solid #d4af37;">
        <div style="font-size:3rem; margin-bottom:15px;">💰</div>
        <h3 style="color:#d4af37; font-size:1.2rem; margin-bottom:12px;">Controla tus gastos</h3>
        <p style="color:#9fb3c8; font-size:0.9rem; line-height:1.6;">
          Registra cada movimiento y descubre en qué se va tu dinero.
        </p>
      </div>

    </div>

    <!-- Botón comenzar -->
    <div style="animation: fadeInUp 0.6s ease-out 1.4s backwards;">
      <a href="/FinanIA/dashboards/<?= $usuario['rol'] ?>.php" 
         class="btn btn-primary" 
         style="padding:18px 50px; font-size:1.15rem; border-radius:50px; display:inline-block;">
        Comenzar ahora →
      </a>
    </div>

    <!-- Divisor -->
    <div style="margin:50px auto; max-width:400px; animation: fadeInUp 0.6s ease-out 1.6s backwards;">
      <div style="display:flex; align-items:center; gap:15px;">
        <hr style="flex:1; border-color:rgba(212,175,55,0.2);">
        <span style="color:#7a8a9c; font-size:0.8rem;">O</span>
        <hr style="flex:1; border-color:rgba(212,175,55,0.2);">
      </div>
    </div>

    <!-- Perfil de riesgo calculado -->
    <?php if (isset($_SESSION['perfil_calculado'])): 
        $perfil = $_SESSION['perfil_calculado'];
        unset($_SESSION['perfil_calculado']);  // Limpiar después de mostrar
    ?>
    <div class="card" style="border:2px solid <?= $perfil['colores']['principal'] ?>; background:linear-gradient(135deg, <?= $perfil['colores']['principal'] ?>15, rgba(19,47,76,0.6)); padding:35px; margin-bottom:40px; animation: fadeInUp 0.6s ease-out 1.4s backwards;">
        <h2 style="color:<?= $perfil['colores']['principal'] ?>; margin-bottom:15px; font-size:1.4rem;">
        <?= $perfil['colores']['emoji'] ?> Tu perfil: <?= $perfil['colores']['nombre'] ?>
        </h2>
        <p style="color:#c9d6e3; margin-bottom:20px; line-height:1.7;">
        <?= e($perfil['descripcion']) ?>
        </p>
        <div style="background:rgba(10,25,41,0.5); padding:20px; border-radius:12px;">
        <p style="color:<?= $perfil['colores']['principal'] ?>; font-weight:600; margin-bottom:12px;">
            💡 Productos recomendados para ti:
        </p>
        <ul style="color:#c9d6e3; padding-left:20px; line-height:1.9;">
            <?php foreach ($perfil['recomendaciones'] as $rec): ?>
            <li><?= e($rec) ?></li>
            <?php endforeach; ?>
        </ul>
        </div>
        <p style="color:#9fb3c8; font-size:0.85rem; margin-top:20px;">
        Puntaje de perfil: <strong><?= $perfil['puntaje'] ?>/100</strong>
        </p>
    </div>
    <?php endif; ?>

    <!-- CTA Premium -->
    <div class="card" style="border:2px solid #9b59b6; background:linear-gradient(135deg, rgba(155,89,182,0.15), rgba(19,47,76,0.6)); padding:30px; animation: fadeInUp 0.6s ease-out 1.8s backwards; max-width:600px; margin:0 auto;">
      <div style="font-size:2rem; margin-bottom:10px;">⭐</div>
      <h3 style="color:#d5a6ff; font-size:1.2rem; margin-bottom:12px;">¿Quieres más?</h3>
      <p style="color:#c9d6e3; font-size:0.9rem; margin-bottom:20px;">
        Con Premium accedes a un asistente IA, simuladores financieros y análisis avanzado.
      </p>
      <a href="/FinanIA/premium/index.php" 
         class="btn btn-secondary" 
         style="padding:12px 30px;">
        Conocer Premium
      </a>
    </div>

  </main>
</div>

<style>
  @keyframes bounceIn {
    0% {
      transform: scale(0);
      opacity: 0;
    }
    50% {
      transform: scale(1.2);
    }
    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @media (max-width: 768px) {
    h1 { font-size: 1.8rem !important; }
    .main-content { padding: 20px !important; }
  }
</style>

<script>
// ============================================
// CONFETI ANIMADO
// ============================================
(function() {
  const canvas = document.getElementById('confeti');
  const ctx = canvas.getContext('2d');
  
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
  
  const colores = ['#d4af37', '#27ae60', '#3498db', '#9b59b6', '#e74c3c', '#f39c12'];
  const confetis = [];
  const cantidadConfeti = 120;
  
  class Confeti {
    constructor() {
      this.x = Math.random() * canvas.width;
      this.y = Math.random() * canvas.height - canvas.height;
      this.width = Math.random() * 10 + 5;
      this.height = Math.random() * 6 + 4;
      this.color = colores[Math.floor(Math.random() * colores.length)];
      this.velocidadY = Math.random() * 2 + 2;
      this.velocidadX = Math.random() * 2 - 1;
      this.rotacion = Math.random() * 360;
      this.velocidadRotacion = Math.random() * 4 - 2;
      this.opacidad = 1;
    }
    
    actualizar() {
      this.y += this.velocidadY;
      this.x += this.velocidadX;
      this.rotacion += this.velocidadRotacion;
      
      // Desvanecer cerca del final
      if (this.y > canvas.height - 100) {
        this.opacidad -= 0.02;
      }
      
      // Reiniciar si sale de pantalla
      if (this.y > canvas.height + 20 || this.opacidad <= 0) {
        this.y = -20;
        this.x = Math.random() * canvas.width;
        this.opacidad = 1;
      }
    }
    
    dibujar() {
      ctx.save();
      ctx.globalAlpha = this.opacidad;
      ctx.translate(this.x + this.width / 2, this.y + this.height / 2);
      ctx.rotate(this.rotacion * Math.PI / 180);
      ctx.fillStyle = this.color;
      ctx.fillRect(-this.width / 2, -this.height / 2, this.width, this.height);
      ctx.restore();
    }
  }
  
  // Crear confetis
  for (let i = 0; i < cantidadConfeti; i++) {
    confetis.push(new Confeti());
  }
  
  let animando = true;
  
  function animar() {
    if (!animando) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    confetis.forEach(c => {
      c.actualizar();
      c.dibujar();
    });
    requestAnimationFrame(animar);
  }
  
  animar();
  
  // Detener confeti después de 8 segundos
  setTimeout(() => {
    let fadeOut = setInterval(() => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      let visible = false;
      confetis.forEach(c => {
        c.opacidad -= 0.05;
        if (c.opacidad > 0) {
          c.dibujar();
          visible = true;
        }
      });
      if (!visible) {
        clearInterval(fadeOut);
        animando = false;
        canvas.style.display = 'none';
      }
    }, 50);
  }, 8000);
  
  // Ajustar tamaño al redimensionar
  window.addEventListener('resize', () => {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  });
})();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>