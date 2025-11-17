<?php
declare(strict_types=1);
define('APP_INIT', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$username = $_SESSION['username'] ?? 'usuario';

// Router
$module = isset($_GET['m']) ? preg_replace('/[^a-z0-9_\-]/i','', $_GET['m']) : 'home';
$action = isset($_GET['action']) ? preg_replace('/[^a-z0-9_\-]/i','', $_GET['action']) : 'index';
$pageTitle = 'Panel — ' . ucfirst($module);

// Lista blanca
$allowed = [
  'home'   => ['index'],
  'ventas' => ['insertar', 'lectura'], // <<--- habilitamos tus páginas
    'vendedores'  => ['lista','agregar','reportes'], // ← nuevo
    'proyectos'  => ['lista','agregar','reportes'], // ← nuevo
  'perfil'     => ['perfil','perfil_editar'],
];


$moduleFile  = realpath(__DIR__ . "/../modules/{$module}/{$action}.php");
$modulesRoot = realpath(__DIR__ . '/../modules');
if (!$moduleFile || !str_starts_with($moduleFile, $modulesRoot)) {
  http_response_code(404);
  exit('Módulo no encontrado');
}

if (!isset($allowed[$module]) || !in_array($action, $allowed[$module], true)) {
  $module = 'home'; $action = 'index';
}

$moduleFile  = realpath(__DIR__ . "/../modules/{$module}/{$action}.php");
$modulesRoot = realpath(__DIR__ . '/../modules');
if (!$moduleFile || !str_starts_with($moduleFile, $modulesRoot)) {
  http_response_code(404);
  exit('Módulo no encontrado');
}

require __DIR__ . '/header.php';
require __DIR__ . '/navbar.php';
?>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>

  <main class="content">
    <?php require $moduleFile; ?>
  </main>
</div>

<?php require __DIR__ . '/footer.php';