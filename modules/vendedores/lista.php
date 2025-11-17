<?php
if (!defined('APP_INIT')) { http_response_code(403); exit; }
require_once __DIR__ . '/../../includes/config.php'; // $pdo

// --- AJAX: eliminar / actualizar ---
$isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
  header('Content-Type: application/json; charset=utf-8');
  $action = $_POST['action'] ?? '';
  try {
    if ($action === 'delete') {
      $id = (int)($_POST['id'] ?? 0);
      if ($id <= 0) throw new RuntimeException('ID inválido');
      $st = $pdo->prepare('DELETE FROM ventas WHERE id = ?');
      $st->execute([$id]);
      echo json_encode(['ok'=>true]); exit;
    }
    if ($action === 'update') {
      $id        = (int)($_POST['id'] ?? 0);
      $vendedor  = trim($_POST['vendedor'] ?? '');
      $direccion = trim($_POST['direccion'] ?? '');
      $fecha     = trim($_POST['fechaventa'] ?? '');
      if ($id<=0 || $vendedor==='' || $direccion==='' || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha)) {
        throw new RuntimeException('Datos incompletos o inválidos');
      }
      $up = $pdo->prepare('UPDATE ventas SET vendedor=?, direccion=?, fechaventa=? WHERE id=?');
      $up->execute([$vendedor,$direccion,$fecha,$id]);
      echo json_encode(['ok'=>true]); exit;
    }
    echo json_encode(['ok'=>false,'error'=>'Acción no soportada']);
  } catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
  }
  exit;
}

// --- Datos para la tabla ---
$rows = $pdo->query('SELECT id, vendedor, direccion, fechaventa FROM ventas ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<div class="tw-card">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
    <h2 style="margin:4px 0 10px"><i class="fa-solid fa-users"></i> Lista de vendedores</h2>
    <div style="display:flex;gap:.5rem;align-items:center">
      <div class="relative" style="max-width:320px">
        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-secondary"><i class="fa-solid fa-search"></i></span>
        <input id="q" type="search" placeholder="Buscar…" class="pl-8 pr-2 py-2 bg-transparent outline-none border-b-2" style="border-color:#cbd5e1">
      </div>
      <a class="btn-pro" href="./?m=vendedores&action=agregar"><i class="fa-solid fa-user-plus"></i> Nuevo</a>
    </div>
  </div>

  <div class="overflow-auto" style="margin-top:12px">
    <table id="tabla-vendedores" class="min-w-full table-hover" style="width:100%;border-collapse:collapse">
      <thead style="background:rgba(0,0,0,.04)">
        <tr>
          <th style="text-align:left;padding:10px">ID</th>
          <th style="text-align:left;padding:10px">Vendedor</th>
          <th style="text-align:left;padding:10px">Dirección</th>
          <th style="text-align:left;padding:10px">Fecha venta</th>
          <th style="text-align:right;padding:10px">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr data-id="<?= h($r['id']) ?>">
            <td style="padding:10px"><span class="badge text-secondary"><?= h($r['id']) ?></span></td>
            <td style="padding:10px"><?= h($r['vendedor']) ?></td>
            <td style="padding:10px"><?= h($r['direccion']) ?></td>
            <td style="padding:10px"><?= h($r['fechaventa']) ?></td>
            <td style="padding:10px;text-align:right;white-space:nowrap">
              <button class="icon-btn js-view"   title="Ver PDF" aria-label="Ver"><i class="fa-regular fa-eye"></i></button>
              <button class="icon-btn js-edit"   title="Editar"  aria-label="Editar"><i class="fa-regular fa-pen-to-square"></i></button>
              <button class="icon-btn js-delete" title="Eliminar" aria-label="Eliminar"><i class="fa-regular fa-trash-can"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (!$rows): ?><p class="muted" style="margin:12px 10px 0">Aún no hay registros.</p><?php endif; ?>
  </div>
</div>

<!-- Modal EDITAR -->
<div class="xmodal-mask" id="editMask">
  <div class="xmodal" role="dialog" aria-modal="true" aria-labelledby="editTitle">
    <div class="xmodal-head">
      <h5 id="editTitle" style="margin:0"><i class="fa-solid fa-pen-to-square"></i> Editar vendedor</h5>
      <button class="btn" id="editClose" type="button"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="editForm" method="post">
      <div class="xmodal-body grid" style="gap:12px">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="e_id">
        <label class="flex flex-col">
          <span class="text-sm text-secondary">Vendedor</span>
          <input name="vendedor" id="e_vendedor" class="ct-input" required maxlength="120" />
        </label>
        <label class="flex flex-col">
          <span class="text-sm text-secondary">Dirección</span>
          <input name="direccion" id="e_direccion" class="ct-input" required maxlength="180" />
        </label>
        <label class="flex flex-col">
          <span class="text-sm text-secondary">Fecha venta</span>
          <input type="date" name="fechaventa" id="e_fechaventa" class="ct-input" required />
        </label>
      </div>
      <div class="xmodal-foot">
        <button type="button" class="btn btn-secondary" id="editCancel">Cancelar</button>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal VER (PDF) -->
<div class="xmodal-mask" id="viewMask">
  <div class="xmodal" role="dialog" aria-modal="true" aria-labelledby="viewTitle">
    <div class="xmodal-head">
      <h5 id="viewTitle" style="margin:0"><i class="fa-regular fa-eye"></i> Ficha del vendedor</h5>
      <button class="btn" id="viewClose" type="button"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="xmodal-body" style="height:min(70vh,700px);padding:0">
      <iframe id="pdfFrame" title="Vista previa PDF" style="width:100%;height:100%;border:0"></iframe>
    </div>
    <div class="xmodal-foot" style="justify-content:space-between">
      <div style="display:flex;gap:.5rem">
        <a id="openPdf"  class="btn btn-secondary" target="_blank" rel="noopener"><i class="fa-solid fa-up-right-from-square"></i> Abrir aparte</a>
        <a id="downPdf"  class="btn btn-primary"  target="_blank" rel="noopener"><i class="fa-solid fa-download"></i> Descargar</a>
      </div>
      <button class="btn btn-secondary" id="viewCancel" type="button">Cerrar</button>
    </div>
  </div>
</div>
<!-- Toast de éxito -->
<div id="toast" class="toast" hidden>
  <i class="fa-solid fa-circle-check"></i>
  <span id="toast-msg">Operación exitosa</span>
</div>

<script>
// Filtro
const q = document.getElementById('q');
const tbody = document.querySelector('#tabla-vendedores tbody');
q?.addEventListener('input', () => {
  const t = q.value.toLowerCase().trim();
  for (const tr of tbody.rows) tr.style.display = tr.innerText.toLowerCase().includes(t) ? '' : 'none';
});

// AJAX helper
const postJSON = async (body) => {
  const res = await fetch('./?m=vendedores&action=lista', {
    method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body
  });
  return res.json();
};

// Eliminar
document.querySelectorAll('.js-delete').forEach(btn => {
  btn.addEventListener('click', async () => {
    const tr = btn.closest('tr'); const id = tr?.dataset.id;
    if (!id) return;
    if (!confirm(`¿Eliminar registro #${id}?`)) return;

    const fd = new FormData(); fd.append('id', id);
    try{
      const res = await fetch('../modules/vendedores/_delete.php', {
        method:'POST',
        headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
        credentials: 'same-origin',
        body: fd
      });
      const ct = res.headers.get('content-type') || '';
      const data = ct.includes('application/json') ? await res.json() : { ok:false, error:'Respuesta no-JSON' };
      if (!res.ok || !data.ok) throw new Error(data.error || 'No se pudo eliminar');
      showToast('Eliminado correctamente', true); // refresca tabla
    }catch(e){
      alert(e.message || 'Error al eliminar');
    }
  });
});


// Editar
const editMask=document.getElementById('editMask'),
      eClose=document.getElementById('editClose'),
      eCancel=document.getElementById('editCancel'),
      eForm=document.getElementById('editForm');

function openEdit(data){
  document.getElementById('e_id').value = data.id;
  document.getElementById('e_vendedor').value = data.vendedor || '';
  document.getElementById('e_direccion').value = data.direccion || '';
  document.getElementById('e_fechaventa').value = data.fechaventa || '';

  if (window.ModalUtil) ModalUtil.open('editMask');
  else { const el=document.getElementById('editMask'); el?.removeAttribute('hidden'); el.style.display='flex'; }
}
function closeEdit(){
  if (window.ModalUtil) ModalUtil.close('editMask');
  else { const el=document.getElementById('editMask'); if(el){ el.style.display='none'; el.setAttribute('hidden',''); } }
}

document.getElementById('editClose')?.addEventListener('click', closeEdit);
document.getElementById('editCancel')?.addEventListener('click', closeEdit);

// (Re)engancha los botones editar
document.querySelectorAll('.js-edit').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const tr = btn.closest('tr');
    if (!tr) return;
    const data = {
      id: tr.dataset.id,
      vendedor: tr.children[1].innerText.trim(),
      direccion: tr.children[2].innerText.trim(),
      fechaventa: tr.children[3].innerText.trim()
    };
    openEdit(data);
  });
});

document.getElementById('editForm').addEventListener('submit', async (ev)=>{
  ev.preventDefault();
  const fd = new FormData(ev.currentTarget);
  try{
    const res = await fetch('../modules/vendedores/_update.php', {
      method:'POST',
      headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
      credentials: 'same-origin',
      body: fd
    });
    const ct = res.headers.get('content-type') || '';
    const data = ct.includes('application/json') ? await res.json() : { ok:false, error:'Respuesta no-JSON' };
    if (!res.ok || !data.ok) throw new Error(data.error || 'No se pudo guardar');
    closeEdit();
    showToast('Guardado correctamente', true); // refresca tabla
  }catch(e){
    alert(e.message || 'Error al guardar');
  }
});


eForm.addEventListener('submit', async (ev)=>{
  ev.preventDefault();
  const fd = new FormData(eForm);
  const r = await postJSON(fd);
  if (!r.ok){ alert(r.error || 'No se pudo guardar'); return; }
  const id = document.getElementById('e_id').value; 
  const tr = document.querySelector(`tr[data-id="${CSS.escape(id)}"]`);
  tr.children[1].innerText = document.getElementById('e_vendedor').value;
  tr.children[2].innerText = document.getElementById('e_direccion').value;
  tr.children[3].innerText = document.getElementById('e_fechaventa').value;
  closeEdit();
});

// Ver (PDF)
const viewMask=document.getElementById('viewMask'),
      vClose=document.getElementById('viewClose'),
      vCancel=document.getElementById('viewCancel'),
      pdfFrame=document.getElementById('pdfFrame'),
      openPdf=document.getElementById('openPdf'),
      downPdf=document.getElementById('downPdf');
function openView(id){
  const raw = `../modules/vendedores/pdf.php?id=${encodeURIComponent(id)}`;
  // Intenta ocultar toolbar y panel lateral del visor nativo
  const iframeUrl = raw + '#toolbar=0&navpanes=0&pagemode=none&view=FitH';
  pdfFrame.src = iframeUrl;            // ← previsualiza sin barras
  openPdf.href = raw;                  // abrir aparte = PDF “limpio”
  downPdf.href = raw + '&dl=1';        // descargar
  viewMask.style.display = 'flex';
}

function closeView(){ viewMask.style.display='none'; pdfFrame.src='about:blank'; }
vClose.addEventListener('click', closeView); vCancel.addEventListener('click', closeView);
document.querySelectorAll('.js-view').forEach(btn=>btn.addEventListener('click',()=>{ const tr=btn.closest('tr'); const id=tr?.dataset.id; if(id) openView(id); }));

// ---------- helper: toast + reload ----------
function showToast(msg, thenReload=false){
  const box = document.getElementById('toast');
  const txt = document.getElementById('toast-msg');
  if (!box || !txt) return;
  txt.textContent = msg || 'Operación exitosa';
  box.hidden = false;
  setTimeout(()=>{
    box.hidden = true;
    if (thenReload) location.reload();
  }, 900);
}

</script>

<style>
.icon-btn{ background:transparent; border:0; cursor:pointer; padding:6px 8px; border-radius:8px; color:var(--text); opacity:.85; }
.icon-btn:hover{ background:rgba(0,0,0,.06); opacity:1; }
:root[data-theme="dark"] .icon-btn:hover{ background:rgba(255,255,255,.08); }
.ct-input{ width:100%; background:transparent; color:var(--text); border:1px solid var(--input-line-muted); border-radius:10px; padding:10px 12px; outline:none; }
.ct-input:focus{ border-color:var(--primary); box-shadow:0 0 0 3px var(--ring); }
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
