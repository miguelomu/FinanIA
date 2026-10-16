<link rel="stylesheet" href="../assets/css/style.css">
<?php
require_once __DIR__ . '/../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../config/database.php';

$usuario = usuarioActual();

// Verificar si ya completó el onboarding
$stmt = $pdo->prepare("SELECT onboarding_completado FROM usuario WHERE id_usuario = ?");
$stmt->execute([$usuario['id']]);
$u = $stmt->fetch();

if ($u && $u['onboarding_completado']) {
    header('Location: /FinanIA/dashboards/' . $usuario['rol'] . '.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="layout" style="justify-content: center; align-items: center; min-height: 100vh; padding: 30px;">
  <main class="main-content" style="margin-left: 0; max-width: 800px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
      <div style="font-size: 3.5rem; margin-bottom: 10px;">🚀</div>
      <h1 style="color: #d4af37; font-size: 1.8rem;">Configura tu cuenta</h1>
      <p style="color: #9fb3c8; font-size: 1rem;">
        Responde 6 preguntas rápidas para personalizar tu experiencia
      </p>
    </div>

    <!-- Indicador de pasos -->
    <!-- Indicador de pasos (clickeable) -->
    <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 35px; flex-wrap: wrap;">
    <?php for ($i = 1; $i <= 6; $i++): ?>
        <button type="button" 
                class="paso-indicador <?= $i === 1 ? 'actual' : '' ?>" 
                data-paso="<?= $i ?>" 
                title="Ir al paso <?= $i ?>"
                style="width: 36px; height: 36px; border-radius: 50%; 
                    background: <?= $i === 1 ? '#d4af37' : 'rgba(122,138,156,0.3)' ?>; 
                    color: <?= $i === 1 ? '#0a1929' : '#9fb3c8' ?>; 
                    display: flex; align-items: center; justify-content: center; 
                    font-weight: bold; font-size: 0.9rem;
                    border: none; cursor: pointer;
                    font-family: inherit;">
        <?= $i ?>
        </button>
    <?php endfor; ?>
    </div>

    <form id="formOnboarding" method="POST" action="procesar_onboarding.php">
      <?= csrfField() ?>

      <!-- ============================================
           PASO 1: PERFIL PERSONAL
           ============================================ -->
      <div class="paso-form" data-paso="1">
        <h2 style="color: #d4af37; margin-bottom: 15px;">👤 Cuéntanos sobre ti</h2>
        <p style="color: #9fb3c8; margin-bottom: 25px;">
          Esto nos ayuda a personalizar tus recomendaciones.
        </p>

        <div class="form-group">
          <label>¿Cuál es tu ocupación?</label>
          <select name="ocupacion" required>
            <option value="">Selecciona...</option>
            <option value="estudiante">🎓 Estudiante</option>
            <option value="empleado">💼 Empleado</option>
            <option value="freelance">💻 Freelance</option>
            <option value="independiente">🏪 Independiente</option>
            <option value="desempleado">🔍 Desempleado</option>
            <option value="otro">📦 Otro</option>
          </select>
        </div>

        <div class="form-group">
          <label>¿Cómo son tus ingresos?</label>
          <select name="tipo_ingreso" required>
            <option value="">Selecciona...</option>
            <option value="fijo">📌 Fijo (sueldo mensual)</option>
            <option value="variable">📊 Variable (freelance, comisiones)</option>
            <option value="mixto">🔀 Mixto (parte fija, parte variable)</option>
          </select>
        </div>

        <div class="form-group">
          <label>¿Cuántas personas dependen económicamente de ti?</label>
          <select name="dependientes" required>
            <option value="0">Ninguna (solo yo)</option>
            <option value="1">1 persona</option>
            <option value="2">2 personas</option>
            <option value="3">3 o más</option>
          </select>
        </div>
      </div>

      <!-- ============================================
           PASO 2: SITUACIÓN FINANCIERA
           ============================================ -->
      <div class="paso-form" data-paso="2" style="display: none;">
        <h2 style="color: #d4af37; margin-bottom: 15px;">💼 Tu situación financiera actual</h2>
        <p style="color: #9fb3c8; margin-bottom: 25px;">
          Un diagnóstico rápido de dónde estás hoy.
        </p>

        <div class="form-group">
          <label>¿Tienes cuenta bancaria o billetera digital?</label>
          <select name="tiene_banco" required>
            <option value="1">Sí, tengo al menos una</option>
            <option value="0">No, uso solo efectivo</option>
          </select>
        </div>

        <div class="form-group">
          <label>¿Tienes un fondo de emergencia?</label>
          <select name="tiene_fondo_emergencia" required>
            <option value="1">Sí, tengo 3+ meses de gastos</option>
            <option value="0">No, no tengo o es muy poco</option>
          </select>
        </div>

        <div class="form-group">
          <label>¿Tienes deudas activas?</label>
          <select name="tiene_deudas" id="tieneDeudas" required>
            <option value="0">No tengo deudas 🎉</option>
            <option value="1">Sí, tengo deudas</option>
          </select>
        </div>

        <div class="form-group" id="campoPagoDeudas" style="display: none;">
          <label>¿Cuánto pagas al mes en cuotas?</label>
          <input type="number" name="pago_mensual_deudas" step="0.01" min="0" value="0" 
                 placeholder="Ej: 300000">
        </div>
      </div>

            <!-- ============================================
           PASO 3: DINERO ACTUAL
           ============================================ -->
            <div class="paso-form" data-paso="3" style="display: none;">
                <h2 style="color: #d4af37; margin-bottom: 15px;">💰 Tu dinero hoy</h2>
                <p style="color: #9fb3c8; margin-bottom: 25px;">
                Vamos a registrar todo lo que tienes actualmente.
                </p>

                <!-- EFECTIVO -->
                <div style="background:rgba(39,174,96,0.1); border-left:4px solid #27ae60; padding:20px; border-radius:12px; margin-bottom:20px;">
                <h3 style="color:#7ee8a8; font-size:1rem; margin-bottom:15px;">💵 Efectivo en mano</h3>
                <div class="form-group">
                    <label>¿Cuánto efectivo tienes ahora?</label>
                    <input type="number" name="efectivo" step="0.01" min="0" value="0" 
                        placeholder="Ej: 200000">
                    <small style="color:#9fb3c8; font-size:0.8rem;">
                    El dinero físico que tienes contigo o guardado en casa.
                    </small>
                </div>
                </div>

                <!-- CUENTA BANCARIA -->
                <div style="background:rgba(52,152,219,0.1); border-left:4px solid #3498db; padding:20px; border-radius:12px; margin-bottom:20px;">
                <h3 style="color:#6bb6ff; font-size:1rem; margin-bottom:15px;">🏦 Cuenta bancaria o billetera digital</h3>

                <div id="camposCuenta" style="display:block;">
                    <div class="form-group">
                    <label>Nombre de tu cuenta principal</label>
                    <input type="text" name="cuenta_nombre" value="Mi cuenta principal" 
                            placeholder="Ej: Nequi, Bancolombia, Davivienda...">
                    </div>

                    <div class="form-group">
                    <label>Tipo de cuenta</label>
                    <select name="cuenta_tipo">
                        <option value="ahorros">🏦 Cuenta de ahorros</option>
                        <option value="corriente">🏛️ Cuenta corriente</option>
                        <option value="otro">📱 Billetera digital (Nequi, Daviplata)</option>
                    </select>
                    </div>

                    <div class="form-group">
                    <label>Saldo actual en esa cuenta</label>
                    <input type="number" name="cuenta_saldo" step="0.01" min="0" value="0" 
                            placeholder="Ej: 500000">
                    </div>
                </div>
                </div>

                <!-- INGRESO MENSUAL -->
                <div style="background:rgba(212,175,55,0.1); border-left:4px solid #d4af37; padding:20px; border-radius:12px;">
                <h3 style="color:#d4af37; font-size:1rem; margin-bottom:15px;">📊 Ingreso mensual</h3>
                <div class="form-group">
                    <label>¿Cuánto ganas al mes en promedio?</label>
                    <input type="number" name="ingreso_mensual" step="0.01" min="0" value="0" 
                        placeholder="Ej: 3000000">
                    <small style="color:#9fb3c8; font-size:0.8rem;">
                    Ingreso total mensual (salario + freelance + otros).
                    </small>
                </div>
                </div>
            </div>

      <!-- ============================================
           PASO 4: PRIMERA META
           ============================================ -->
      <div class="paso-form" data-paso="4" style="display: none;">
        <h2 style="color: #d4af37; margin-bottom: 15px;">🎯 Tu primera meta</h2>
        <p style="color: #9fb3c8; margin-bottom: 25px;">
          ¿Para qué quieres ahorrar? Empieza con una meta clara.
        </p>

        <div class="form-group">
          <label>Nombre de la meta</label>
          <input type="text" name="meta_nombre" value="Fondo de emergencia" required>
        </div>

        <div class="form-group">
          <label>Monto objetivo</label>
          <input type="number" name="meta_monto" step="0.01" min="1" value="1000000" required>
        </div>

        <div class="form-group">
          <label>Fecha límite</label>
          <input type="date" name="meta_fecha" 
                 value="<?= date('Y-m-d', strtotime('+6 months')) ?>" required>
        </div>

        <div class="form-group">
          <label>¿Qué tan importante es esta meta para ti?</label>
          <select name="meta_prioridad">
            <option value="3">⭐⭐⭐ Media</option>
            <option value="4">⭐⭐⭐⭐ Alta</option>
            <option value="5">⭐⭐⭐⭐⭐ Muy alta</option>
          </select>
        </div>
      </div>

      <!-- ============================================
           PASO 5: PERFIL DE RIESGO
           ============================================ -->
      <div class="paso-form" data-paso="5" style="display: none;">
        <h2 style="color: #d4af37; margin-bottom: 15px;">📊 Tu perfil de inversionista</h2>
        <p style="color: #9fb3c8; margin-bottom: 25px;">
          Responde 5 preguntas para que podamos recomendarte los mejores productos financieros.
        </p>

        <div class="form-group">
          <label>1. ¿En cuánto tiempo necesitas el dinero?</label>
          <select name="horizonte_inversion" required>
            <option value="">Selecciona...</option>
            <option value="corto">Menos de 1 año</option>
            <option value="mediano">1 a 3 años</option>
            <option value="largo">3 a 5 años</option>
            <option value="muy_largo">Más de 5 años</option>
          </select>
        </div>

        <div class="form-group">
          <label>2. Si tus inversiones bajaran 20% en un mes, ¿qué harías?</label>
          <select name="reaccion_caida" required>
            <option value="">Selecciona...</option>
            <option value="vender_todo">Vender todo para no perder más</option>
            <option value="vender_algo">Vender una parte</option>
            <option value="no_hacer_nada">No hacer nada, esperar</option>
            <option value="comprar_mas">Comprar más (aprovechar el precio bajo)</option>
          </select>
        </div>

        <div class="form-group">
          <label>3. ¿Cuál es tu objetivo principal con el dinero?</label>
          <select name="objetivo_dinero" required>
            <option value="">Selecciona...</option>
            <option value="preservar">Preservar el capital (no perder)</option>
            <option value="ingresos">Generar ingresos pasivos</option>
            <option value="crecer">Crecer moderadamente</option>
            <option value="maximizar">Maximizar ganancias</option>
          </select>
        </div>

        <div class="form-group">
          <label>4. ¿Qué % de tus ingresos puedes ahorrar mensualmente?</label>
          <select name="porcentaje_ahorro" required>
            <option value="">Selecciona...</option>
            <option value="cero">0% (no me alcanza)</option>
            <option value="bajo">1% - 10%</option>
            <option value="medio">11% - 20%</option>
            <option value="alto">Más de 20%</option>
          </select>
        </div>

        <div class="form-group">
          <label>5. ¿Has invertido antes?</label>
          <select name="experiencia_inversion" required>
            <option value="">Selecciona...</option>
            <option value="ninguna">No, nunca</option>
            <option value="cdt">Solo CDT o cuentas de ahorro</option>
            <option value="fondos">Fondos de inversión</option>
            <option value="avanzada">Acciones, cripto o derivados</option>
          </select>
        </div>
      </div>

      <!-- ============================================
           PASO 6: CATEGORÍAS
           ============================================ -->
      <div class="paso-form" data-paso="6" style="display: none;">
        <h2 style="color: #d4af37; margin-bottom: 15px;">🏷️ Categorías base</h2>
        <p style="color: #9fb3c8; margin-bottom: 25px;">
          Creamos categorías básicas para que empieces a registrar transacciones.
          Puedes editarlas después.
        </p>

        <div style="background: rgba(19,47,76,0.5); padding: 20px; border-radius: 12px;">
          <?php
          $cats = [
            'salario' => '💼 Salario',
            'comida' => '🍔 Comida',
            'transporte' => '🚗 Transporte',
            'entretenimiento' => '🎮 Entretenimiento',
            'servicios' => '💡 Servicios',
            'otros' => '📦 Otros'
          ];
          foreach ($cats as $key => $label): ?>
            <label style="display: flex; align-items: center; gap: 10px; padding: 8px 0; cursor: pointer;">
              <input type="checkbox" name="categorias[]" value="<?= $key ?>" checked style="width: auto;">
              <span style="color: #c9d6e3;"><?= $label ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Botones -->
      <div style="display: flex; gap: 15px; margin-top: 35px;">
        <button type="button" id="btnAnterior" class="btn btn-secondary" 
                style="flex: 1; display: none;" onclick="cambiarPaso(-1)">
          ← Anterior
        </button>
        <button type="button" id="btnSiguiente" class="btn btn-primary" 
                style="flex: 1;" onclick="cambiarPaso(1)">
          Siguiente →
        </button>
        <button type="submit" id="btnFinalizar" class="btn btn-primary" 
                style="flex: 1; display: none;">
          🚀 Finalizar configuración
        </button>
      </div>
    </form>
  </main>
</div>

<script>
let pasoActual = 1;
const TOTAL_PASOS = 6;

function cambiarPaso(direccion) {
  const nuevoPaso = pasoActual + direccion;
  if (nuevoPaso < 1 || nuevoPaso > TOTAL_PASOS) return;

  // Validar paso actual antes de avanzar
  if (direccion > 0 && !validarPaso(pasoActual)) return;

  document.querySelectorAll('.paso-form').forEach(el => el.style.display = 'none');
  document.querySelector('.paso-form[data-paso="' + nuevoPaso + '"]').style.display = 'block';

  document.querySelectorAll('.paso-indicador').forEach(el => {
    const paso = parseInt(el.dataset.paso);
    if (paso <= nuevoPaso) {
      el.style.background = '#d4af37';
      el.style.color = '#0a1929';
    } else {
      el.style.background = 'rgba(122,138,156,0.3)';
      el.style.color = '#9fb3c8';
    }
  });

  document.getElementById('btnAnterior').style.display = nuevoPaso > 1 ? 'block' : 'none';
  document.getElementById('btnSiguiente').style.display = nuevoPaso < TOTAL_PASOS ? 'block' : 'none';
  document.getElementById('btnFinalizar').style.display = nuevoPaso === TOTAL_PASOS ? 'block' : 'none';

  pasoActual = nuevoPaso;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validarPaso(paso) {
  const pasoForm = document.querySelector('.paso-form[data-paso="' + paso + '"]');
  const campos = pasoForm.querySelectorAll('input[required], select[required]');
  
  for (let campo of campos) {
    if (!campo.value || campo.value === '') {
      campo.focus();
      campo.style.borderColor = '#e74c3c';
      setTimeout(() => campo.style.borderColor = '', 2000);
      toast('error', 'Campo requerido', 'Completa este campo para continuar');
      return false;
    }
  }
  return true;
}

// Mostrar campo de pago de deudas solo si tiene deudas
document.getElementById('tieneDeudas').addEventListener('change', function() {
  const campo = document.getElementById('campoPagoDeudas');
  campo.style.display = this.value === '1' ? 'block' : 'none';
});

// ============================================
// NAVEGACIÓN ENTRE PASOS CON CÍRCULOS
// ============================================
document.querySelectorAll('.paso-indicador').forEach(ind => {
  ind.style.cursor = 'pointer';
  ind.addEventListener('click', function() {
    const pasoDestino = parseInt(this.dataset.paso);
    irAPaso(pasoDestino);
  });
});

function irAPaso(nuevoPaso) {
  if (nuevoPaso < 1 || nuevoPaso > TOTAL_PASOS) return;
  if (nuevoPaso === pasoActual) return;
  
  // Solo permitir avanzar si el paso actual es válido
  if (nuevoPaso > pasoActual && !validarPaso(pasoActual)) return;
  
  // Ocultar todos los pasos
  document.querySelectorAll('.paso-form').forEach(el => el.style.display = 'none');
  document.querySelector('.paso-form[data-paso="' + nuevoPaso + '"]').style.display = 'block';
  
  // Actualizar indicadores
  document.querySelectorAll('.paso-indicador').forEach(el => {
    const paso = parseInt(el.dataset.paso);
    el.classList.remove('actual', 'completado');
    
    if (paso === nuevoPaso) {
      el.style.background = '#d4af37';
      el.style.color = '#0a1929';
      el.classList.add('actual');
    } else if (paso < nuevoPaso) {
      el.style.background = 'rgba(39, 174, 96, 0.3)';
      el.style.color = '#7ee8a8';
      el.classList.add('completado');
    } else {
      el.style.background = 'rgba(122,138,156,0.3)';
      el.style.color = '#9fb3c8';
    }
  });
  
  // Actualizar botones
  document.getElementById('btnAnterior').style.display = nuevoPaso > 1 ? 'block' : 'none';
  document.getElementById('btnSiguiente').style.display = nuevoPaso < TOTAL_PASOS ? 'block' : 'none';
  document.getElementById('btnFinalizar').style.display = nuevoPaso === TOTAL_PASOS ? 'block' : 'none';
  
  pasoActual = nuevoPaso;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Actualizar cambiarPaso para usar irAPaso
function cambiarPaso(direccion) {
  irAPaso(pasoActual + direccion);
}

// ============================================
// MOSTRAR/OCULTAR CAMPOS DE CUENTA SEGÚN PASO 2
// ============================================
document.querySelector('[name="tiene_banco"]').addEventListener('change', function() {
  const camposCuenta = document.querySelectorAll('#camposCuenta input, #camposCuenta select');
  const contenedorCuenta = document.getElementById('camposCuenta');
  
  if (this.value === '1') {
    contenedorCuenta.style.display = 'block';
    // Hacer requeridos los campos
    document.querySelector('[name="cuenta_nombre"]').required = true;
    document.querySelector('[name="cuenta_saldo"]').required = true;
  } else {
    contenedorCuenta.style.display = 'none';
    // Quitar requeridos
    document.querySelector('[name="cuenta_nombre"]').required = false;
    document.querySelector('[name="cuenta_saldo"]').required = false;
  }
});

// Ejecutar al cargar para inicializar el estado
document.addEventListener('DOMContentLoaded', function() {
  const tieneBanco = document.querySelector('[name="tiene_banco"]');
  if (tieneBanco) tieneBanco.dispatchEvent(new Event('change'));
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>