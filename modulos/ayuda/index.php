<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();

include __DIR__ . '/../../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>❓ Centro de Ayuda</h1>
      <input type="text" 
             id="buscarAyuda" 
             placeholder="Buscar ayuda..." 
             style="padding:10px 15px; border-radius:20px; background:rgba(10,25,41,0.7); border:1px solid rgba(212,175,55,0.3); color:#e6edf3; width:300px;">
    </div>

    <!-- Hero -->
    <div class="card" style="border:2px solid #d4af37; background:linear-gradient(135deg, rgba(212,175,55,0.1), rgba(19,47,76,0.6)); padding:40px; text-align:center; margin-bottom:30px;">
      <div style="font-size:4rem; margin-bottom:15px;">📚</div>
      <h1 style="color:#d4af37; margin-bottom:15px;">Guía completa de FinanIA</h1>
      <p style="color:#c9d6e3; max-width:600px; margin:0 auto;">
        Todo lo que necesitas saber para aprovechar FinanIA al máximo.
        Haz clic en cualquier sección para ver los detalles.
      </p>
    </div>

    <!-- Índice rápido -->
    <div class="cards-grid" style="margin-bottom:30px;">
      <a href="#primeros-pasos" class="card" style="text-decoration:none; text-align:center; cursor:pointer; border-left:4px solid #27ae60;">
        <div style="font-size:2.5rem; margin-bottom:10px;">🚀</div>
        <h3 style="color:#7ee8a8;">Primeros pasos</h3>
        <p style="color:#9fb3c8; font-size:0.85rem;">Configura tu cuenta en 5 minutos</p>
      </a>
      <a href="#finanzas-basicas" class="card" style="text-decoration:none; text-align:center; cursor:pointer; border-left:4px solid #3498db;">
        <div style="font-size:2.5rem; margin-bottom:10px;">💰</div>
        <h3 style="color:#6bb6ff;">Finanzas básicas</h3>
        <p style="color:#9fb3c8; font-size:0.85rem;">Cuentas, transacciones, metas</p>
      </a>
      <a href="#analisis" class="card" style="text-decoration:none; text-align:center; cursor:pointer; border-left:4px solid #f39c12;">
        <div style="font-size:2.5rem; margin-bottom:10px;">📊</div>
        <h3 style="color:#f39c12;">Análisis</h3>
        <p style="color:#9fb3c8; font-size:0.85rem;">Reportes, salud financiera</p>
      </a>
      <a href="#premium" class="card" style="text-decoration:none; text-align:center; cursor:pointer; border-left:4px solid #9b59b6;">
        <div style="font-size:2.5rem; margin-bottom:10px;">⭐</div>
        <h3 style="color:#d5a6ff;">Funciones Premium</h3>
        <p style="color:#9fb3c8; font-size:0.85rem;">IA, simuladores, análisis avanzado</p>
      </a>
    </div>

    <!-- Secciones de ayuda -->
    <?php
    $secciones = [
        'primeros-pasos' => [
            'titulo' => '🚀 Primeros pasos',
            'color' => '#27ae60',
            'items' => [
                [
                    'pregunta' => '¿Cómo empiezo a usar FinanIA?',
                    'respuesta' => 'Al registrarte, verás un <strong>asistente de 3 pasos</strong> que configura tu cuenta automáticamente: 1) Crea tu primera cuenta con el saldo actual. 2) Establece tu primera meta de ahorro. 3) Crea categorías básicas (Comida, Transporte, etc.).'
                ],
                [
                    'pregunta' => '¿Qué es una "cuenta" en FinanIA?',
                    'respuesta' => 'Una cuenta representa <strong>dónde tienes tu dinero</strong>. Puede ser Nequi, Bancolombia, una tarjeta, efectivo, etc. Cada transacción que registres se asocia a una cuenta y su saldo se actualiza automáticamente.'
                ],
                [
                    'pregunta' => '¿Cómo registro mi primer gasto?',
                    'respuesta' => 'Ve a <strong>Transacciones → + Nueva Transacción</strong>. Selecciona "Gasto", el monto, fecha, categoría y la cuenta de donde salió el dinero. El sistema calculará automáticamente el impacto en tu presupuesto y patrimonio.'
                ],
                [
                    'pregunta' => '¿Qué es el patrimonio neto?',
                    'respuesta' => 'Es <strong>todo lo que tienes menos todo lo que debes</strong>. Es el número más importante de tu dashboard. Si crece mes a mes, tu situación financiera está mejorando.'
                ]
            ]
        ],
        'finanzas-basicas' => [
            'titulo' => '💰 Finanzas básicas',
            'color' => '#3498db',
            'items' => [
                [
                    'pregunta' => '¿Cómo creo una meta de ahorro?',
                    'respuesta' => 'Ve a <strong>Metas → + Nueva Meta</strong>. Ponle nombre (ej: "Viaje a Europa"), monto objetivo, monto inicial (si ya tienes algo) y fecha límite. Cuando ahorres, agrega aportes con <strong>+ Aporte</strong>. El progreso se actualiza en tiempo real.'
                ],
                [
                    'pregunta' => '¿Cómo registro una deuda?',
                    'respuesta' => 'Ve a <strong>Deudas → + Nueva Deuda</strong>. Llena el acreedor, monto total, tasa de interés anual, fecha de vencimiento y pago mínimo. El sistema te alertará si la tasa supera la usura (28.79% EA) y 30 días antes del vencimiento.'
                ],
                [
                    'pregunta' => '¿Cómo creo un presupuesto mensual?',
                    'respuesta' => 'Ve a <strong>Presupuestos → + Nuevo Presupuesto</strong>. Selecciona mes y año, asigna un límite a cada categoría de gasto. El sistema compara automáticamente con tu gasto real y te alerta al 80% y 100%.'
                ],
                [
                    'pregunta' => '¿Qué es un gasto recurrente?',
                    'respuesta' => 'Son gastos que se repiten cada mes (arriendo, Netflix, cuotas). Al crear una transacción, marca la casilla <strong>"recurrente"</strong>. Aparecerán agrupados en el módulo <strong>Recurrentes</strong> mostrando el impacto anual.'
                ],
                [
                    'pregunta' => '¿Cómo funcionan las fuentes de ingreso?',
                    'respuesta' => 'Si tienes ingresos variables (freelance, trabajos puntuales), regístralos en <strong>Fuentes de Ingreso</strong>. El sistema analiza su estabilidad (0-100%) y te ayuda a planear con ingresos irregulares.'
                ]
            ]
        ],
        'analisis' => [
            'titulo' => '📊 Análisis',
            'color' => '#f39c12',
            'items' => [
                [
                    'pregunta' => '¿Qué es la Salud Financiera?',
                    'respuesta' => 'Es un <strong>puntaje de 0 a 100</strong> que evalúa: fondo de emergencia (25 pts), nivel de deuda (25 pts), tasa de ahorro (20 pts), diversificación de ingresos (15 pts) y estabilidad (15 pts). 85+ es excelente, 70+ es bueno, menos de 50 requiere atención.'
                ],
                [
                    'pregunta' => '¿Cómo veo mis reportes?',
                    'respuesta' => 'Ve a <strong>Reportes</strong> en el menú. Verás gráficas de ingresos vs gastos por mes, gastos por categoría, top 5 gastos más altos. Puedes filtrar por rango de fechas y exportar a PDF.'
                ],
                [
                    'pregunta' => '¿Cómo funciona la alerta de tasa de usura?',
                    'respuesta' => 'La usura es el <strong>máximo interés legal</strong> en Colombia (28.79% EA). Si una deuda tuya cobra más, el sistema muestra una <strong>alerta roja</strong> en el listado y en el simulador de deudas. Eso es ilegal y puedes denunciarlo.'
                ]
            ]
        ],
        'premium' => [
            'titulo' => '⭐ Funciones Premium',
            'color' => '#9b59b6',
            'items' => [
                [
                    'pregunta' => '¿Qué incluye Premium?',
                    'respuesta' => 'Premium desbloquea: 🤖 Chatbot IA, 🏔️ Simulador de Deudas, 💰 Simulador de Inversiones, ⚙️ Configuración financiera avanzada. Es la versión completa de FinanIA para usuarios que quieren exprimir al máximo sus finanzas.'
                ],
                [
                    'pregunta' => '¿Cómo me hago Premium?',
                    'respuesta' => 'Ve al botón <strong>⭐ HAZTE PREMIUM</strong> en el menú lateral. Elige método de pago (tarjeta, PSE, Nequi, Daviplata) y confirma. Es una simulación para demo, no se procesa ningún pago real.'
                ],
                [
                    'pregunta' => '¿Cómo funciona el Simulador de Deudas?',
                    'respuesta' => 'Compara 2 estrategias: <strong>Avalancha</strong> (paga primero la de mayor interés) y <strong>Bola de nieve</strong> (paga primero la más pequeña). El sistema calcula cuánto tiempo y cuánto dinero ahorras con cada una, y te recomienda la mejor para tu caso.'
                ],
                [
                    'pregunta' => '¿Cómo funciona el Simulador de Inversiones?',
                    'respuesta' => 'Ingresa monto y plazo. El sistema compara CDT, cuenta de ahorros y fondo, mostrando: <strong>rendimiento nominal</strong>, retención en la fuente, gravamen 4x1000, y <strong>rendimiento real</strong> (descontando inflación). El rendimiento real es el número más importante.'
                ],
                [
                    'pregunta' => '¿Qué puede hacer el Chatbot IA?',
                    'respuesta' => 'El chatbot analiza tus datos reales y responde preguntas como: "¿cuánto tengo?", "¿cuánto debo?", "¿podré ahorrar 5 millones en 6 meses?", "¿debería invertir?". Puede hacer proyecciones y darte recomendaciones personalizadas.'
                ]
            ]
        ]
    ];

    foreach ($secciones as $id => $seccion): ?>
      <section id="<?= $id ?>" style="margin-bottom:50px;">
        <h2 style="color:<?= $seccion['color'] ?>; margin-bottom:25px; padding-left:15px; border-left:4px solid <?= $seccion['color'] ?>;">
          <?= $seccion['titulo'] ?>
        </h2>
        
        <?php foreach ($seccion['items'] as $item): ?>
          <div class="acordeon" style="background:rgba(19,47,76,0.5); border-radius:12px; margin-bottom:12px; overflow:hidden; border:1px solid rgba(212,175,55,0.15);">
            <button onclick="toggleAcordeon(this)" 
                    style="width:100%; padding:18px 25px; background:transparent; border:none; color:#e6edf3; 
                           text-align:left; cursor:pointer; font-size:1rem; font-weight:600;
                           display:flex; justify-content:space-between; align-items:center;">
              <span><?= $item['pregunta'] ?></span>
              <span class="icono" style="font-size:1.5rem; color:<?= $seccion['color'] ?>; transition:transform 0.3s;">▾</span>
            </button>
            <div class="contenido" style="display:none; padding:0 25px 20px 25px; color:#c9d6e3; line-height:1.7;">
              <?= $item['respuesta'] ?>
            </div>
          </div>
        <?php endforeach; ?>
      </section>
    <?php endforeach; ?>

    <!-- CTA final -->
    <div class="card" style="border:2px solid #d4af37; background:linear-gradient(135deg, rgba(212,175,55,0.15), rgba(19,47,76,0.6)); padding:40px; text-align:center; margin-top:50px;">
      <div style="font-size:3rem; margin-bottom:15px;">🤖</div>
      <h2 style="color:#d4af37; margin-bottom:10px;">¿Aún tienes dudas?</h2>
      <p style="color:#c9d6e3; margin-bottom:20px;">
        Pregúntale al Asistente IA. Analiza tus datos y te responde personalizado.
      </p>
      <a href="/FinanIA/modulos/chatbot/index.php" class="btn btn-primary" style="padding:12px 30px;">
        🤖 Ir al Asistente IA
      </a>
    </div>
  </main>
</div>

<style>
  .acordeon button:hover {
    background: rgba(212, 175, 55, 0.05);
  }
  .acordeon .icono.abierto {
    transform: rotate(180deg);
  }
</style>

<script>
function toggleAcordeon(btn) {
  const contenido = btn.nextElementSibling;
  const icono = btn.querySelector('.icono');
  
  if (contenido.style.display === 'none') {
    contenido.style.display = 'block';
    icono.classList.add('abierto');
  } else {
    contenido.style.display = 'none';
    icono.classList.remove('abierto');
  }
}

// Búsqueda simple
document.getElementById('buscarAyuda').addEventListener('input', function(e) {
  const query = e.target.value.toLowerCase();
  const acordeones = document.querySelectorAll('.acordeon');
  
  acordeones.forEach(ac => {
    const texto = ac.textContent.toLowerCase();
    ac.style.display = texto.includes(query) ? 'block' : 'none';
  });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>