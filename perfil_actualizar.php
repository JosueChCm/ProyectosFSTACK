<?php
// perfil_actualizar.php
define('APP_INIT', true);
session_start();

if (!isset($_SESSION['user'])) {
  header('Location: /login.php'); exit;
}

// Conecta a DB (ajusta a tu forma de conexión)
require __DIR__ . 'conexionpdo.php'; // crea aquí tu $pdo (PDO)

$userId       = (int) $_SESSION['user']['id'];
$display_name = trim($_POST['display_name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$password     = $_POST['password'] ?? '';

if ($display_name === '' || $email === '') {
  header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/?error=validation')); exit;
}

// 6.1) Procesar avatar (opcional)
$avatarPath = null; // quedará con la ruta si hay nueva imagen
$defaultAvatar = 'uploads/avatars/default.png';
$uploadDir = __DIR__ . '/uploads/avatars';
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }

if (!empty($_FILES['avatar']['name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
  $file = $_FILES['avatar'];

  // Validaciones
  if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: /?hash=MiPerfil&error=upload'); exit;
  }
  if ($file['size'] > 2 * 1024 * 1024) { // 2MB
    header('Location: /?hash=MiPerfil&error=size'); exit;
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($file['tmp_name']);
  $ext   = match ($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    default      => null
  };
  if (!$ext) {
    header('Location: /?hash=MiPerfil&error=type'); exit;
  }

  // Nombre único
  $basename = 'u' . $userId . '_' . time();
  $targetRel = 'uploads/avatars/' . $basename . '.' . $ext;
  $targetAbs = $uploadDir . '/' . $basename . '.' . $ext;

  if (!move_uploaded_file($file['tmp_name'], $targetAbs)) {
    header('Location: /?hash=MiPerfil&error=move'); exit;
  }

  // Opcional: borrar el anterior si no es el default
  $old = $_SESSION['user']['avatar'] ?? '';
  if ($old && $old !== $defaultAvatar) {
    $oldAbs = __DIR__ . '/' . ltrim($old, '/');
    if (is_file($oldAbs)) @unlink($oldAbs);
  }

  $avatarPath = $targetRel;
}

// 6.2) Construye SQL dinámico
$fields = ['display_name = :display_name', 'email = :email'];
$params = [':display_name' => $display_name, ':email' => $email, ':id' => $userId];

if ($password !== '') {
  $fields[] = 'password = :password';
  $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
}
if ($avatarPath) {
  $fields[] = 'avatar = :avatar';
  $params[':avatar'] = $avatarPath;
}

$sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// 6.3) Refresca sesión
$_SESSION['user']['display_name'] = $display_name;
$_SESSION['user']['email']        = $email;
if ($avatarPath) $_SESSION['user']['avatar'] = $avatarPath;

header('Location: /#MiPerfil');
exit;
