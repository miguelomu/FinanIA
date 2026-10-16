/**
 * Búsqueda global con atajo Ctrl+K
 */

(function() {
  document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.querySelector('.buscador-input');
    const panel = document.querySelector('.buscador-resultados');
    
    if (!buscador || !panel) return;

    let timeoutId = null;
    let itemSeleccionado = -1;
    let itemsActuales = [];

    // ============================================
    // BÚSQUEDA CON DEBOUNCE
    // ============================================
    buscador.addEventListener('input', function() {
      const query = this.value.trim();
      clearTimeout(timeoutId);

      if (query.length < 2) {
        panel.classList.remove('activo');
        return;
      }

      panel.classList.add('activo');
      panel.innerHTML = '<div class="buscador-cargando">Buscando</div>';

      timeoutId = setTimeout(() => {
        buscar(query);
      }, 250);
    });

    async function buscar(query) {
      try {
        const response = await fetch('/FinanIA/api/buscar.php?q=' + encodeURIComponent(query));
        const data = await response.json();

        if (!data.success) {
          panel.innerHTML = '<div class="buscador-vacio">Error al buscar</div>';
          return;
        }

        renderResultados(data);
      } catch (e) {
        panel.innerHTML = '<div class="buscador-vacio">Error de conexión</div>';
      }
    }

    // ============================================
    // RENDERIZADO DE RESULTADOS
    // ============================================
    function renderResultados(data) {
      itemsActuales = [];
      itemSeleccionado = -1;

      if (data.total === 0) {
        panel.innerHTML = `
          <div class="buscador-vacio">
            <div class="buscador-vacio-icono">🔍</div>
            <p>No se encontraron resultados para "<strong>${escapeHtml(data.query)}</strong>"</p>
          </div>
        `;
        return;
      }

      let html = '';

      data.resultados.forEach(grupo => {
        html += `<div class="buscador-grupo">
          <div class="buscador-grupo-titulo">${grupo.categoria}</div>
        `;

        grupo.items.forEach(item => {
          const icono = iconoPorTipo(item.tipo_item);
          const valorHtml = valorPorTipo(item);

          itemsActuales.push(item);

          html += `
            <a href="${item.url}" class="buscador-item" data-index="${itemsActuales.length - 1}">
              <div class="buscador-item-icono">${icono}</div>
              <div class="buscador-item-info">
                <div class="buscador-item-titulo">${escapeHtml(item.titulo || 'Sin título')}</div>
                <div class="buscador-item-meta">${metaPorTipo(item)}</div>
              </div>
              ${valorHtml ? `<div class="buscador-item-valor">${valorHtml}</div>` : ''}
            </a>
          `;
        });

        html += `</div>`;
      });

      panel.innerHTML = html;

      // Hover para marcar como seleccionado
      panel.querySelectorAll('.buscador-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
          panel.querySelectorAll('.buscador-item').forEach(i => i.classList.remove('seleccionado'));
          this.classList.add('seleccionado');
          itemSeleccionado = parseInt(this.dataset.index);
        });
      });
    }

    // ============================================
    // NAVEGACIÓN CON FLECHAS
    // ============================================
    buscador.addEventListener('keydown', function(e) {
      const items = panel.querySelectorAll('.buscador-item');
      if (items.length === 0) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        itemSeleccionado = Math.min(itemSeleccionado + 1, items.length - 1);
        actualizarSeleccion(items);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        itemSeleccionado = Math.max(itemSeleccionado - 1, 0);
        actualizarSeleccion(items);
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (itemSeleccionado >= 0 && items[itemSeleccionado]) {
          window.location.href = items[itemSeleccionado].href;
        }
      } else if (e.key === 'Escape') {
        cerrarPanel();
      }
    });

    function actualizarSeleccion(items) {
      items.forEach((item, i) => {
        if (i === itemSeleccionado) {
          item.classList.add('seleccionado');
          item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        } else {
          item.classList.remove('seleccionado');
        }
      });
    }

    // ============================================
    // ATAJO Ctrl+K (o Cmd+K en Mac)
    // ============================================
    document.addEventListener('keydown', function(e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        buscador.focus();
        buscador.select();
      }
      
      if (e.key === 'Escape' && document.activeElement === buscador) {
        cerrarPanel();
        buscador.blur();
      }
    });

    // ============================================
    // CERRAR AL HACER CLIC FUERA
    // ============================================
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.buscador-global')) {
        cerrarPanel();
      }
    });

    function cerrarPanel() {
      panel.classList.remove('activo');
      buscador.value = '';
      itemSeleccionado = -1;
    }

    // ============================================
    // UTILIDADES
    // ============================================
    function iconoPorTipo(tipo) {
      return {
        transaccion: '💸',
        meta: '🎯',
        deuda: '💳',
        cuenta: '🏦',
        categoria: '🏷️'
      }[tipo] || '📌';
    }

    function metaPorTipo(item) {
      if (item.tipo_item === 'transaccion') {
        return item.categoria || 'Sin categoría';
      }
      if (item.tipo_item === 'meta') {
        return `Progreso: ${item.progreso}% · Vence ${formatFecha(item.fecha_limite)}`;
      }
      if (item.tipo_item === 'deuda') {
        return `Tasa ${item.tasa_interes}% · ${capitalizar(item.estado)}`;
      }
      if (item.tipo_item === 'cuenta') {
        return capitalizar(item.tipo);
      }
      if (item.tipo_item === 'categoria') {
        return capitalizar(item.tipo);
      }
      return '';
    }

    function valorPorTipo(item) {
      if (item.tipo_item === 'transaccion') {
        const signo = item.tipo === 'ingreso' ? '+' : '-';
        return `<span style="color:${item.tipo === 'ingreso' ? '#27ae60' : '#e74c3c'};">${signo}${formatCOP(item.monto)}</span>`;
      }
      if (item.tipo_item === 'cuenta') {
        return `<span style="color:#27ae60;">${formatCOP(item.saldo_actual)}</span>`;
      }
      if (item.tipo_item === 'meta') {
        return `<span style="color:#d4af37;">${item.progreso}%</span>`;
      }
      if (item.tipo_item === 'deuda') {
        return `<span style="color:#e74c3c;">${formatCOP(item.pendiente)}</span>`;
      }
      return '';
    }

    function formatCOP(valor) {
      return '$' + parseFloat(valor).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function formatFecha(fecha) {
      const d = new Date(fecha);
      return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
    }

    function capitalizar(texto) {
      return texto ? texto.charAt(0).toUpperCase() + texto.slice(1) : '';
    }

    function escapeHtml(texto) {
      const div = document.createElement('div');
      div.textContent = texto;
      return div.innerHTML;
    }
  });
})();