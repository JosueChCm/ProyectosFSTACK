<?php
require_once __DIR__ . '/../../includes/config.php';

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Método no permitido']); exit;
}

try {
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

} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); exit;
}
