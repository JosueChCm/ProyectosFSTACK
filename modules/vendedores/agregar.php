<?php
if (!defined('APP_INIT')) { http_response_code(403); exit; }
require_once __DIR__ . '/../../includes/config.php';

// ====== Manejo POST robusto: siempre JSON y sin HTML colado ======
function start_json_response() {
  // limpia buffers para evitar que se cuele HTML/espacios antes del JSON
  while (ob_get_level() > 0) { ob_end_clean(); }
  header('Content-Type: application/json; charset=utf-8');
}

$xhr      = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
$ajaxFlag = (($_POST['__ajax'] ?? '') === '1');
$acceptJs = (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
$isAjax   = $xhr || $ajaxFlag || $acceptJs;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  start_json_response();

  // si usas auth y la sesion no está, devuelve JSON (no HTML)
  // $loggedIn = isset($_SESSION['usuario']); // <-- ajusta a tu lógica
  // if (!$loggedIn) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Sesión vencida']); exit; }

  if (!$isAjax) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'POST no reconocido como AJAX']); exit;
  }

  try{
    // Leer datos
    $idRaw     = trim($_POST['id'] ?? '');
    $vendedor  = trim($_POST['vendedor'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $fecha     = trim($_POST['fechaventa'] ?? '');

    // Validación básica
    if ($vendedor==='' || $direccion==='' || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha)) {
      throw new RuntimeException('Completa vendedor, dirección y fecha válida (YYYY-MM-DD).');
    }

    // Si el usuario ingresó ID, debe ser entero positivo
    $useId = ($idRaw !== '');
    if ($useId) {
      if (!ctype_digit($idRaw) || (int)$idRaw <= 0) {
        throw new RuntimeException('El ID debe ser un número entero positivo.');
      }
      $id = (int)$idRaw;
    }

    // Insert con o sin ID explícito
    if ($useId) {
      $st = $pdo->prepare('INSERT INTO ventas (id, vendedor, direccion, fechaventa) VALUES (?, ?, ?, ?)');
      $ok = $st->execute([$id, $vendedor, $direccion, $fecha]);
    } else {
      $st = $pdo->prepare('INSERT INTO ventas (vendedor, direccion, fechaventa) VALUES (?, ?, ?)');
      $ok = $st->execute([$vendedor, $direccion, $fecha]);
    }

    if (!$ok) throw new RuntimeException('No se pudo guardar el registro.');

    echo json_encode(['ok'=>true]); exit;

  } catch (PDOException $e) {
    if ($e->getCode() === '23000') {
      http_response_code(409);
      echo json_encode(['ok'=>false,'error'=>'El ID ingresado ya existe. Prueba con otro ID o deja el campo vacío para que se asigne automáticamente.']); exit;
    }
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Error de base de datos: '.$e->getMessage()]); exit;

  } catch (Throwable $e){
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); exit;
  }
}

?>

<div class="tw-card" style="max-width:800px;margin-inline:auto">
  <h2 style="margin:4px 0 10px"><i class="fa-solid fa-user-plus"></i> Agregar vendedor</h2>
  <p class="muted" style="margin-top:-2px">
    Puedes ingresar un <strong>ID</strong> manual. Si lo dejas vacío, se usará el ID <em>AUTO_INCREMENT</em>.
  </p> 

  <form id="fVend" class="grid" style="gap:14px; margin-top:12px">
    <!-- NUEVO: ID (opcional) -->
    <label class="flex flex-col">
      <span class="text-sm text-secondary">ID</span>
      <input class="ct-input" name="id" id="id" inputmode="numeric" pattern="[0-9]*" placeholder="Ej. 101 (o déjalo vacío)">
      <small class="muted">Solo números enteros positivos. Si lo dejas vacío, se asigna automáticamente.</small>
    </label>

    <label class="flex flex-col">
      <span class="text-sm text-secondary">Vendedor <span style="color:#ef4444">*</span></span>
      <input class="ct-input" name="vendedor" id="vendedor" placeholder="Ej. Ana López" required maxlength="120">
    </label>

    <label class="flex flex-col">
      <span class="text-sm text-secondary">Dirección <span style="color:#ef4444">*</span></span>
      <input class="ct-input" name="direccion" id="direccion" placeholder="Ej. Jr. Lima 123" required maxlength="180">
    </label>

    <label class="flex flex-col">
      <span class="text-sm text-secondary">Fecha venta <span style="color:#ef4444">*</span></span>
      <input class="ct-input" type="date" name="fechaventa" id="fechaventa" required>
    </label>

    <div style="display:flex; gap:.6rem; margin-top:6px; flex-wrap:wrap">
      <button class="btn-pro" type="submit"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
      <a class="btn-pro btn-pro--ghost" href="./?m=vendedores&action=lista"><i class="fa-solid fa-users"></i> Ver lista</a>
    </div>
    <p class="muted" style="margin:.25rem 0 0">Los campos marcados con <span style="color:#ef4444">*</span> son obligatorios.</p>
  </form>
</div>

<script>
const $ = (s,ctx=document)=>ctx.querySelector(s);
// Helper toast
function showToast(msg, autoHideMs = 1200) {
  const box = document.getElementById('toast');
  const txt = document.getElementById('toast-msg');
  if (!box || !txt) return;
  txt.textContent = msg || 'Guardado correctamente';
  box.hidden = false;
  setTimeout(()=>{ box.hidden = true; }, autoHideMs);
}

$('#fVend').addEventListener('submit', async (ev)=>{
  ev.preventDefault();

  const form = ev.currentTarget;
  const fd = new FormData(form);
  fd.append('__ajax','1');

  const idVal = ($('#id')?.value || '').trim();
  if (idVal !== '' && !/^\d+$/.test(idVal)) {
    alert('El ID debe contener solo números.');
    $('#id').focus();
    return;
  }

  try{
    const res = await fetch('../modules/vendedores/_save.php', {
      method:'POST',
      headers:{
        'X-Requested-With':'XMLHttpRequest',
        'Accept':'application/json'
      },
      credentials: 'same-origin',
      body: fd
    });

    const ct = res.headers.get('content-type') || '';
    let data;
    if (ct.includes('application/json')) {
      data = await res.json();
    } else {
      const txt = await res.text();
      console.error('Respuesta HTML no esperada:\n', txt);
      throw new Error('El servidor devolvió HTML en lugar de JSON. Revisa la consola.');
    }

    if (!res.ok || !data.ok) {
      throw new Error((data && data.error) || 'No se pudo guardar');
    }

    // ✅ Mostrar mensajito y limpiar
    showToast('Guardado correctamente');
    form.reset();
    document.getElementById('id')?.focus();

    // (opcional) Volver a la lista después de 1.2s:
    // setTimeout(()=>{ window.location.href = './?m=vendedores&action=lista'; }, 1200);

  }catch(err){
    alert(err.message || 'Error al guardar');
  }
});

</script>

<style>
.ct-input{
  width:100%;
  background:transparent;
  color:var(--text);
  border:1px solid var(--input-line-muted);
  border-radius:10px;
  padding:12px 14px;
  outline:none;
}
.ct-input:focus{
  border-color:var(--primary);
  box-shadow:0 0 0 3px var(--ring);
}
<style>
.toast{
  position: fixed;
  left: 50%; top: 14px; transform: translateX(-50%);
  background: #16a34a; color: #fff; 
  border-radius: 10px; padding: 10px 14px; 
  display: flex; gap: 8px; align-items: center;
  box-shadow: 0 10px 35px rgba(0,0,0,.18);
  z-index: 9999; font-weight: 600;
}
.toast[hidden]{ display:none; }
:root[data-theme="dark"] .toast{ background:#22c55e; color:#0b1120; }

</style>
