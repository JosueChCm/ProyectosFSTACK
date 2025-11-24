<?php if (!defined('APP_INIT')) { http_response_code(403); exit; } ?>

<?php
// Avatar por defecto
$DEFAULT_AVATAR = '../uploads/avatars/default.webp';

// 1) Avatar en sesión
$currentAvatar = $_SESSION['avatar_url'] ?? null;

// 2) Intentar buscarlo una vez en BD si no existe en sesión
if (!$currentAvatar && !empty($_SESSION['user_id'])) {
    if (isset($pdo)) {
        $stmt = $pdo->prepare('SELECT avatar_url FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $dbAvatar = $stmt->fetchColumn();
        if ($dbAvatar) {
            $currentAvatar = $dbAvatar;
            $_SESSION['avatar_url'] = $dbAvatar;
        }
    }
}

// 3) Escoger avatar final
$avatarToShow = $currentAvatar ?: $DEFAULT_AVATAR;

// Sanitizar nombre
if (!function_exists('e')) {
    function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$usernameToShow = e($_SESSION['username'] ?? 'usuario');
?>
<aside class="sidebar" id="sidebar" aria-label="Menú lateral">
  <div class="sidebar-title">
    <i class="fa-solid fa-bars-staggered"></i><span>Menú</span>
  </div>

 <!-- BLOQUE DE PERFIL -->
  <div class="sb-profile">
    <img src="<?= e($avatarToShow) ?>"
         alt="Avatar"
         class="sb-avatar">

    <div class="sb-name">
      <?= $usernameToShow ?>
    </div>
  </div>

<div class="sb-nav">
  <nav class="menu">
    <a class="menu-item <?= ($module==='home' && $action==='index') ? 'active' : '' ?>" href="./?m=home&action=index">
      <i class="fa-solid fa-house"></i><span>Presentación</span>
    </a>
    
    <a class="menu-item <?= ($module==='perfil' && $action==='perfil') ? 'active' : '' ?>" href="./?m=perfil&action=perfil">
      <i class="fa-solid fa-user"></i><span>Mi Perfil</span>
    </a>


    <!-- Grupo: Vendedores -->
<button
  class="menu-item menu-toggle"
  id="vendorsToggle"
  aria-expanded="<?= ($module==='vendedores') ? 'true' : 'false' ?>"
  aria-controls="vendorsMenu"
>
  <i class="fa-solid fa-users"></i>
  <span>Vendedores</span> 
  <i class="fa-solid fa-chevron-down chevron"></i>
</button>
<div
  class="submenu <?= ($module==='vendedores') ? 'open' : '' ?>"
  id="vendorsMenu"
  <?= ($module==='vendedores') ? '' : 'hidden' ?>
>
  <a class="menu-item <?= ($module==='vendedores' && ($action ?? '')==='lista') ? 'active' : '' ?>" href="./?m=vendedores&action=lista">
    <i class="fa-solid fa-list"></i> Lista de vendedores
  </a>
  <a class="menu-item <?= ($module==='vendedores' && ($action ?? '')==='agregar') ? 'active' : '' ?>" href="./?m=vendedores&action=agregar">
    <i class="fa-solid fa-user-plus"></i> Agregar vendedor
  </a>
  <a class="menu-item <?= ($module==='vendedores' && ($action ?? '')==='reportes') ? 'active' : '' ?>" href="./?m=vendedores&action=reportes">
    <i class="fa-solid fa-chart-line"></i> Reportes
  </a>
</div>


    <button class="menu-item menu-toggle" id="projToggle" aria-expanded="false" aria-controls="projMenu">
      <i class="fa-solid fa-folder-open"></i>
      <span>Proyectos</span>
      <i class="fa-solid fa-chevron-down chevron"></i>
    </button>
    <div class="submenu" id="projMenu" hidden>
      <a class="menu-item submenu-item" href="#Presentacion">
        <i class="fa-solid fa-layer-group"></i><span>Maquetación</span>
      </a>
      <a class="menu-item submenu-item code-link" href="#mi-perfil">
        <span class="code-icon">&lt;/&gt;</span><span>CSS</span>
      </a>
      <a class="menu-item submenu-item" href="#punto4">
        <i class="fa-brands fa-js"></i><span>JavaScript</span>
      </a>
    </div>

    <!-- Grupo: Backend → abre en nueva pestaña/ventana -->
    <button class="menu-item menu-toggle" id="backendToggle" aria-expanded="false" aria-controls="backendMenu">
      <i class="fa-solid fa-database"></i>
      <span>Backend</span>
      <i class="fa-solid fa-chevron-down chevron"></i>
    </button>
    <div class="submenu" id="backendMenu" hidden>
      <a class="menu-item" href="../apps/ventas/lectura.php?skin=bs" target="_blank" rel="noopener">
        <i class="fa-solid fa-table"></i> Lectura
      </a>
      <a class="menu-item" href="../apps/ventas/insertar.php?skin=bs" target="_blank" rel="noopener">
        <i class="fa-solid fa-circle-plus"></i> Insertar
      </a>
    </div>

    <a class="menu-item" href="#contactame" data-section="contactame">
      <i class="fa-solid fa-envelope"></i><span>Contáctame</span>
    </a>
    <a class="menu-item" href="#punto4" data-section="punto4">
      <i class="fa-solid fa-list-check"></i><span>Punto 4</span>
    </a>
  </nav>
</div>
  <footer class="sidebar-footer">
    <small>© <span id="year"></span> Mi Portafolio</small>
  </footer>
</aside>