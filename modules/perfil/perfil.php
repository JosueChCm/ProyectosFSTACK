<?php
if (!defined('APP_INIT')) { http_response_code(403); exit; }
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$stmt = $pdo->prepare('SELECT username, email, COALESCE(avatar_url,"") AS avatar_url FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['username'=>'','email'=>'','avatar_url'=>''];

$DEFAULT_AVATAR = '../uploads/avatars/default.webp';
$avatar = $user['avatar_url'] ?: $DEFAULT_AVATAR;
?>

<section class="perfil-section">
  <h2>Mi Perfil</h2>
  <hr />
  <div class="u-card" style="max-width:820px;margin-inline:auto">
    <div style="display:flex;gap:18px;align-items:center">
      <img src="<?= e($avatar) ?>" alt="Avatar" style="width:96px;height:96px;border-radius:50%;object-fit:cover">
      <div>
        <div><strong>Usuario:</strong> <?= e($user['username']) ?></div>
        <div><strong>Correo:</strong> <?= e($user['email']) ?></div>
      </div>
    </div>

    <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap">
      <a class="btn-pro" href="./?m=perfil&action=perfil_editar">
        <i class="fa-solid fa-pen"></i> Editar perfil
      </a>
    </div>
  </div>
</section>
