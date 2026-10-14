<button onclick="toggleTema()" 
        class="btn btn-secondary" 
        style="padding:8px 15px; font-size:0.85rem; cursor:pointer;"
        title="Cambiar tema"
        type="button">
  <span id="iconoTema">☀️</span>
</button>

<script>
// Actualizar icono al cargar
(function() {
  const tema = document.documentElement.getAttribute('data-tema') || 'oscuro';
  actualizarIcono(tema);
})();

function toggleTema() {
  const html = document.documentElement;
  const actual = html.getAttribute('data-tema') || 'oscuro';
  const nuevo = actual === 'oscuro' ? 'claro' : 'oscuro';
  
  html.setAttribute('data-tema', nuevo);
  localStorage.setItem('tema', nuevo);
  actualizarIcono(nuevo);
  
  // Guardar en BD (opcional)
  try {
    fetch('/FinanIA/auth/cambiar_tema.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'tema=' + nuevo
    });
  } catch(e) {}
}

function actualizarIcono(tema) {
  const icono = document.getElementById('iconoTema');
  if (icono) {
    icono.textContent = tema === 'oscuro' ? '☀️' : '🌙';
  }
}
</script>