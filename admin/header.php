<?php if (!defined('APP_INIT')) { http_response_code(403); exit; } ?>
<!doctype html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= e($pageTitle ?? 'Panel') ?></title>

  <!-- Iconos -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- Estilos del panel (contiene tu styles.css) -->
  <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body class="site-scope">
