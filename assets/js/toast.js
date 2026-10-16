/**
 * Sistema de notificaciones Toast
 * 
 * Uso:
 *   toast('success', 'Éxito', 'La operación se completó');
 *   toast('error', 'Error', 'Algo salió mal');
 *   toast('warning', 'Atención', 'Cuidado con esto');
 *   toast('info', 'Info', 'Dato importante');
 */

(function() {
  // Crear el contenedor al cargar
  document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('.toast-container')) {
      const container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
  });

  /**
   * Muestra un toast
   * 
   * @param {string} tipo - 'success', 'error', 'warning', 'info'
   * @param {string} titulo - Título del toast
   * @param {string} mensaje - Mensaje opcional
   * @param {number} duracion - Duración en ms (default 4000)
   */
  window.toast = function(tipo, titulo, mensaje = '', duracion = 4000) {
    const container = document.querySelector('.toast-container');
    if (!container) return;

    const iconos = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };

    const toast = document.createElement('div');
    toast.className = 'toast ' + tipo;
    
    toast.innerHTML = `
        <div class="toast-icon">${iconos[tipo] || 'ℹ️'}</div>
        <div class="toast-content">
        <div class="toast-title">${escapeHtml(titulo)}</div>
        ${mensaje ? `<div class="toast-message">${escapeHtml(mensaje)}</div>` : ''}
        </div>
        <button class="toast-close" onclick="cerrarToast(this)">×</button>
        <div class="toast-progress" style="animation-duration: ${duracion}ms;"></div>
    `;

    container.appendChild(toast);

    // Sistema de timer pausable
    let tiempoRestante = duracion;
    let tiempoInicio = Date.now();
    let timer = null;
    let pausado = false;

    function iniciarTimer() {
        tiempoInicio = Date.now();
        timer = setTimeout(() => {
        cerrarToast(toast.querySelector('.toast-close'));
        }, tiempoRestante);
    }

    function pausarTimer() {
        if (pausado) return;
        pausado = true;
        clearTimeout(timer);
        tiempoRestante -= (Date.now() - tiempoInicio);
        if (tiempoRestante < 0) tiempoRestante = 0;
    }

    function reanudarTimer() {
        if (!pausado) return;
        pausado = false;
        if (tiempoRestante <= 0) {
        cerrarToast(toast.querySelector('.toast-close'));
        return;
        }
        iniciarTimer();
    }

    iniciarTimer();

    // Pausar/reanudar al hacer hover
    toast.addEventListener('mouseenter', () => {
        pausarTimer();
        const progress = toast.querySelector('.toast-progress');
        if (progress) progress.style.animationPlayState = 'paused';
    });

    toast.addEventListener('mouseleave', () => {
        reanudarTimer();
        const progress = toast.querySelector('.toast-progress');
        if (progress) {
        // Reiniciar la animación CSS con el tiempo restante
        progress.style.animation = 'none';
        progress.offsetHeight; // forzar reflow
        progress.style.animation = `toastProgress ${tiempoRestante}ms linear forwards`;
        progress.style.animationPlayState = 'running';
        }
    });
    };

  /**
   * Cierra un toast
   */
  window.cerrarToast = function(boton) {
    const toast = boton.closest ? boton.closest('.toast') : boton;
    if (!toast) return;
    
    toast.classList.add('saliendo');
    setTimeout(() => {
      toast.remove();
    }, 300);
  };

  /**
   * Escapa HTML para prevenir XSS
   */
  function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
  }
})();