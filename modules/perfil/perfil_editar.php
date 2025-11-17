<?php
if (!defined('APP_INIT')) { http_response_code(403); exit; }
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$errors = [];

// POST: guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email    = trim($_POST['email'] ?? '');

  if ($username === '') $errors['username'] = 'Ingresa un nombre de usuario.';
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Correo no válido.';

  if (!$errors) {
    $q = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $q->execute([$email, $_SESSION['user_id']]);
    if ($q->fetch()) $errors['email'] = 'Este correo ya está en uso.';
  }

  $avatarPath = null;
  if (!$errors && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['avatar'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
      $errors['avatar'] = 'Error al subir el archivo.';
    } else {
      $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime  = $finfo->file($file['tmp_name']);
      if (!isset($allowed[$mime])) {
        $errors['avatar'] = 'Formato no permitido. Usa JPG, PNG o WEBP.';
      } elseif ($file['size'] > 2*1024*1024) {
        $errors['avatar'] = 'La imagen no debe superar 2MB.';
      } else {
        $ext = $allowed[$mime];
        $dir = realpath(__DIR__ . '/../../uploads') . '/avatars';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $name = 'u'.$_SESSION['user_id'].'_'.bin2hex(random_bytes(6)).'.'.$ext;
        $dest = $dir.'/'.$name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
          $errors['avatar'] = 'No se pudo guardar el archivo.';
        } else {
          $avatarPath = '../uploads/avatars/'.$name;
        }
      }
    }
  }

  if (!$errors) {
    if ($avatarPath) {
      $st = $pdo->prepare('UPDATE users SET username=?, email=?, avatar_url=?, updated_at=NOW() WHERE id=?');
      $st->execute([$username, $email, $avatarPath, $_SESSION['user_id']]);
      $_SESSION['avatar_url'] = $avatarPath;
    } else {
      $st = $pdo->prepare('UPDATE users SET username=?, email=?, updated_at=NOW() WHERE id=?');
      $st->execute([$username, $email, $_SESSION['user_id']]);
    }
    $_SESSION['username'] = $username;

        $_SESSION['username'] = $username;

    // ya no uses header('Location...') aquí
    echo '<script>window.location.href = "./?m=perfil&action=perfil";</script>';
    exit;
  }
}

// GET: datos actuales
$u = $pdo->prepare('SELECT username, email, COALESCE(avatar_url,"") AS avatar_url FROM users WHERE id = ? LIMIT 1');
$u->execute([$_SESSION['user_id']]);
$user = $u->fetch(PDO::FETCH_ASSOC);

$DEFAULT_AVATAR = '../uploads/avatars/default.webp';
$avatar = ($user['avatar_url'] ?? '') ?: $DEFAULT_AVATAR;
?>
<section class="perfil-section">
  <h2>Editar perfil</h2>
  <hr />
  <div class="u-card" style="max-width:820px;margin-inline:auto">
    <form method="post" enctype="multipart/form-data" class="grid" style="gap:14px">
      <div style="display:flex;gap:18px;align-items:center">
        <img id="previewImg" src="<?= e($avatar) ?>" alt="Avatar" style="width:120px;height:120px;border-radius:50%;object-fit:cover">
        <div>
          <label>Foto (JPG/PNG/WEBP, máx 2MB)</label>
          <input type="file" name="avatar" accept="image/*" onchange="previewAvatar(this)">
          <?php if (!empty($errors['avatar'])): ?><div class="error"><?= e($errors['avatar']) ?></div><?php endif; ?>
        </div>
      </div>

      <label class="flex flex-col">
        <span>Usuario</span>
        <input class="ct-input" type="text" name="username" value="<?= e($user['username'] ?? '') ?>" required>
        <?php if (!empty($errors['username'])): ?><div class="error"><?= e($errors['username']) ?></div><?php endif; ?>
      </label>

      <label class="flex flex-col">
        <span>Correo</span>
        <input class="ct-input" type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required>
        <?php if (!empty($errors['email'])): ?><div class="error"><?= e($errors['email']) ?></div><?php endif; ?>
      </label>

      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
        <button class="btn-pro" type="submit"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button>
        <a class="btn-pro btn-pro--ghost" href="./?m=perfil&action=perfil"><i class="fa-solid fa-arrow-left"></i> Cancelar</a>
      </div>
    </form>
  </div>
</section>

<script>
function previewAvatar(input){
  const f = input.files && input.files[0];
  if (!f) return;
  const img = document.getElementById('previewImg');
  img.src = URL.createObjectURL(f);
}
</script>
<style>
.error{color:#d92d20;font-size:13px}
.ct-input{width:100%;background:transparent;color:var(--text);border:1px solid var(--input-line-muted);border-radius:10px;padding:12px 14px;outline:none}
.ct-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px var(--ring)}
</style>
