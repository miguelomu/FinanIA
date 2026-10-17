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
                    'respuesta' => 'Al registrarte, verás un <strong>asistente de 6 pasos</strong> que configura tu cuenta automáticamente: 1) Tu perfil personal y ocupación. 2) Tu situación financiera. 3) Tu efectivo y cuentas. 4) Tu primera meta. 5) Tu perfil de inversionista. 6) Categorías base.'
                ],
                [
                    'pregunta' => '¿Qué es el perfil de inversionista?',
                    'respuesta' => 'Es un análisis que hacemos con <strong>5 preguntas sobre tu tolerancia al riesgo</strong>. Según tus respuestas, te clasificamos como <strong>Conservador, Cauteloso, Moderado, Valiente o Arriesgado</strong>, y te recomendamos productos financieros adecuados para ti.'
                ],
                [
                    'pregunta' => '¿Qué es una "cuenta" en FinanIA?',
                    'respuesta' => 'Una cuenta representa <strong>dónde tienes tu dinero</strong>: Nequi, Bancolombia, efectivo, tarjeta. Cada transacción se asocia a una cuenta y su saldo se actualiza automáticamente. Puedes tener varias cuentas y el sistema suma todos los saldos en tu patrimonio.'
                ],
                [
                    'pregunta' => '¿Cómo registro mi primer gasto?',
                    'respuesta' => 'Ve a <strong>Transacciones → + Nueva Transacción</strong>. Selecciona "Gasto", el monto, fecha, categoría y cuenta de origen. El sistema actualizará automáticamente tu presupuesto y patrimonio.'
                ],
                [
                    'pregunta' => '¿Qué es el patrimonio neto?',
                    'respuesta' => 'Es <strong>todo lo que tienes menos todo lo que debes</strong>. Es el número más importante de tu dashboard, en dorado. Si crece mes a mes, tu situación financiera está mejorando.'
                ]
            ]
        ],
        
        'productividad' => [
            'titulo' => '⚡ Productividad',
            'color' => '#3498db',
            'items' => [
                [
                    'pregunta' => '¿Cómo importo el extracto de mi banco?',
                    'respuesta' => 'Ve a <strong>📂 Importar CSV</strong> en el menú lateral. Arrastra el archivo CSV de tu banco (Bancolombia, Davivienda, Nequi, Nu). El sistema: 1) Detecta automáticamente las columnas de fecha, monto y descripción. 2) Determina si es ingreso o gasto por el signo. 3) <strong>Categoriza automáticamente</strong> cada transacción según palabras clave (Éxito → Comida, Uber → Transporte, Netflix → Entretenimiento). 4) Importa todo a la cuenta que elijas.'
                ],
                [
                    'pregunta' => '¿Cómo uso la búsqueda global?',
                    'respuesta' => 'Presiona <strong>Ctrl + K</strong> en cualquier página (o Cmd + K en Mac). Se abre una barra en el sidebar donde puedes buscar: transacciones, metas, deudas, cuentas y categorías. Usa las <strong>flechas ↑↓</strong> para navegar y <strong>Enter</strong> para abrir un resultado.'
                ],
                [
                    'pregunta' => '¿Cómo funciona el calendario?',
                    'respuesta' => 'El <strong>📅 Calendario</strong> muestra tu mes completo con: 💸 transacciones reales, 💳 vencimientos de deudas, 🎯 fechas límite de metas, y 🔄 <strong>pagos recurrentes proyectados</strong>. Haz clic en cualquier día para ver el detalle. Navega entre meses con las flechas.'
                ],
                [
                    'pregunta' => '¿Qué son las transacciones recurrentes?',
                    'respuesta' => 'Son pagos que se repiten cada mes (arriendo, Netflix, cuotas). Al crear una transacción, marca la casilla <strong>"Recurrente"</strong>. Aparecerán agrupadas en el módulo <strong>🔄 Recurrentes</strong> con su impacto anual, y el calendario las <strong>proyectará automáticamente</strong> en los próximos meses con un borde punteado.'
                ],
                [
                    'pregunta' => '¿Cómo descargo una plantilla CSV?',
                    'respuesta' => 'En la página de <strong>Importar CSV</strong>, hay un botón <strong>"📥 Descargar plantilla de ejemplo"</strong>. Te da un archivo CSV con la estructura correcta que puedes llenar con tus propias transacciones.'
                ]
            ]
        ],
        
        'finanzas-basicas' => [
            'titulo' => '💰 Finanzas básicas',
            'color' => '#f39c12',
            'items' => [
                [
                    'pregunta' => '¿Cómo creo una meta de ahorro?',
                    'respuesta' => 'Ve a <strong>Metas → + Nueva Meta</strong>. Ponle nombre (ej: "Viaje a Europa"), monto objetivo, monto inicial y fecha límite. Cuando ahorres, agrega aportes con <strong>+ Aporte</strong>. El progreso se actualiza en tiempo real.'
                ],
                [
                    'pregunta' => '¿Cómo registro una deuda?',
                    'respuesta' => 'Ve a <strong>Deudas → + Nueva Deuda</strong>. Llena el acreedor, monto total, tasa de interés anual, fecha de vencimiento y pago mínimo. El sistema te alertará si la tasa supera la <strong>usura (28.79% EA)</strong> y 30 días antes del vencimiento.'
                ],
                [
                    'pregunta' => '¿Cómo creo un presupuesto mensual?',
                    'respuesta' => 'Ve a <strong>Presupuestos → + Nuevo Presupuesto</strong>. Selecciona mes y año, asigna límites a cada categoría. El sistema compara con tu gasto real y te alerta al <strong>80% y 100%</strong>.'
                ],
                [
                    'pregunta' => '¿Qué son las fuentes de ingreso?',
                    'respuesta' => 'Si tienes ingresos variables (freelance, trabajos puntuales), regístralos en <strong>💼 Fuentes de Ingreso</strong>. El sistema analiza su estabilidad (0-100%) y te ayuda a planear con ingresos irregulares.'
                ]
            ]
        ],
        
        'analisis' => [
            'titulo' => '📊 Análisis',
            'color' => '#9b59b6',
            'items' => [
                [
                    'pregunta' => '¿Qué es la Salud Financiera?',
                    'respuesta' => 'Es un <strong>puntaje de 0 a 100</strong> que evalúa: fondo de emergencia (25 pts), nivel de deuda (25 pts), tasa de ahorro (20 pts), diversificación de ingresos (15 pts) y estabilidad (15 pts). 85+ es excelente, menos de 50 requiere atención.'
                ],
                [
                    'pregunta' => '¿Qué son los logros?',
                    'respuesta' => 'Son <strong>medallas que desbloqueas</strong> al cumplir hitos: crear tu primera cuenta, 7 días seguidos registrando, completar una meta, tener salud financiera >70, etc. Ve a <strong>🏆 Mis Logros</strong> para verlos todos.'
                ],
                [
                    'pregunta' => '¿Cómo veo mis reportes?',
                    'respuesta' => 'Ve a <strong>📈 Reportes</strong>. Verás gráficas de ingresos vs gastos, gastos por categoría, top 5 gastos más altos. Puedes filtrar por fecha y exportar a PDF.'
                ],
                [
                    'pregunta' => '¿Qué es el simulador de deudas?',
                    'respuesta' => 'Compara <strong>Avalancha</strong> (paga primero la de mayor tasa) vs <strong>Bola de nieve</strong> (paga primero la más pequeña). El sistema calcula cuánto tiempo y dinero ahorras con cada estrategia.'
                ]
            ]
        ],
        
        'premium' => [
            'titulo' => '⭐ Funciones Premium',
            'color' => '#d4af37',
            'items' => [
                [
                    'pregunta' => '¿Qué incluye Premium?',
                    'respuesta' => 'Premium desbloquea: 🤖 Chatbot IA, 🏔️ Simulador de Deudas, 💰 Simulador de Inversiones, ⚙️ Configuración financiera. Es la versión completa para exprimir al máximo tus finanzas.'
                ],
                [
                    'pregunta' => '¿Cómo me hago Premium?',
                    'respuesta' => 'Ve al botón <strong>⭐ HAZTE PREMIUM</strong> en el sidebar. Elige método de pago (tarjeta, PSE, Nequi, Daviplata) y confirma. Es una <strong>simulación para demo</strong>, no se procesa ningún pago real.'
                ],
                [
                    'pregunta' => '¿Cómo funciona el Simulador de Inversiones?',
                    'respuesta' => 'Tiene <strong>4 pestañas</strong>: 1) 📜 CDT (inversión única a plazo fijo). 2) 📈 FIC (aportes mensuales con capitalización). 3) ⚖️ Comparador (los 3 productos lado a lado). 4) 🎯 Calculadora de meta ("¿cuánto debo aportar al mes?"). Todos con <strong>tasa personalizable</strong> y cálculo de rendimiento real.'
                ],
                [
                    'pregunta' => '¿Qué puede hacer el Chatbot IA?',
                    'respuesta' => 'Analiza tus datos reales y responde: "¿cuánto tengo?", "¿cuánto debo?", "¿podré ahorrar 5 millones en 6 meses?", "¿debería invertir?". Además <strong>personaliza sus respuestas según tu perfil de riesgo</strong>.'
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