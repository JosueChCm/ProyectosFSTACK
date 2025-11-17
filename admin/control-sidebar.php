<?php
// control-sidebar.php — Panel derecho opcional
if (!defined('APP_INIT')) { http_response_code(403); exit; }
?>
<aside class="tw-card" style="position:sticky;top:var(--topbar-h);height:fit-content;">
  <h3 style="margin:0 0 8px;">Atajos</h3>
  <ul class="site-list">
    <li><a href="./?m=vendedores&action=crear">+ Nuevo vendedor</a></li>
  </ul>
</aside>
