/**
 * Modal de confirmación personalizado
 * 
 * Uso:
 *   <a href="eliminar.php?id=5" onclick="return confirmar(event, {
 *     titulo: '¿Eliminar transacción?',
 *     mensaje: 'Esta acción no se puede deshacer.',
 *     tipo: 'danger',
 *     confirmar: 'Eliminar'
 *   })">Eliminar</a>
 * 
 * O simplificado:
 *   <a href="..." onclick="return confirmar(event)">Eliminar</a>
 */

(function() {
  document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('.modal-overlay')) {
      crearModalBase();
    }
  });

  function crearModalBase() {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.id = 'modalConfirm';
    modal.innerHTML = `
      <div class="modal-confirm" id="modalConfirmContent">
        <div class="modal-icon warning" id="modalIcon">⚠️</div>
        <h3 class="modal-titulo" id="modalTitulo">¿Estás seguro?</h3>
        <p class="modal-mensaje" id="modalMensaje">Esta acción no se puede deshacer.</p>
        <div class="modal-botones">
          <button class="btn btn-secondary" onclick="cerrarModal(false)">Cancelar</button>
          <button class="btn btn-danger" id="modalBtnConfirmar" onclick="cerrarModal(true)">Confirmar</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
  }

  let callbackActual = null;

  /**
   * Muestra el modal de confirmación
   * 
   * @param {Event} event - Evento del clic (para prevenir el default)
   * @param {Object} opciones - Configuración del modal
   */
  window.confirmar = function(event, opciones = {}) {
    event.preventDefault();
    
    const config = {
      titulo: opciones.titulo || '¿Estás seguro?',
      mensaje: opciones.mensaje || 'Esta acción no se puede deshacer.',
      tipo: opciones.tipo || 'danger',
      icono: opciones.icono || (opciones.tipo === 'danger' ? '🗑️' : '⚠️'),
      confirmar: opciones.confirmar || 'Confirmar',
      href: event.currentTarget.href || null
    };

    const modal = document.getElementById('modalConfirm');
    const icono = document.getElementById('modalIcon');
    const titulo = document.getElementById('modalTitulo');
    const mensaje = document.getElementById('modalMensaje');
    const btnConfirmar = document.getElementById('modalBtnConfirmar');

    icono.textContent = config.icono;
    icono.className = 'modal-icon ' + config.tipo;
    titulo.textContent = config.titulo;
    mensaje.textContent = config.mensaje;
    btnConfirmar.textContent = config.confirmar;

    callbackActual = function() {
      if (config.href) {
        window.location.href = config.href;
      }
    };

    modal.classList.add('activo');

    return false;
  };

  /**
   * Cierra el modal
   */
  window.cerrarModal = function(confirmado) {
    const modal = document.getElementById('modalConfirm');
    if (!modal) return;
    
    modal.classList.remove('activo');
    
    if (confirmado && callbackActual) {
      callbackActual();
    }
    
    callbackActual = null;
  };

  // Cerrar al hacer clic en el overlay (fuera del modal)
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
      cerrarModal(false);
    }
  });

  // Cerrar con ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      cerrarModal(false);
    }
  });
})();