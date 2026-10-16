<?php include __DIR__ . '/../../../includes/header.php'; ?>

<style>
  .drop-zone {
    border: 3px dashed rgba(212, 175, 55, 0.4);
    border-radius: 20px;
    padding: 60px 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: rgba(19, 47, 76, 0.3);
  }
  
  .drop-zone:hover,
  .drop-zone.dragover {
    border-color: #d4af37;
    background: rgba(212, 175, 55, 0.1);
    transform: scale(1.01);
  }
  
  .drop-zone-icono {
    font-size: 4rem;
    margin-bottom: 15px;
    opacity: 0.7;
  }
  
  .drop-zone-titulo {
    color: #d4af37;
    font-size: 1.3rem;
    margin-bottom: 10px;
  }
  
  .drop-zone-subtitulo {
    color: #9fb3c8;
    font-size: 0.9rem;
  }
  
  .preview-tabla {
    max-height: 400px;
    overflow-y: auto;
    border-radius: 12px;
  }
  
  .preview-tabla table {
    font-size: 0.85rem;
  }
  
  .paso-importar {
    background: rgba(19, 47, 76, 0.5);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 25px;
  }
  
  .paso-numero {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #d4af37, #b8860b);
    color: #0a1929;
    font-weight: bold;
    margin-right: 12px;
  }
</style>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>📂 Importar extracto bancario</h1>
      <a href="/FinanIA/modulos/transacciones/index.php" class="btn btn-secondary">← Volver a transacciones</a>
    </div>

    <!-- Hero -->
    <div class="card" style="border:2px solid #3498db; background:linear-gradient(135deg, rgba(52,152,219,0.15), rgba(19,47,76,0.6)); padding:30px; margin-bottom:30px;">
      <h2 style="color:#6bb6ff; margin-bottom:15px;">💡 Importa cientos de transacciones en segundos</h2>
      <p style="color:#c9d6e3; line-height:1.7;">
        Descarga el extracto CSV de tu banco (Bancolombia, Davivienda, Nequi, Nu) y súbelo aquí. 
        El sistema detectará automáticamente las columnas y categorizará cada transacción.
      </p>
    </div>

    <!-- PASO 1: SUBIR ARCHIVO -->
    <div class="paso-importar" id="paso1">
      <h3 style="color:#d4af37; margin-bottom:20px;">
        <span class="paso-numero">1</span> Selecciona el archivo CSV
      </h3>

      <div class="drop-zone" id="dropZone">
        <div class="drop-zone-icono">📁</div>
        <div class="drop-zone-titulo">Arrastra tu archivo aquí</div>
        <div class="drop-zone-subtitulo">o haz clic para seleccionar</div>
        <input type="file" id="archivoInput" accept=".csv,.txt" style="display:none;">
      </div>

      <div style="margin-top:20px; display:flex; gap:15px; flex-wrap:wrap;">
        <a href="plantilla.php" class="btn btn-secondary" style="font-size:0.85rem;">
          📥 Descargar plantilla de ejemplo
        </a>
      </div>
    </div>

    <!-- PASO 2: PREVIEW -->
    <div class="paso-importar" id="paso2" style="display:none;">
      <h3 style="color:#d4af37; margin-bottom:20px;">
        <span class="paso-numero">2</span> Revisa y confirma
      </h3>

      <div style="background:rgba(39,174,96,0.15); border-left:4px solid #27ae60; padding:15px; border-radius:8px; margin-bottom:20px;">
        <strong style="color:#7ee8a8;" id="infoArchivo"></strong>
      </div>

      <h4 style="color:#c9d6e3; margin-bottom:12px; font-size:0.95rem;">
        📋 Previsualización (primeras 20 filas)
      </h4>
      <div class="table-container preview-tabla" id="previewTabla"></div>

      <h4 style="color:#c9d6e3; margin:25px 0 12px 0; font-size:0.95rem;">
        🎯 Cuenta destino
      </h4>
      <div class="form-group">
        <select id="cuentaDestino" required>
          <option value="">Selecciona la cuenta...</option>
          <?php foreach ($cuentas as $c): ?>
            <option value="<?= $c['id_cuenta'] ?>"><?= e($c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
        <small style="color:#9fb3c8; font-size:0.8rem;">
          Todas las transacciones se asociarán a esta cuenta.
        </small>
      </div>

      <div style="margin-top:25px; display:flex; gap:15px;">
        <button class="btn btn-primary" id="btnImportar" style="flex:1;" onclick="importar()">
          ✅ Importar transacciones
        </button>
        <button class="btn btn-secondary" onclick="resetear()">
          Cancelar
        </button>
      </div>
    </div>

    <!-- PASO 3: RESULTADO -->
    <div class="paso-importar" id="paso3" style="display:none;">
      <h3 style="color:#27ae60; margin-bottom:20px;">
        <span class="paso-numero" style="background:linear-gradient(135deg,#27ae60,#145a32);color:#fff;">✓</span>
        Importación completada
      </h3>

      <div id="resultadoImportacion"></div>

      <div style="margin-top:25px; display:flex; gap:15px;">
        <a href="/FinanIA/modulos/transacciones/index.php" class="btn btn-primary" style="flex:1;">
          Ver mis transacciones
        </a>
        <button class="btn btn-secondary" onclick="resetear()">
          Importar otro archivo
        </button>
      </div>
    </div>

  </main>
</div>

<script>
const csrfToken = '<?= csrfToken() ?>';
let archivoTemporal = null;
let mapeoDetectado = null;

// ============================================
// DROP ZONE
// ============================================
const dropZone = document.getElementById('dropZone');
const archivoInput = document.getElementById('archivoInput');

dropZone.addEventListener('click', () => archivoInput.click());

dropZone.addEventListener('dragover', (e) => {
  e.preventDefault();
  dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
  dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
  e.preventDefault();
  dropZone.classList.remove('dragover');
  const archivos = e.dataTransfer.files;
  if (archivos.length > 0) {
    archivoInput.files = archivos;
    subirArchivo();
  }
});

archivoInput.addEventListener('change', subirArchivo);

// ============================================
// SUBIR Y PREVISUALIZAR
// ============================================
async function subirArchivo() {
  const archivo = archivoInput.files[0];
  if (!archivo) return;

  const formData = new FormData();
  formData.append('archivo', archivo);
  formData.append('csrf_token', csrfToken);

  dropZone.innerHTML = '<div class="drop-zone-icono">⏳</div><div class="drop-zone-titulo">Procesando...</div>';

  try {
    const response = await fetch('index.php?accion=preview', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.error) {
      toast('error', 'Error', data.error);
      resetear();
      return;
    }

    archivoTemporal = data.archivo_temporal;
    mapeoDetectado = data.mapeo;

    mostrarPreview(data);
  } catch (e) {
    toast('error', 'Error', 'No se pudo procesar el archivo');
    resetear();
  }
}

function mostrarPreview(data) {
  document.getElementById('paso1').style.display = 'none';
  document.getElementById('paso2').style.display = 'block';

  document.getElementById('infoArchivo').textContent = 
    `✅ Detectadas ${data.total} transacciones. Delimitador: "${data.delimitador || ','}"`;

  // Construir tabla de preview
  let html = '<table><thead><tr>';
  data.columnas.forEach(col => {
    const esFecha = col === data.mapeo.fecha;
    const esMonto = col === data.mapeo.monto;
    const esDesc = col === data.mapeo.descripcion;
    const icono = esFecha ? ' 📅' : esMonto ? ' 💰' : esDesc ? ' 📝' : '';
    const color = esFecha ? '#3498db' : esMonto ? '#27ae60' : esDesc ? '#d4af37' : '#9fb3c8';
    html += `<th style="color:${color};">${escapeHtml(col)}${icono}</th>`;
  });
  html += '</tr></thead><tbody>';

  data.preview.forEach(fila => {
    html += '<tr>';
    data.columnas.forEach(col => {
      html += `<td>${escapeHtml(fila[col] || '')}</td>`;
    });
    html += '</tr>';
  });
  html += '</tbody></table>';

  document.getElementById('previewTabla').innerHTML = html;
}

// ============================================
// IMPORTAR
// ============================================
async function importar() {
  const idCuenta = document.getElementById('cuentaDestino').value;

  if (!idCuenta) {
    toast('warning', 'Selecciona una cuenta', 'Debes elegir a qué cuenta asociar las transacciones');
    return;
  }

  const formData = new FormData();
  formData.append('archivo_temporal', archivoTemporal);
  formData.append('id_cuenta', idCuenta);
  formData.append('csrf_token', csrfToken);

  // Enviar el mapeo
  Object.keys(mapeoDetectado).forEach(key => {
    formData.append(`mapeo[${key}]`, mapeoDetectado[key]);
  });

  document.getElementById('btnImportar').textContent = '⏳ Importando...';
  document.getElementById('btnImportar').disabled = true;

  try {
    const response = await fetch('index.php?accion=importar', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.error) {
      toast('error', 'Error', data.error);
      document.getElementById('btnImportar').textContent = '✅ Importar transacciones';
      document.getElementById('btnImportar').disabled = false;
      return;
    }

    mostrarResultado(data);
  } catch (e) {
    toast('error', 'Error', 'No se pudo importar');
    document.getElementById('btnImportar').textContent = '✅ Importar transacciones';
    document.getElementById('btnImportar').disabled = false;
  }
}

function mostrarResultado(data) {
  document.getElementById('paso2').style.display = 'none';
  document.getElementById('paso3').style.display = 'block';

  let html = `
    <div style="background:rgba(39,174,96,0.15); border-left:4px solid #27ae60; padding:20px; border-radius:12px; margin-bottom:15px;">
      <div style="font-size:2.5rem; margin-bottom:10px;">🎉</div>
      <strong style="color:#7ee8a8; font-size:1.2rem;">${data.importadas} transacciones importadas</strong>
      <p style="color:#c9d6e3; margin-top:8px;">
        Se asociaron a tu cuenta correctamente.
      </p>
    </div>
  `;

  if (data.errores && data.errores.length > 0) {
    html += `
      <div style="background:rgba(243,156,18,0.15); border-left:4px solid #f39c12; padding:20px; border-radius:12px;">
        <strong style="color:#f39c12;">⚠️ ${data.errores.length} filas con errores</strong>
        <ul style="color:#c9d6e3; margin-top:10px; padding-left:25px; font-size:0.9rem;">
          ${data.errores.slice(0, 5).map(e => `<li>${escapeHtml(e)}</li>`).join('')}
          ${data.errores.length > 5 ? `<li>... y ${data.errores.length - 5} más</li>` : ''}
        </ul>
      </div>
    `;
  }

  document.getElementById('resultadoImportacion').innerHTML = html;

  toast('success', '¡Importación exitosa!', `${data.importadas} transacciones agregadas`);
}

function resetear() {
  document.getElementById('paso1').style.display = 'block';
  document.getElementById('paso2').style.display = 'none';
  document.getElementById('paso3').style.display = 'none';
  document.getElementById('archivoInput').value = '';
  dropZone.innerHTML = `
    <div class="drop-zone-icono">📁</div>
    <div class="drop-zone-titulo">Arrastra tu archivo aquí</div>
    <div class="drop-zone-subtitulo">o haz clic para seleccionar</div>
  `;
  archivoTemporal = null;
  mapeoDetectado = null;
}

function escapeHtml(texto) {
  const div = document.createElement('div');
  div.textContent = texto || '';
  return div.innerHTML;
}
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>