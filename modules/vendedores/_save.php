<?php
require_once __DIR__ . '/../../includes/config.php';

function start_json_response() {
  while (ob_get_level() > 0) { ob_end_clean(); }
  header('Content-Type: application/json; charset=utf-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  start_json_response();
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Método no permitido']); exit;
}

// Si usas auth, valida sesión aquí y NUNCA redirijas en AJAX:
$loggedIn = true; // <-- ajusta según tu sistema
if (!$loggedIn) {
  start_json_response();
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Sesión vencida']); exit;
}

start_json_response();

try {
  $idRaw     = trim($_POST['id'] ?? '');
  $vendedor  = trim($_POST['vendedor'] ?? '');
  $direccion = trim($_POST['direccion'] ?? '');
  $fecha     = trim($_POST['fechaventa'] ?? '');

  if ($vendedor==='' || $direccion==='' || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha)) {
    throw new RuntimeException('Completa vendedor, dirección y fecha válida (YYYY-MM-DD).');
  }

  $useId = ($idRaw !== '');
  if ($useId) {
    if (!ctype_digit($idRaw) || (int)$idRaw <= 0) {
      throw new RuntimeException('El ID debe ser un número entero positivo.');
    }
    $id = (int)$idRaw;
  }

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
    echo json_encode(['ok'=>false,'error'=>'El ID ingresado ya existe. Usa otro o deja el campo vacío.']); exit;
  }
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Error BD: '.$e->getMessage()]); exit;

} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); exit;
}
