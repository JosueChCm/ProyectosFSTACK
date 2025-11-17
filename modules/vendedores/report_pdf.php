<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config.php';

$nombre = trim($_GET['nombre'] ?? '');
$motivo = trim($_GET['motivo'] ?? '');
$orden  = trim($_GET['orden']  ?? 'vendedor'); // vendedor | id | fechaventa
$download = isset($_GET['dl']);

if ($nombre==='' || $motivo==='') { http_response_code(400); echo 'Faltan nombre y motivo.'; exit; }

$map = ['vendedor'=>'vendedor ASC','id'=>'id ASC','fechaventa'=>'fechaventa ASC'];
$orderBy = $map[$orden] ?? $map['vendedor'];

$rows = $pdo->query("SELECT id, vendedor, direccion, fechaventa FROM ventas ORDER BY {$orderBy}")
            ->fetchAll(PDO::FETCH_ASSOC);

$now = date('Y-m-d H:i:s');
ob_start();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reporte de Vendedores</title>
<style>
  :root{
    --navy:#0b2a4a;      /* azul oscuro principal */
    --navy-2:#123a64;    /* variación */
    --mint:#e6f0ff;      /* fondo suave */
    --text:#111;
    --muted:#666;
    --line:#e6e8eb;
  }
  *{ box-sizing:border-box; }
  body{ font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:var(--text); font-size:12.5px; margin:0; }
  .page{ padding:24px 26px; }

  /* ===== HERO / PORTADA ===== */
  .hero{
    border-radius:12px; overflow:hidden; display:grid;
    grid-template-columns: 2fr 1fr; border:1px solid var(--line);
  }
  .hero-left{ background:#ecf2fa; padding:18px 20px; border-right:1px solid var(--line);}
  .brand{ display:flex; align-items:center; gap:8px; color:var(--navy); font-weight:700; margin-bottom:8px; }
  .brand .dot{ width:10px; height:10px; border-radius:50%; background:var(--navy); display:inline-block; }
  .hero-title{ font-size:26px; line-height:1.15; margin:6px 0 4px; color:var(--navy); }
  .hero-sub{ color:var(--muted); font-size:12px; }
  .hero-right{
    background:linear-gradient(135deg,var(--navy),var(--navy-2));
    color:#fff; padding:16px 18px; display:flex; flex-direction:column; justify-content:flex-end;
  }
  .badge-date{
    background:#fff; color:var(--navy); border-radius:8px; padding:8px 10px; font-weight:700; display:inline-block; margin-bottom:8px;
  }
  .pill{ background:rgba(255,255,255,.15); color:#fff; padding:6px 10px; border-radius:999px; font-size:11px; display:inline-block; }

  /* ===== INFO BLOCKS ===== */
  .info-2col{ margin-top:12px; display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .ibox{ border:1px solid var(--line); border-radius:10px; overflow:hidden; }
  .ibox .ih{ background:#f7f9fc; padding:8px 10px; font-weight:700; color:var(--navy); border-bottom:1px solid var(--line); }
  .ibox .ib{ padding:10px; color:#222; min-height:38px; }

  /* ===== CONTENIDO ===== */
  .section-title{ margin:18px 0 8px; font-size:14px; color:var(--navy); font-weight:800; }
  table{ width:100%; border-collapse:collapse; }
  thead th{ background:#f3f6fb; color:#344c66; border-bottom:1px solid var(--line); }
  th, td{ text-align:left; padding:8px 10px; border-bottom:1px solid var(--line); }
  tbody tr:nth-child(even){ background:#fbfdff; }

  .footer{ margin-top:14px; color:var(--muted); font-size:11px; text-align:right; }
</style>
</head>
<body>
  <div class="page">

    <!-- PORTADA -->
    <div class="hero">
      <div class="hero-left">
        <div class="brand"><span class="dot"></span> <span>Tu Empresa</span></div>
        <div class="hero-title">Reporte de Vendedores</div>
        <div class="hero-sub">Resumen general del padrón de vendedores</div>
      </div>
      <div class="hero-right">
        <div class="badge-date"><?= htmlspecialchars($now) ?></div>
        <div class="pill">Generado automáticamente</div>
      </div>
    </div>

    <!-- INFO -->
    <div class="info-2col">
      <div class="ibox">
        <div class="ih">Solicitante</div>
        <div class="ib"><?= htmlspecialchars($nombre) ?></div>
      </div>
      <div class="ibox">
        <div class="ih">Motivo</div>
        <div class="ib"><?= htmlspecialchars($motivo) ?></div>
      </div>
    </div>
    <div class="info-2col">
      <div class="ibox">
        <div class="ih">Orden</div>
        <div class="ib"><?= htmlspecialchars($orden) ?></div>
      </div>
      <div class="ibox">
        <div class="ih">Total de registros</div>
        <div class="ib"><?= count($rows) ?></div>
      </div>
    </div>

    <!-- TABLA -->
    <div class="section-title">Listado completo</div>
    <table>
      <thead>
        <tr><th style="width:60px">ID</th><th>Vendedor</th><th>Dirección</th><th style="width:120px">Fecha venta</th></tr>
      </thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars((string)$r['id']) ?></td>
          <td><?= htmlspecialchars($r['vendedor']) ?></td>
          <td><?= htmlspecialchars($r['direccion']) ?></td>
          <td><?= htmlspecialchars($r['fechaventa']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$rows): ?>
        <tr><td colspan="4" style="color:#777">No hay registros.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

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
    $filename = 'reporte_vendedores_'.date('Ymd_His').'.pdf';
    if ($download) { $dompdf->stream($filename, ['Attachment'=>true]); }
    else { header('Content-Type: application/pdf'); echo $dompdf->output(); }
    exit;
  }
}
header('Content-Type: text/html; charset=utf-8'); echo $html;
