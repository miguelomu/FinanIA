<?php
require_once __DIR__ . '/../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/dev.php';

$usuario = usuarioActual();

// Si ya es premium, redirigir
if ($usuario['rol'] === 'premium') {
    header('Location: /FinanIA/dashboards/premium.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="layout">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>⭐ Actualizar a Premium</h1>
    </div>

    <!-- Hero -->
    <div class="card" style="border:2px solid #9b59b6; background: linear-gradient(135deg, rgba(155,89,182,0.15), rgba(19,47,76,0.6)); padding:40px; text-align:center; margin-bottom:30px;">
      <div style="font-size:4rem; margin-bottom:15px;">🌟</div>
      <h1 style="color:#d5a6ff; margin-bottom:15px; font-size:2rem;">FinanIA Premium</h1>
      <p style="color:#c9d6e3; font-size:1.1rem; max-width:600px; margin:0 auto;">
        Desbloquea el poder de la inteligencia artificial en tus finanzas
      </p>
      <div style="margin-top:25px;">
        <div style="font-size:2.5rem; color:#d4af37; font-weight:bold;">$14.900</div>
        <p style="color:#9fb3c8; font-size:0.9rem;">/ mes (COP)</p>
      </div>
    </div>

    <!-- Comparativa -->
    <h2 style="color:#d4af37; margin:30px 0 20px;">¿Qué incluye Premium?</h2>
    
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:25px;">
      
      <!-- Registrado -->
      <div class="card">
        <h3 style="color:#9fb3c8; font-size:1.2rem; margin-bottom:20px;">
          🆓 REGISTRADO — Gratis
        </h3>
        <ul style="color:#c9d6e3; list-style:none; padding:0; line-height:2;">
          <li>✅ Cuentas ilimitadas</li>
          <li>✅ Transacciones ilimitadas</li>
          <li>✅ Metas y presupuestos</li>
          <li>✅ Deudas y pagos</li>
          <li>✅ Reportes básicos</li>
          <li>✅ Gastos compartidos</li>
          <li>✅ Fuentes de ingreso</li>
          <li>✅ Logros y modo oscuro</li>
          <li style="color:#7a8a9c;">❌ Asistente IA</li>
          <li style="color:#7a8a9c;">❌ Simulador de deudas</li>
          <li style="color:#7a8a9c;">❌ Simulador de inversiones</li>
        </ul>
      </div>

      <!-- Premium -->
      <div class="card" style="border:2px solid #9b59b6; background: linear-gradient(135deg, rgba(155,89,182,0.08), rgba(19,47,76,0.5));">
        <h3 style="color:#d5a6ff; font-size:1.2rem; margin-bottom:20px;">
          ⭐ PREMIUM — $14.900/mes
        </h3>
        <ul style="color:#c9d6e3; list-style:none; padding:0; line-height:2;">
          <li>✅ Todo lo de Registrado</li>
          <li>⭐ <strong>Asistente IA</strong> con análisis de tus datos</li>
          <li>⭐ <strong>Simulador de deudas</strong> (avalancha vs bola de nieve)</li>
          <li>⭐ <strong>Simulador de inversiones</strong> (rendimiento real)</li>
          <li>⭐ <strong>Configuración financiera</strong> (tasas personalizadas)</li>
          <li>⭐ Análisis predictivo</li>
          <li>⭐ Prioridad en soporte</li>
          <li>⭐ Acceso anticipado a nuevas funciones</li>
        </ul>
      </div>
    </div>

    <!-- Formulario de pago -->
    <h2 style="color:#d4af37; margin:40px 0 20px;">💳 Método de pago</h2>
    
    <div class="card" style="max-width:700px; padding:30px;">
      <div style="background:rgba(243,156,18,0.15); border-left:4px solid #f39c12; padding:15px; border-radius:8px; margin-bottom:25px;">
        <strong style="color:#f39c12;">⚠️ Modo demostración</strong>
        <p style="color:#f39c12; font-size:0.9rem; margin-top:5px;">
          Esta es una simulación. No se procesará ningún cargo real. 
          Al confirmar, tu cuenta se actualizará a Premium inmediatamente.
        </p>
      </div>

      <form action="procesar_pago.php" method="POST" autocomplete="off" id="formPago">
    <?= csrfField() ?>

    <!-- Método de pago PRIMERO -->
    <div class="form-group">
        <label>Método de pago</label>
        <select name="metodo_pago" id="metodoPago" required>
        <option value="">Selecciona un método...</option>
        <option value="tarjeta_credito">💳 Tarjeta de crédito</option>
        <option value="tarjeta_debito">💳 Tarjeta de débito</option>
        <option value="pse">🏦 PSE (Débito bancario)</option>
        <option value="nequi">📱 Nequi</option>
        <option value="daviplata">📱 Daviplata</option>
        </select>
    </div>

    <!-- Campos específicos de TARJETA -->
    <div id="camposTarjeta" style="display:none;">
        <div class="form-group">
        <label>Nombre en la tarjeta</label>
        <input type="text" name="nombre_tarjeta" 
                value="<?= e($usuario['nombre']) ?>"
                autocomplete="off"
                placeholder="Como aparece en la tarjeta">
        </div>

        <div class="form-group">
        <label>Número de tarjeta</label>
        <input type="text" 
                name="numero_tarjeta" 
                id="numeroTarjeta"
                maxlength="19"
                inputmode="numeric"
                autocomplete="off"
                placeholder="0000 0000 0000 0000">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;">
        <div class="form-group">
            <label>Mes (MM)</label>
            <input type="text" 
                name="mes_vencimiento" 
                id="mesVencimiento"
                maxlength="2"
                inputmode="numeric"
                autocomplete="off"
                placeholder="12">
        </div>

        <div class="form-group">
            <label>Año (AA)</label>
            <input type="text" 
                name="anio_vencimiento" 
                id="anioVencimiento"
                maxlength="2"
                inputmode="numeric"
                autocomplete="off"
                placeholder="28">
        </div>

        <div class="form-group">
            <label>CVV</label>
            <input type="text" 
                name="cvv" 
                id="cvv"
                maxlength="4"
                inputmode="numeric"
                autocomplete="off"
                placeholder="123">
        </div>
        </div>

        <!-- Cuotas (solo crédito) -->
        <div class="form-group" id="campoCuotas" style="display:none;">
        <label>Número de cuotas</label>
        <select name="cuotas" id="cuotas">
            <option value="1">1 cuota (sin interés) — $17.731</option>
            <option value="3">3 cuotas de $5.910</option>
            <option value="6">6 cuotas de $2.955</option>
            <option value="12">12 cuotas de $1.478</option>
        </select>
        <small style="color:#9fb3c8; font-size:0.8rem;">
            Simulación sin intereses. En producción se aplicarían tasas.
        </small>
        </div>
    </div>

    <!-- Campos específicos de PSE -->
    <div id="camposPSE" style="display:none;">
        <div class="form-group">
        <label>Banco</label>
        <select name="banco_pse">
            <option value="">Selecciona tu banco...</option>
            <option value="bancolombia">Bancolombia</option>
            <option value="davivienda">Davivienda</option>
            <option value="bogota">Banco de Bogotá</option>
            <option value="bbva">BBVA</option>
            <option value="occidente">Banco de Occidente</option>
            <option value="popular">Banco Popular</option>
            <option value="caja_social">Banco Caja Social</option>
            <option value="itau">Itaú</option>
            <option value="av_villas">Banco AV Villas</option>
            <option value="otros">Otro banco</option>
        </select>
        </div>

        <div class="form-group">
        <label>Tipo de persona</label>
        <select name="tipo_persona">
            <option value="natural">Persona natural</option>
            <option value="juridica">Persona jurídica</option>
        </select>
        </div>
    </div>

    <!-- Campos específicos de NEQUI / DAVIPLATA -->
    <div id="camposBilletera" style="display:none;">
        <div class="form-group">
        <label>Número de celular</label>
        <input type="text" 
                name="celular_billetera" 
                id="celularBilletera"
                maxlength="10"
                inputmode="numeric"
                autocomplete="off"
                placeholder="3001234567">
        <small style="color:#9fb3c8; font-size:0.8rem;">
            Recibirás una notificación en tu app para confirmar el pago.
        </small>
        </div>

        <div class="form-group">
        <label>Cédula del titular</label>
        <input type="text" 
                name="cedula_billetera" 
                maxlength="12"
                inputmode="numeric"
                autocomplete="off"
                placeholder="1234567890">
        </div>
    </div>

    <!-- Resumen del pago (siempre visible) -->
    <div style="background:rgba(19,47,76,0.5); padding:20px; border-radius:12px; margin:25px 0;">
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
        <span style="color:#9fb3c8;">Subtotal mensual:</span>
        <span style="color:#e6edf3;">$14.900</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
        <span style="color:#9fb3c8;">IVA (19%):</span>
        <span style="color:#e6edf3;">$2.831</span>
        </div>
        <div style="display:flex; justify-content:space-between; padding-top:10px; border-top:1px solid rgba(212,175,55,0.2);">
        <span style="color:#d4af37; font-weight:bold;">Total:</span>
        <span style="color:#d4af37; font-weight:bold; font-size:1.2rem;">$17.731</span>
        </div>
    </div>

    <div style="display:flex; gap:15px;">
        <button type="submit" class="btn btn-primary" style="flex:1; padding:15px; font-size:1rem;">
        🔒 Confirmar pago y actualizar
        </button>
        <a href="/FinanIA/dashboards/<?= $usuario['rol'] ?>.php" 
        class="btn btn-secondary" 
        style="flex:1; text-align:center; padding:15px;">
        Cancelar
        </a>
    </div>
    </form>

    <script>
    // ============================================
    // MOSTRAR/OCULTAR CAMPOS SEGÚN MÉTODO DE PAGO
    // ============================================
    const metodoPago = document.getElementById('metodoPago');
    const camposTarjeta = document.getElementById('camposTarjeta');
    const camposPSE = document.getElementById('camposPSE');
    const camposBilletera = document.getElementById('camposBilletera');
    const campoCuotas = document.getElementById('campoCuotas');

    function quitarRequired(contenedor) {
    contenedor.querySelectorAll('input, select, textarea').forEach(el => {
        el.required = false;
        el.removeAttribute('required');
    });
    }

    function ponerRequired(contenedor) {
    contenedor.querySelectorAll('input, select, textarea').forEach(el => {
        // No marcar required a campos opcionales
        if (el.name === 'cuotas') return;
        el.required = true;
        el.setAttribute('required', '');
    });
    }

    metodoPago.addEventListener('change', function() {
    const valor = this.value;
    
    // 1. Ocultar todos los bloques
    camposTarjeta.style.display = 'none';
    camposPSE.style.display = 'none';
    camposBilletera.style.display = 'none';
    campoCuotas.style.display = 'none';
    
    // 2. Quitar required de TODOS los bloques
    quitarRequired(camposTarjeta);
    quitarRequired(camposPSE);
    quitarRequired(camposBilletera);
    
    // 3. Mostrar el bloque correspondiente + poner required
    if (valor === 'tarjeta_credito' || valor === 'tarjeta_debito') {
        camposTarjeta.style.display = 'block';
        ponerRequired(camposTarjeta);
        
        if (valor === 'tarjeta_credito') {
        campoCuotas.style.display = 'block';
        }
    } else if (valor === 'pse') {
        camposPSE.style.display = 'block';
        ponerRequired(camposPSE);
    } else if (valor === 'nequi' || valor === 'daviplata') {
        camposBilletera.style.display = 'block';
        ponerRequired(camposBilletera);
    }
    });

    // ============================================
    // FORMATEO DE NÚMERO DE TARJETA
    // ============================================
    const numeroTarjetaInput = document.getElementById('numeroTarjeta');
    if (numeroTarjetaInput) {
    numeroTarjetaInput.addEventListener('input', function(e) {
        let valor = e.target.value.replace(/\D/g, '');
        valor = valor.substring(0, 16);
        const formateado = valor.replace(/(\d{4})(?=\d)/g, '$1 ');
        
        const cursorPos = e.target.selectionStart;
        const longitudAntes = e.target.value.length;
        e.target.value = formateado;
        const longitudDespues = formateado.length;
        const delta = longitudDespues - longitudAntes;
        e.target.setSelectionRange(cursorPos + delta, cursorPos + delta);
    });
    }

    // ============================================
    // VALIDACIÓN DE MES (01-12)
    // ============================================
    const mesInput = document.getElementById('mesVencimiento');
    if (mesInput) {
    mesInput.addEventListener('input', function(e) {
        let valor = e.target.value.replace(/\D/g, '').substring(0, 2);
        if (valor.length === 2) {
        let mes = parseInt(valor);
        if (mes < 1) valor = '01';
        else if (mes > 12) valor = '12';
        }
        e.target.value = valor;
    });
    }

    // ============================================
    // SOLO NÚMEROS
    // ============================================
    ['anioVencimiento', 'cvv', 'celularBilletera'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        const maxLen = id === 'cvv' ? 4 : (id === 'celularBilletera' ? 10 : 2);
        el.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, maxLen);
        });
    }
    });

    // ============================================
    // VALIDACIÓN AL ENVIAR
    // ============================================
    document.getElementById('formPago').addEventListener('submit', function(e) {
    const metodo = metodoPago.value;
    
    if (!metodo) {
        e.preventDefault();
        alert('⚠️ Selecciona un método de pago.');
        return false;
    }
    
    if (metodo === 'tarjeta_credito' || metodo === 'tarjeta_debito') {
        const numero = numeroTarjetaInput.value.replace(/\s/g, '');
        const mes = mesInput.value;
        const anio = document.getElementById('anioVencimiento').value;
        const cvv = document.getElementById('cvv').value;
        
        if (numero.length !== 16) {
        e.preventDefault();
        alert('⚠️ El número de tarjeta debe tener 16 dígitos.');
        return false;
        }
        
        const mesNum = parseInt(mes);
        if (!mes || mesNum < 1 || mesNum > 12) {
        e.preventDefault();
        alert('⚠️ El mes debe estar entre 01 y 12.');
        return false;
        }
        
        const anioActual = new Date().getFullYear() % 100;
        const anioNum = parseInt(anio);
        if (!anio || anioNum < anioActual || anioNum > anioActual + 10) {
        e.preventDefault();
        alert('⚠️ Año inválido. Debe estar entre ' + anioActual + ' y ' + (anioActual + 10) + '.');
        return false;
        }
        
        const fechaTarjeta = new Date(2000 + anioNum, mesNum - 1, 1);
        const hoy = new Date();
        hoy.setDate(1);
        hoy.setHours(0, 0, 0, 0);
        if (fechaTarjeta < hoy) {
        e.preventDefault();
        alert('⚠️ Tu tarjeta está vencida.');
        return false;
        }
        
        if (cvv.length < 3) {
        e.preventDefault();
        alert('⚠️ El CVV debe tener 3 o 4 dígitos.');
        return false;
        }
    }
    
    if (metodo === 'nequi' || metodo === 'daviplata') {
        const celular = document.getElementById('celularBilletera').value;
        const cedula = document.getElementById('cedula_billetera')?.value || document.querySelector('[name="cedula_billetera"]').value;
        
        if (celular.length !== 10) {
        e.preventDefault();
        alert('⚠️ El celular debe tener 10 dígitos.');
        return false;
        }
        if (cedula.length < 6) {
        e.preventDefault();
        alert('⚠️ Ingresa una cédula válida.');
        return false;
        }
    }
    
    if (metodo === 'pse') {
        const banco = document.querySelector('[name="banco_pse"]').value;
        if (!banco) {
        e.preventDefault();
        alert('⚠️ Selecciona tu banco.');
        return false;
        }
    }
    });
    </script>

      <p style="color:#7a8a9c; font-size:0.75rem; text-align:center; margin-top:20px;">
        🔒 Conexión segura SSL · Tus datos están encriptados
      </p>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>