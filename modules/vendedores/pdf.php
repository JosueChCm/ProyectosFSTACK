<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$download = isset($_GET['dl']);
if ($id <= 0) { http_response_code(400); echo 'ID inválido'; exit; }

$st = $pdo->prepare('SELECT id, vendedor, direccion, fechaventa FROM ventas WHERE id=?');
$st->execute([$id]);
$v = $st->fetch(PDO::FETCH_ASSOC);
if (!$v) { http_response_code(404); echo 'No encontrado'; exit; }

$now = date('Y-m-d H:i:s');
ob_start();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Ficha de Vendedor</title>
<style>
  :root{ --navy:#0b2a4a; --navy-2:#123a64; --line:#e6e8eb; --muted:#666; }
  body{ font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#111; font-size:12.5px; margin:0; }
  .page{ padding:24px 26px; }
  .hero{ border-radius:12px; overflow:hidden; border:1px solid var(--line); display:grid; grid-template-columns:2fr 1fr; }
  .hero-left{ background:#ecf2fa; padding:18px 20px; border-right:1px solid var(--line); }
  .brand{ display:flex; align-items:center; gap:8px; color:var(--navy); font-weight:700; margin-bottom:8px; }
  .brand .dot{ width:10px; height:10px; border-radius:50%; background:var(--navy); display:inline-block; }
  .hero-title{ font-size:24px; color:var(--navy); margin:6px 0 4px; }
  .hero-right{ background:linear-gradient(135deg,var(--navy),var(--navy-2)); color:#fff; padding:16px 18px; display:flex; flex-direction:column; justify-content:flex-end; }
  .badge{ background:#fff; color:var(--navy); border-radius:8px; padding:8px 10px; font-weight:700; display:inline-block; }
  .card{ border:1px solid var(--line); border-radius:10px; padding:12px; margin-top:14px; }
  table{ width:100%; border-collapse:collapse; }
  th,td{ text-align:left; padding:8px 10px; border-bottom:1px solid var(--line); }
  th{ background:#f3f6fb; color:#344c66; }
  .footer{ margin-top:14px; color:var(--muted); font-size:11px; text-align:right; }
</style>
</head>
<body>
  <div class="page">
    <div class="hero">
      <div class="hero-left">
        <div class="brand"><span class="dot"></span> <span>Tu Empresa</span></div>
        <div class="hero-title">Ficha de Vendedor</div>
        <div style="color:#666">ID #<?= htmlspecialchars((string)$v['id']) ?></div>
      </div>
      <div class="hero-right">
        <span class="badge"><?= htmlspecialchars($now) ?></span>
      </div>
    </div>

    <div class="card">
      <table>
        <tr><th style="width:180px">Vendedor</th><td><?= htmlspecialchars((string)$v['vendedor']) ?></td></tr>
        <tr><th>Dirección</th><td><?= htmlspecialchars((string)$v['direccion']) ?></td></tr>
        <tr><th>Fecha de venta</th><td><?= htmlspecialchars((string)$v['fechaventa']) ?></td></tr>
      </table>
    </div>

    <div class="footer">© <?= date('Y') ?> · Generado por el sistema</div>
  </div>
</body>
</html>
<?php
$html = ob_get_clean();

$autoloader = __DIR__ . '/../../vendor/autoload.php';
if (is_file($autoloader)) {
  require_once $autoloader;
  if (class_exists('Dompdf\\Dompdf')) {
    $dompdf = new Dompdf\Dompdf(['isRemoteEnabled'=>true]);
    $dompdf->loadHtml($html,'UTF-8');
    $dompdf->setPaper('A4','portrait');
    $dompdf->render();
    $filename = 'vendedor_'.$v['id'].'.pdf';
    if ($download) { $dompdf->stream($filename, ['Attachment'=>true]); }
    else { header('Content-Type: application/pdf'); echo $dompdf->output(); }
    exit;
  }
}
header('Content-Type: text/html; charset=utf-8'); echo $html;
