<?php
declare(strict_types=1);
define('APP_INIT', true);
session_start();
require __DIR__.'/conexionpdo.php';

if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email    = trim($_POST['email'] ?? '');

  if ($username === '') $errors['username'] = 'Ingresa un nombre de usuario.';
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Correo no válido.';

  // unicidad de email (otro usuario)
  if (!$errors) {
    $q = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $q->execute([$email, $_SESSION['user_id']]);
    if ($q->fetch()) $errors['email'] = 'Este correo ya está en uso.';
  }

  // Manejo de avatar (opcional)
  $avatarPath = null;
  if (!$errors && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['avatar'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
      $errors['avatar'] = 'Error al subir el archivo.';
    } else {
      // Validar tipo y tamaño (2MB)
      $allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp'];
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime = $finfo->file($file['tmp_name']);
      if (!isset($allowed[$mime])) {
        $errors['avatar'] = 'Formato no permitido. Usa JPG, PNG o WEBP.';
      } elseif ($file['size'] > 2*1024*1024) {
        $errors['avatar'] = 'La imagen no debe superar 2MB.';
      } else {
        // Guardar con nombre único
        $ext  = $allowed[$mime];
        $dir  = __DIR__.'/uploads/avatars';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $name = 'u'.$_SESSION['user_id'].'_'.bin2hex(random_bytes(6)).'.'.$ext;
        $dest = $dir.'/'.$name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
          $errors['avatar'] = 'No se pudo guardar el archivo.';
        } else {
          // Ruta pública (ajusta si usas subcarpetas públicas)
          $avatarPath = '/uploads/avatars/'.$name;
        }
      }
    }
  }

  if (!$errors) {
    if ($avatarPath) {
      $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, avatar_url = ?, updated_at = NOW() WHERE id = ?');
      $stmt->execute([$username, $email, $avatarPath, $_SESSION['user_id']]);
      $_SESSION['avatar_url'] = $avatarPath; // actualizar la sesión
    } else {
      $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, updated_at = NOW() WHERE id = ?');
      $stmt->execute([$username, $email, $_SESSION['user_id']]);
    }
    $_SESSION['username'] = $username;
    $success = 'Perfil actualizado correctamente.';
  }
}

// Cargar datos actuales
$u = $pdo->prepare('SELECT username, email, avatar_url FROM users WHERE id = ? LIMIT 1');
$u->execute([$_SESSION['user_id']]);
$user = $u->fetch(PDO::FETCH_ASSOC);

$DEFAULT_AVATAR = '/assets/img/avatar-default.png';
$avatar = $user['avatar_url'] ?: $DEFAULT_AVATAR;

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar perfil</title>
  <style>
    .card{max-width:760px;margin:24px auto;background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);padding:24px}
    .row{display:flex;gap:24px;align-items:center}
    .row img{width:120px;height:120px;border-radius:50%;object-fit:cover}
    .field{display:flex;flex-direction:column;margin:10px 0}
    .error{color:#d92d20;font-size:13px}
    .success{background:#ecfdf3;color:#027a48;padding:10px 12px;border-radius:8px;margin-bottom:10px}
    .btn{padding:10px 14px;border-radius:10px;border:1px solid #d0d5dd;background:#fff;cursor:pointer}
    .btn.primary{background:#1b1363;color:#fff;border-color:#1b1363}
  </style>
</head>
<body>
  <main class="card">
    <h2>Editar perfil</h2>
    <?php if ($success): ?><div class="success"><?= e($success) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <div class="row">
        <img id="previewImg" src="<?= e($avatar) ?>" alt="Avatar">
        <div>
          <div class="field">
            <label>Foto de perfil (JPG/PNG/WEBP, máx 2MB)</label>
            <input type="file" name="avatar" accept="image/*" onchange="preview(this)">
            <?php if (!empty($errors['avatar'])): ?><div class="error"><?= e($errors['avatar']) ?></div><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="field">
        <label>Nombre de usuario</label>
        <input type="text" name="username" value="<?= e($user['username']) ?>" required>
        <?php if (!empty($errors['username'])): ?><div class="error"><?= e($errors['username']) ?></div><?php endif; ?>
      </div>

      <div class="field">
        <label>Correo</label>
        <input type="email" name="email" value="<?= e($user['email']) ?>" required>
        <?php if (!empty($errors['email'])): ?><div class="error"><?= e($errors['email']) ?></div><?php endif; ?>
      </div>

      <div style="margin-top:12px;display:flex;gap:8px">
        <button class="btn primary" type="submit">Guardar cambios</button>
        <a class="btn" href="perfil.php">Cancelar</a>
      </div>
    </form>
  </main>

  <script>
    function preview(input){
      const file = input.files && input.files[0];
      if (!file) return;
      const img = document.getElementById('previewImg');
      img.src = URL.createObjectURL(file);
    }
  </script>
</body>
</html>
