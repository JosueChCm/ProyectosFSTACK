<?php if (!defined('APP_INIT')) { http_response_code(403); exit; } ?>
<?php
// Avatar por defecto (ajusta la ruta según tu estructura pública)
$DEFAULT_AVATAR = '../uploads/avatars/default.webp';

// 1) Toma el avatar de la sesión si existe, si no NULL
$currentAvatar = $_SESSION['avatar_url'] ?? null;

// 2) Si no hay en sesión, intenta traerlo una vez desde BD
if (!$currentAvatar && !empty($_SESSION['user_id'])) {
    // Asegura que $pdo exista en este contexto
    if (isset($pdo)) {
        $stmt = $pdo->prepare('SELECT avatar_url FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $dbAvatar = $stmt->fetchColumn();
        if ($dbAvatar) {
            $currentAvatar = $dbAvatar;
            $_SESSION['avatar_url'] = $dbAvatar; // cache en sesión
        }
    }
}

// 3) Decide qué mostrar (fallback al default)
$avatarToShow = $currentAvatar ?: $DEFAULT_AVATAR;

// (opcional) función e() si no existe
if (!function_exists('e')) {
    function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
?>


<header class="topbar">
  <button class="hamburger" id="btnSidebar" aria-label="Abrir menú" aria-expanded="false">
    <i class="fa-solid fa-bars"></i>
  </button>

  <h1 class="brand">Bienvenido, <span><?= e($_SESSION['username'] ?? 'usuario') ?></span></h1>

  <div class="topbar-right">
    <nav class="quick-links fx-borderfill" aria-label="Accesos rápidos">
      <a href="#Presentacion"><i class="fa-solid fa-house"></i> INICIO</a>
      <a href="#mi-perfil" class="code-link"><span class="code-icon">&lt;/&gt;</span> PERFIL</a>
      <a href="#contactame" class="code-link"><span class="code-icon">&lt;/&gt;</span> CONTÁCTAME</a>
      <a href="#punto4"><i class="fa-brands fa-js"></i> PUNTO 4</a>
    </nav>

    <button id="themeToggle" class="theme-toggle" aria-label="Cambiar tema" title="Cambiar tema">
      <i class="fa-solid fa-moon"></i>
    </button>

    <!-- User popover (idéntico a tu index) -->
      <div class="userbox">
    <button id="userboxTrigger" class="userbox-trigger" aria-haspopup="dialog" aria-expanded="false">
      <i class="fa-regular fa-user"></i>
      <span><?= e($_SESSION['username'] ?? 'usuario') ?></span>
      <i class="fa-solid fa-chevron-down chevron"></i>
    </button>
    <template id="userboxDropdown">
      <div class="userbox-card">
        <div class="userbox-header">
          <!-- ✅ usar avatar del usuario o el default -->
          <img class="userbox-avatar" src="<?= e($avatarToShow) ?>" alt="Avatar">
          <div class="userbox-id">
            <div class="userbox-id__name"><?= e($_SESSION['username'] ?? 'usuario') ?></div>
            <small class="muted">Sesión activa</small>
          </div>
        </div>
        <div class="userbox-actions">
          <a class="userbox-btn" href="./?m=perfil&action=perfil">
            <i class="fa-solid fa-user-pen"></i>
            Ver perfil
          </a>
          <a class="userbox-btn" href="../logout.php">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Cerrar sesión
          </a>
        </div>
      </div>
    </template>
  </div>
  </div>
</header>