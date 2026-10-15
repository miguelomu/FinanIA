<?php
require_once __DIR__ . '/../app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestor Financiero</title>
<link rel="stylesheet" href="/FinanIA/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
<script>
  // Aplicar tema ANTES de renderizar (evita parpadeo)
  (function() {
    const tema = localStorage.getItem('tema') || 'oscuro';
    document.documentElement.setAttribute('data-tema', tema);
  })();
</script>
</head>
<body>