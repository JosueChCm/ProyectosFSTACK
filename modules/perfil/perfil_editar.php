<?php
if (!defined('APP_INIT')) { http_response_code(403); exit; }
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$errors = [];
$DEFAULT_AVATAR = '../uploads/avatars/default.webp';

// ==========================
// CARGAR DATOS DEL USUARIO
// ==========================
$u = $pdo->prepare('
    SELECT username,
           email,
           COALESCE(avatar_url,"") AS avatar_url,
           password,
           google_id
    FROM users
    WHERE id = ?
    LIMIT 1
');
$u->execute([$_SESSION['user_id']]);
$user = $u->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // algo raro: no existe usuario
    echo '<p>No se encontró el usuario actual.</p>';
    exit;
}

// ¿Tiene contraseña local?
$hasLocalPassword = !empty($user['password']);   // si es NULL o '', es false
$avatar = $user['avatar_url'] ?: $DEFAULT_AVATAR;

// ==========================
// POST: GUARDAR CAMBIOS
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tomamos lo que viene del formulario
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    // --- Validación básica de perfil (nombre y correo) ---
    if ($username === '') {
        $errors['username'] = 'Ingresa un nombre de usuario.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Correo no válido.';
    }

    // Correo único (excluyendo mi propio id)
    if (!$errors) {
        $q = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $q->execute([$email, $_SESSION['user_id']]);
        if ($q->fetch()) {
            $errors['email'] = 'Este correo ya está en uso.';
        }
    }

    // ==============================
    // BLOQUE: CAMBIO DE CONTRASEÑA
    // Solo si tiene contraseña local
    // ==============================
    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    $newPass2    = $_POST['new_password_confirm'] ?? '';

    // Solo permitimos intentar cambiar password si existe una local
    $changePassword = $hasLocalPassword && (
        $currentPass !== '' ||
        $newPass     !== '' ||
        $newPass2    !== ''
    );

    if ($changePassword) {
        // Todos los campos obligatorios
        if ($currentPass === '') {
            $errors['current_password'] = 'Ingresa tu contraseña actual.';
        }
        if ($newPass === '') {
            $errors['new_password'] = 'Ingresa la nueva contraseña.';
        }
        if ($newPass2 === '') {
            $errors['new_password_confirm'] = 'Repite la nueva contraseña.';
        }

        if (!$errors) {
            // Reglas de seguridad mínimas (ajusta a gusto)
            if (strlen($newPass) < 6) {
                $errors['new_password'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            }

            if ($newPass !== $newPass2) {
                $errors['new_password_confirm'] = 'Las contraseñas nuevas no coinciden.';
            }

            // Verificar contraseña actual contra el hash guardado
            if (!$errors) {
                if (empty($user['password']) || !password_verify($currentPass, $user['password'])) {
                    $errors['current_password'] = 'La contraseña actual no es correcta.';
                }
            }
        }
    }

    // ==============================
    // BLOQUE: AVATAR (igual que antes)
    // ==============================
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

    // ==============================
    // SI TODO ESTÁ OK → UPDATE
    // ==============================
    if (!$errors) {
        // Construimos dinámicamente los campos a actualizar
        $fields = [
            'username' => $username,
            'email'    => $email,
        ];
        if ($avatarPath) {
            $fields['avatar_url'] = $avatarPath;
        }
        if ($changePassword) {
            $fields['password'] = password_hash($newPass, PASSWORD_BCRYPT);
        }

        $setParts = [];
        $params   = [];
        foreach ($fields as $col => $val) {
            $setParts[] = "$col = ?";
            $params[]   = $val;
        }

        // Si tienes updated_at en la tabla, lo actualizamos
        $setParts[] = 'updated_at = NOW()';

        $sql = 'UPDATE users SET '.implode(', ', $setParts).' WHERE id = ?';
        $params[] = $_SESSION['user_id'];

        $st = $pdo->prepare($sql);
        $st->execute($params);

        // Actualizar sesión
        $_SESSION['username'] = $username;
        if ($avatarPath) {
            $_SESSION['avatar_url'] = $avatarPath;
        }

        // Redirección sin header() (para no chocar con los headers)
        echo '<script>window.location.href = "./?m=perfil&action=perfil";</script>';
        exit;
    }

    // Si hay errores, sobreescribimos los valores del formulario para que no se pierdan
    $user['username'] = $username;
    $user['email']    = $email;
    if ($avatarPath) {
        $avatar = $avatarPath;
    }
}

// (si venimos de GET o de POST con errores, usamos $user cargado arriba)
$avatar = $user['avatar_url'] ?: $DEFAULT_AVATAR;
?>
<div class="u-card" style="max-width:820px;margin-inline:auto">
  <h2>Editar perfil</h2>
  <hr />
  <form method="post" enctype="multipart/form-data" class="grid" style="gap:14px; margin-top:10px">
    <!-- AVATAR -->
    <div style="display:flex;gap:18px;align-items:center">
      <img id="previewImg" src="<?= e($avatar) ?>" alt="Avatar" style="width:120px;height:120px;border-radius:50%;object-fit:cover">
      <div>
        <label>Foto (JPG/PNG/WEBP, máx 2MB)</label>
        <input type="file" name="avatar" accept="image/*" onchange="previewAvatar(this)">
        <?php if (!empty($errors['avatar'])): ?>
          <div class="error"><?= e($errors['avatar']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- NOMBRE DE USUARIO -->
    <label class="flex flex-col">
      <span>Usuario</span>
      <input class="ct-input" type="text" name="username" value="<?= e($user['username'] ?? '') ?>" required>
      <?php if (!empty($errors['username'])): ?>
        <div class="error"><?= e($errors['username']) ?></div>
      <?php endif; ?>
    </label>

    <!-- CORREO -->
    <label class="flex flex-col">
      <span>Correo</span>
      <input class="ct-input" type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required>
      <?php if (!empty($errors['email'])): ?>
        <div class="error"><?= e($errors['email']) ?></div>
      <?php endif; ?>
    </label>

    <!-- BLOQUE CAMBIO DE CONTRASEÑA: SOLO SI TIENE PASSWORD LOCAL -->
    <?php if ($hasLocalPassword): ?>
    <div style="grid-column:1/-1; margin-top:10px; padding:12px 14px; border-radius:10px; border:1px solid var(--input-line-muted); background:rgba(148,163,184,.06);">
      <h3 style="margin:0 0 8px;font-size:1rem;">Cambiar contraseña</h3>
      <p class="muted" style="margin:0 0 10px;font-size:.85rem;">
        Si no deseas cambiar tu contraseña, deja estos campos vacíos.
      </p>

      <div class="grid" style="gap:12px;">
        <label class="flex flex-col">
          <span>Contraseña actual</span>
          <input class="ct-input" type="password" name="current_password" autocomplete="current-password">
          <?php if (!empty($errors['current_password'])): ?>
            <div class="error"><?= e($errors['current_password']) ?></div>
          <?php endif; ?>
        </label>

        <label class="flex flex-col">
          <span>Nueva contraseña</span>
          <input class="ct-input" type="password" name="new_password" autocomplete="new-password">
          <?php if (!empty($errors['new_password'])): ?>
            <div class="error"><?= e($errors['new_password']) ?></div>
          <?php endif; ?>
        </label>

        <label class="flex flex-col">
          <span>Repetir nueva contraseña</span>
          <input class="ct-input" type="password" name="new_password_confirm" autocomplete="new-password">
          <?php if (!empty($errors['new_password_confirm'])): ?>
            <div class="error"><?= e($errors['new_password_confirm']) ?></div>
          <?php endif; ?>
        </label>
      </div>
    </div>
    <?php else: ?>
      <!-- Opcional: pequeño aviso para cuentas solo-Google -->
      <div style="grid-column:1/-1; margin-top:10px; padding:12px 14px; border-radius:10px; border:1px solid var(--input-line-muted); background:rgba(148,163,184,.06);">
        <p class="muted" style="margin:0;font-size:.85rem;">
          Esta cuenta está vinculada con Google. Actualmente no tienes una contraseña local configurada.
        </p>
      </div>
    <?php endif; ?>

    <!-- BOTONES -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;grid-column:1/-1;">
      <button class="btn-pro" type="submit">
        <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
      </button>
      <a class="btn-pro btn-pro--ghost" href="./?m=perfil&action=perfil">
        <i class="fa-solid fa-arrow-left"></i> Cancelar
      </a>
    </div>
  </form>
</div>

<script>
function previewAvatar(input){
  const f = input.files && input.files[0];
  if (!f) return;
  const img = document.getElementById('previewImg');
  img.src = URL.createObjectURL(f);
}
</script>
<style>
.error{
  color:#d92d20;
  font-size:13px;
  margin-top:2px;
}
.ct-input{
  width:100%;
  background:transparent;
  color:var(--text);
  border:1px solid var(--input-line-muted);
  border-radius:10px;
  padding:12px 14px;
  outline:none;
}
.ct-input:focus{
  border-color:var(--primary);
  box-shadow:0 0 0 3px var(--ring);
}
</style>
