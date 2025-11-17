<?php if (!defined('APP_INIT')) { http_response_code(403); exit; } ?>
<aside class="sidebar" id="sidebar" aria-label="Menú lateral">
  <div class="sidebar-title">
    <i class="fa-solid fa-bars-staggered"></i><span>Menú</span>
  </div>

  <nav class="menu">
    <a class="menu-item active" href="#Presentacion" data-section="Presentacion">
      <i class="fa-solid fa-house"></i><span>Presentación</span>
    </a>
    <a class="menu-item" href="#mi-perfil" data-section="mi-perfil">
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

  <footer class="sidebar-footer">
    <small>© <span id="year"></span> Mi Portafolio</small>
  </footer>
</aside>