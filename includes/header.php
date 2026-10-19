<?php
require_once __DIR__ . '/../app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<title>FinanIA - Gestor Financiero</title>

<!-- PWA -->
<link rel="manifest" href="/FinanIA/manifest.json">
<meta name="theme-color" content="#d4af37">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="FinanIA">
<link rel="apple-touch-icon" href="/FinanIA/assets/img/pwa/icon-192.png">
<link rel="icon" type="image/png" href="/FinanIA/assets/img/pwa/icon-192.png">

<!-- Estilos -->
<link rel="stylesheet" href="/FinanIA/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">

<!-- Scripts -->
<script src="/FinanIA/assets/js/toast.js?v=<?= filemtime(__DIR__ . '/../assets/js/toast.js') ?>"></script>
<script src="/FinanIA/assets/js/modal.js?v=<?= filemtime(__DIR__ . '/../assets/js/modal.js') ?>"></script>
<script src="/FinanIA/assets/js/buscador.js?v=<?= filemtime(__DIR__ . '/../assets/js/buscador.js') ?>"></script>

<!-- Aplicar tema ANTES de renderizar (evita parpadeo) -->
<script>
  (function() {
    const tema = localStorage.getItem('tema') || 'oscuro';
    document.documentElement.setAttribute('data-tema', tema);
  })();
</script>

<!-- Registrar Service Worker -->
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/FinanIA/service-worker.js')
        .then(reg => console.log('✅ Service Worker registrado:', reg.scope))
        .catch(err => console.log('❌ Error SW:', err));
    });
  }
</script>

<script>
  function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btn = document.getElementById('btnMenuHamburguesa');
    
    if (sidebar) sidebar.classList.toggle('abierto');
    if (overlay) overlay.classList.toggle('activo');
    
    // Cambiar el ícono
    if (btn) {
      if (sidebar && sidebar.classList.contains('abierto')) {
        btn.textContent = '✕';
      } else {
        btn.textContent = '☰';
      }
    }
  }
  
  // Cerrar al hacer clic en un link del sidebar
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.sidebar nav a').forEach(link => {
      link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
          toggleSidebar();
        }
      });
    });
  });
</script>

</head>
<body>

<!-- Botón hamburguesa (móvil) -->
<button class="menu-hamburguesa" onclick="toggleSidebar()" id="btnMenuHamburguesa">
  ☰
</button>

<!-- Overlay para cerrar el sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>