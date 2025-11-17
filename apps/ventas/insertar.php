<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../includes/config.php'; // $pdo

// (opcional) forzar login si quieres que también validen sesión fuera del panel:
require_once __DIR__ . '/../../includes/auth.php';

// ---------- flash ----------
function flash_push(string $m, string $t='info'){ $_SESSION['flash'][] = ['t'=>$t,'m'=>$m]; }
$flash = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);

// ---------- skin ----------
$skin = ($_GET['skin'] ?? 'bs'); $skin = in_array($skin,['bs','tw'],true)?$skin:'bs';
$otherSkin = $skin==='bs'?'tw':'bs';

// ---------- old ----------
$old = ['id'=>'','vendedor'=>'','direccion'=>'','fechaventa'=>''];

// ---------- insertar ----------
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $id        = trim($_POST['id'] ?? '');
  $vendedor  = trim($_POST['vendedor'] ?? '');
  $direccion = trim($_POST['direccion'] ?? '');
  $fechaventa= trim($_POST['fechaventa'] ?? '');
  $old = compact('id','vendedor','direccion','fechaventa');

  $errs=[];
  if ($id==='' || !ctype_digit($id)) $errs[]='El ID debe ser numérico.';
  if ($vendedor==='')  $errs[]='Ingresa el vendedor.';
  if ($direccion==='') $errs[]='Ingresa la dirección.';
  if ($fechaventa==='' || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$fechaventa)) $errs[]='Fecha inválida.';

  if ($errs) {
    foreach($errs as $e) flash_push($e,'danger');
  } else {
    try{
      $st=$pdo->prepare('INSERT INTO ventas (id,vendedor,direccion,fechaventa) VALUES (?,?,?,?)');
      $st->execute([(int)$id,$vendedor,$direccion,$fechaventa]);
      flash_push('✅ Registro insertado','success');
      $old = ['id'=>'','vendedor'=>'','direccion'=>'','fechaventa'=>''];
    } catch(PDOException $e){
      if ($e->getCode()==='23000') flash_push('El ID ya existe.','danger');
      else flash_push('Error al insertar: '.$e->getMessage(),'danger');
    }
  }
}

function h($s){ return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
?>
<!doctype html>
<html lang="es" data-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Insertar venta</title>

<!-- Bootstrap + Tailwind (preflight off) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script>window.tailwind=window.tailwind||{};tailwind.config={corePlugins:{preflight:false}};</script>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="../../assets/css/custom.css"><!-- tu styles -->
</head>
<body class="site-scope" style="padding:16px">

<div class="wrap" style="max-width:1100px;margin-inline:auto">
  <div class="top-actions" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;margin:8px 0 18px">
    <a class="btn-pro btn-pro--brand" href="?skin=<?= $otherSkin ?>"><i class="fa-solid fa-shuffle"></i> Cambiar a <?= $otherSkin==='bs'?'Bootstrap':'Tailwind' ?></a>
    <a class="btn-pro" style="background:linear-gradient(135deg,#059669,#0ea5e9)" href="./lectura.php?skin=<?= $skin ?>"><i class="fa-solid fa-table"></i> Ver registros</a>
  </div>

  <?php if ($skin==='bs'): ?>
  <!-- =============== Bootstrap =============== -->
  <div class="tw-card">
    <h3 class="mb-3"><i class="fa-solid fa-circle-plus me-2"></i> Insertar venta</h3>

    <?php foreach($flash as $f): ?>
      <div class="alert alert-<?= h($f['t']) ?> mb-3" role="alert"><?= h($f['m']) ?></div>
    <?php endforeach; ?>

    <form method="post" class="row g-3">
      <div class="col-sm-6 col-md-3">
        <label class="form-label">ID</label>
        <input type="number" name="id" class="form-control" required value="<?= h($old['id']) ?>">
      </div>
      <div class="col-sm-6 col-md-3">
        <label class="form-label">Vendedor</label>
        <input type="text" name="vendedor" class="form-control" required value="<?= h($old['vendedor']) ?>">
      </div>
      <div class="col-sm-6 col-md-3">
        <label class="form-label">Dirección</label>
        <input type="text" name="direccion" class="form-control" required value="<?= h($old['direccion']) ?>">
      </div>
      <div class="col-sm-6 col-md-3">
        <label class="form-label">Fecha de venta</label>
        <input type="date" name="fechaventa" class="form-control" required value="<?= h($old['fechaventa']) ?>">
      </div>
      <div class="col-12">
        <button class="btn btn-primary"><i class="fa-solid fa-save me-2"></i> Guardar</button>
      </div>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <?php else: ?>
  <!-- =============== Tailwind =============== -->
  <div class="tw-card">
    <h3 class="text-xl font-semibold mb-3"><i class="fa-solid fa-circle-plus"></i> Insertar venta</h3>

    <?php foreach($flash as $f): ?>
      <div class="px-4 py-3 mb-3 rounded-lg <?= $f['t']==='success'?'bg-green-50 text-green-700 border border-green-200':($f['t']==='danger'?'bg-red-50 text-red-700 border border-red-200':'bg-blue-50 text-blue-700 border border-blue-200') ?>">
        <?= h($f['m']) ?>
      </div>
    <?php endforeach; ?>

    <form method="post" class="grid gap-4 md:grid-cols-4">
      <label class="flex flex-col">
        <span class="text-sm text-gray-600 mb-1">ID</span>
        <input name="id" type="number" required value="<?= h($old['id']) ?>" class="px-3 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500"/>
      </label>
      <label class="flex flex-col">
        <span class="text-sm text-gray-600 mb-1">Vendedor</span>
        <input name="vendedor" type="text" required value="<?= h($old['vendedor']) ?>" class="px-3 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500"/>
      </label>
      <label class="flex flex-col">
        <span class="text-sm text-gray-600 mb-1">Dirección</span>
        <input name="direccion" type="text" required value="<?= h($old['direccion']) ?>" class="px-3 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500"/>
      </label>
      <label class="flex flex-col">
        <span class="text-sm text-gray-600 mb-1">Fecha de venta</span>
        <input name="fechaventa" type="date" required value="<?= h($old['fechaventa']) ?>" class="px-3 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500"/>
      </label>
      <div class="md:col-span-4">
        <button class="px-4 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:opacity-90"><i class="fa-solid fa-save"></i> Guardar</button>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>

<!-- Si usas componentes de Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tu JS -->
<script src="../../assets/js/custom.js"></script>
</body>
</html>
