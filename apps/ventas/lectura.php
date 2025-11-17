<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';

function flash_push(string $m, string $t='info'){ $_SESSION['flash'][]=['t'=>$t,'m'=>$m]; }
$flash = $_SESSION['flash'] ?? []; unset($_SESSION['flash']);

$skin = ($_GET['skin'] ?? 'bs'); $skin = in_array($skin,['bs','tw'],true)?$skin:'bs';
$otherSkin = $skin==='bs'?'tw':'bs';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $action = $_POST['action'] ?? '';
  if ($action==='delete') {
    $id=$_POST['id']??''; if ($id!=='' && ctype_digit($id)) {
      $pdo->prepare('DELETE FROM ventas WHERE id=?')->execute([(int)$id]);
      flash_push('Registro eliminado','success');
    } else flash_push('ID inválido','danger');
    header('Location: ?skin='.$skin); exit;
  }
  if ($action==='update') {
    $id=trim($_POST['id']??''); $vendedor=trim($_POST['vendedor']??''); $direccion=trim($_POST['direccion']??'');
    $errs=[]; if($id===''||!ctype_digit($id))$errs[]='ID inválido'; if($vendedor==='')$errs[]='Vendedor requerido'; if($direccion==='')$errs[]='Dirección requerida';
    if($errs){ flash_push(implode('. ',$errs),'danger'); }
    else {
      $chk=$pdo->prepare('SELECT 1 FROM ventas WHERE id=?'); $chk->execute([(int)$id]);
      if(!$chk->fetchColumn()){ flash_push('Registro no encontrado','danger'); }
      else{ $pdo->prepare('UPDATE ventas SET vendedor=?, direccion=? WHERE id=?')->execute([$vendedor,$direccion,(int)$id]); flash_push('Registro actualizado','success'); }
    }
    header('Location: ?skin='.$skin); exit;
  }
}

$data = $pdo->query('SELECT id,vendedor,direccion,fechaventa FROM ventas ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
function h($s){ return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
?>
<!doctype html>
<html lang="es" data-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Lectura de ventas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script>window.tailwind=window.tailwind||{};tailwind.config={corePlugins:{preflight:false}};</script>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="../../assets/css/custom.css">
</head>
<body class="site-scope" style="padding:16px">

<div class="wrap" style="max-width:1200px;margin-inline:auto">
  <div class="top-actions" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;margin:8px 0 18px">
    <a class="btn-pro btn-pro--brand" href="?skin=<?= $otherSkin ?>"><i class="fa-solid fa-shuffle"></i> Cambiar a <?= $otherSkin==='bs'?'Bootstrap':'Tailwind' ?></a>
    <a class="btn-pro" style="background:linear-gradient(135deg,#059669,#0ea5e9)" href="./insertar.php?skin=<?= $skin ?>"><i class="fa-solid fa-circle-plus"></i> Insertar</a>
  </div>

  <?php if ($skin==='bs'): ?>
  <!-- =============== Bootstrap =============== -->
  <div class="tw-card">
    <h3 class="mb-3"><i class="fa-solid fa-table me-2"></i> Registros</h3>

    <?php foreach($flash as $f): ?>
      <div class="alert alert-<?= h($f['t']) ?> mb-3" role="alert"><?= h($f['m']) ?></div>
    <?php endforeach; ?>

    <div class="table-tools d-flex gap-2 align-items-center mb-2 flex-wrap">
      <div class="input-group" style="max-width:360px">
        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
        <input id="q" type="search" class="form-control" placeholder="Buscar…">
      </div>
      <button id="csv" class="btn btn-outline-success"><i class="fa-solid fa-file-csv"></i> CSV</button>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle" id="tabla">
        <thead class="table-light"><tr><th>ID</th><th>Vendedor</th><th>Dirección</th><th>Fecha</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
        <?php foreach($data as $r): ?>
          <tr>
            <td><span class="badge text-bg-secondary"><?= h((string)$r['id']) ?></span></td>
            <td><?= h($r['vendedor']) ?></td>
            <td><?= h($r['direccion']) ?></td>
            <td><?= h($r['fechaventa']) ?></td>
            <td class="text-end">
              <button class="btn btn-sm btn-primary me-1" type="button" data-edit='<?= h(json_encode($r,JSON_UNESCAPED_UNICODE)) ?>'>
                <i class="fa-solid fa-pen-to-square"></i> Editar
              </button>
              <form method="post" action="" style="display:inline" onsubmit="return confirm('¿Eliminar registro #<?= h((string)$r['id']) ?>?');">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h((string)$r['id']) ?>">
                <button class="btn btn-sm btn-danger" type="submit"><i class="fa-solid fa-trash"></i> Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <?php else: ?>
  <!-- =============== Tailwind =============== -->
  <div class="tw-card">
    <h3 class="text-xl font-semibold mb-3"><i class="fa-solid fa-table"></i> Registros</h3>

    <?php foreach($flash as $f): ?>
      <div class="px-4 py-3 mb-3 rounded-lg <?= $f['t']==='success'?'bg-green-50 text-green-700 border border-green-200':($f['t']==='danger'?'bg-red-50 text-red-700 border border-red-200':'bg-blue-50 text-blue-700 border border-blue-200') ?>">
        <?= h($f['m']) ?>
      </div>
    <?php endforeach; ?>

    <div class="table-tools" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-bottom:12px">
      <div class="relative" style="max-width:360px">
        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500"><i class="fa-solid fa-search"></i></span>
        <input id="q" type="search" placeholder="Buscar…" class="pl-8 pr-2 py-2 bg-transparent outline-none border-b-2 border-gray-300 focus:border-blue-500 w-full">
      </div>
      <button id="csv" class="btn-pro" style="padding:.5rem .8rem;background:linear-gradient(135deg,#16a34a,#22c55e)">
        <i class="fa-solid fa-file-csv"></i> CSV
      </button>
    </div>

    <div class="overflow-auto">
      <table id="tabla" class="min-w-full text-sm">
        <thead class="bg-gray-100 text-gray-700">
          <tr><th class="text-left px-3 py-2">ID</th><th class="text-left px-3 py-2">Vendedor</th><th class="text-left px-3 py-2">Dirección</th><th class="text-left px-3 py-2">Fecha</th><th class="text-right px-3 py-2">Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach($data as $r): ?>
          <tr class="border-b border-gray-100">
            <td class="px-3 py-2"><?= h((string)$r['id']) ?></td>
            <td class="px-3 py-2"><?= h($r['vendedor']) ?></td>
            <td class="px-3 py-2"><?= h($r['direccion']) ?></td>
            <td class="px-3 py-2"><?= h($r['fechaventa']) ?></td>
            <td class="px-3 py-2 text-right">
              <button class="btn-pro" style="padding:.35rem .6rem;background:linear-gradient(135deg,#2563eb,#7c3aed)" data-edit='<?= h(json_encode($r,JSON_UNESCAPED_UNICODE)) ?>'>
                <i class="fa-solid fa-pen-to-square"></i> Editar
              </button>
              <form method="post" action="" style="display:inline" onsubmit="return confirm('¿Eliminar registro #<?= h((string)$r['id']) ?>?');">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h((string)$r['id']) ?>">
                <button class="btn-pro" style="padding:.35rem .6rem;background:linear-gradient(135deg,#ef4444,#f97316)">
                  <i class="fa-solid fa-trash"></i> Eliminar
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Modal edición + utilidades -->
<div class="xmodal-mask" id="mask">
  <div class="xmodal" role="dialog" aria-modal="true" aria-labelledby="mTitle">
    <div class="xmodal-head">
      <h5 id="mTitle" style="margin:0"><i class="fa-solid fa-pen-to-square"></i> Editar registro</h5>
      <button class="btn btn-secondary" id="mClose" type="button"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="post" action="">
      <div class="xmodal-body">
        <input type="hidden" name="action" value="update">
        <div class="mb-3"><label class="form-label">ID (no modificable)</label><input class="form-control is-ro" name="id" id="mId" readonly></div>
        <div class="mb-3"><label class="form-label">Vendedor</label><input class="form-control" name="vendedor" id="mVendedor" required maxlength="120"></div>
        <div class="mb-3"><label class="form-label">Dirección</label><input class="form-control" name="direccion" id="mDireccion" required maxlength="180"></div>
        <div class="mb-1"><label class="form-label">Fecha (no modificable)</label><input type="date" class="form-control" id="mFecha" disabled></div>
      </div>
      <div class="xmodal-foot">
        <button type="button" class="btn btn-secondary" id="mCancel">Cancelar</button>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
/* filtro */
const q=document.getElementById('q'); const tbody=document.querySelector('#tabla tbody');
q?.addEventListener('input',()=>{const t=q.value.toLowerCase().trim(); for(const tr of tbody.rows){ tr.style.display = tr.innerText.toLowerCase().includes(t) ? '' : 'none'; }});
/* csv */
document.getElementById('csv')?.addEventListener('click',()=>{
  const rows=[['ID','Vendedor','Dirección','Fecha']]; for(const tr of tbody.rows){ if(tr.style.display==='none')continue; const c=[...tr.cells]; rows.push([c[0].innerText.trim(),c[1].innerText.trim(),c[2].innerText.trim(),c[3].innerText.trim()]); }
  const csv=rows.map(r=>r.map(v=>`"${String(v).replace(/"/g,'""')}"`).join(',')).join('\n'); const blob=new Blob([csv],{type:'text/csv;charset=utf-8;'}); const url=URL.createObjectURL(blob);
  const a=document.createElement('a'); a.href=url; a.download='ventas.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
});
/* modal edición */
const mask=document.getElementById('mask'); const mClose=document.getElementById('mClose'); const mCancel=document.getElementById('mCancel');
function openModal(d){ document.getElementById('mId').value=d.id??''; document.getElementById('mVendedor').value=d.vendedor??''; document.getElementById('mDireccion').value=d.direccion??''; document.getElementById('mFecha').value=d.fechaventa??''; mask.style.display='flex'; }
function closeModal(){ mask.style.display='none'; }
mClose?.addEventListener('click',closeModal); mCancel?.addEventListener('click',closeModal); mask?.addEventListener('click',e=>{ if(e.target===mask) closeModal(); });
document.querySelectorAll('[data-edit]').forEach(b=>b.addEventListener('click',()=>{ try{ openModal(JSON.parse(b.getAttribute('data-edit'))); }catch{} }));
</script>
<!-- Si usas componentes de Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tu JS -->
<script src="../../assets/js/custom.js"></script>
</body>
</html>
