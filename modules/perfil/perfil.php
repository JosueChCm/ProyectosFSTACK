<?php
declare(strict_types=1);
define('APP_INIT', true);
session_start();
require __DIR__.'/conexionpdo.php';

if (empty($_SESSION['user_id'])) {
  header('Location: login.php'); exit;
}

$userStmt = $pdo->prepare('SELECT username, email, avatar_url FROM users WHERE id = ? LIMIT 1');
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

$DEFAULT_AVATAR = '/assets/img/avatar-default.png';
$avatar = $user['avatar_url'] ?: $DEFAULT_AVATAR;

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Mi perfil</title>
  <link rel="stylesheet" href="/assets/css/tu-estilo.css">
  <style>
    .profile-card{max-width:760px;margin:24px auto;background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);padding:24px}
    .profile-row{display:flex;gap:24px;align-items:center}
    .profile-row img{width:96px;height:96px;border-radius:50%;object-fit:cover}
    .muted{color:#667085}
    .btn{display:inline-flex;align-items:center;gap:8px;border:1px solid #d0d5dd;border-radius:10px;padding:10px 14px;background:#fff;cursor:pointer;text-decoration:none}
  </style>
</head>
<body>
  <?php /* incluye aquí tu header/sidebar si quieres */ ?>

  <main class="profile-card">
    <h2>Mi perfil</h2>
    <div class="profile-row" style="margin-top:12px">
      <img src="<?= e($avatar) ?>" alt="Avatar actual">
      <div>
        <div><strong>Usuario:</strong> <?= e($user['username']) ?></div>
        <div><strong>Correo:</strong> <?= e($user['email']) ?></div>
        <div class="muted">Puedes editar tu información.</div>
      </div>
    </div>

    <div style="margin-top:16px">
      <a class="btn" href="perfil_editar.php">Editar perfil</a>
    </div>
  </main>
</body>
</html>
