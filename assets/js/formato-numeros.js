/**
 * Formateo automático de números con puntos de mil
 * 
 * Uso: Agrega la clase "formato-miles" a cualquier input numérico
 * <input type="text" class="formato-miles" name="monto">
 * 
 * El valor se envía al servidor sin puntos (solo números)
 */

(function() {
  document.addEventListener('DOMContentLoaded', function() {
    aplicarFormatoATodos();
  });

  /**
   * Aplica el formato a todos los inputs con la clase "formato-miles"
   */
  function aplicarFormatoATodos() {
    const inputs = document.querySelectorAll('input.formato-miles');
    inputs.forEach(input => {
      // Formatear el valor inicial si tiene contenido
      if (input.value) {
        input.value = formatearNumero(input.value);
      }

      // Formatear mientras escribe
      input.addEventListener('input', function(e) {
        const cursorPos = this.selectionStart;
        const valorAnterior = this.value;
        
        // Solo números
        let valorLimpio = this.value.replace(/\D/g, '');
        
        // Formatear con puntos
        const valorFormateado = valorLimpio ? 
          parseInt(valorLimpio, 10).toLocaleString('es-CO') : '';
        
        this.value = valorFormateado;
        
        // Ajustar posición del cursor
        const diferencia = valorFormateado.length - valorAnterior.length;
        const nuevaPos = Math.max(0, cursorPos + diferencia);
        this.setSelectionRange(nuevaPos, nuevaPos);
      });

      // Al perder el foco, asegurar formato
      input.addEventListener('blur', function() {
        if (this.value) {
          this.value = formatearNumero(this.value);
        }
      });

      // Al enviar el formulario, quitar los puntos
      const form = input.closest('form');
      if (form) {
        form.addEventListener('submit', function() {
          document.querySelectorAll('input.formato-miles').forEach(inp => {
            // Guardar el valor formateado en un data attribute
            inp.dataset.valorFormateado = inp.value;
            // Poner el valor limpio para enviar
            inp.value = inp.value.replace(/\./g, '');
          });
        });
      }
    });
  }

  /**
   * Formatea un número con puntos de mil
   */
  function formatearNumero(valor) {
    const limpio = String(valor).replace(/\D/g, '');
    if (!limpio) return '';
    return parseInt(limpio, 10).toLocaleString('es-CO');
  }

  // Exponer función global para uso manual
  window.formatearNumero = formatearNumero;

  // Volver a aplicar formato si se agregan inputs dinámicamente
  window.reaplicarFormatoMiles = aplicarFormatoATodos;
})();