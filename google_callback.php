<?php
declare(strict_types=1);
session_start();

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/conexionpdo.php'; // Debe crear $pdo (PDO conectado)
$config = require __DIR__ . '/includes/config_oauth.php';

try {
    // Validación CSRF
    if (!isset($_GET['state'], $_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
        unset($_SESSION['oauth2state']);
        throw new RuntimeException('Estado inválido.');
    }
    if (!isset($_GET['code'])) {
        throw new RuntimeException('Falta "code".');
    }

    // Intercambio de code por token
    $client = new Google\Client();
    $client->setClientId($config['google_client_id']);
    $client->setClientSecret($config['google_client_secret']);
    $client->setRedirectUri($config['google_redirect_uri']);
    $client->setScopes($config['google_scopes']);

    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        throw new RuntimeException('Error token: '.($token['error_description'] ?? $token['error']));
    }
    $client->setAccessToken($token);

    // Perfil del usuario
    $oauth2     = new Google\Service\Oauth2($client);
    $gUser      = $oauth2->userinfo->get();
    $googleId   = $gUser->id;
    $email      = $gUser->email;
    $name       = $gUser->name ?: '';
    $picture    = $gUser->picture ?: null;    // ✅ AÑADIR ESTA LÍNEA

    if (!$googleId || !$email) {
        throw new RuntimeException('No se pudo obtener google_id o email.');
    }

    $pdo->beginTransaction();

    // 1) Buscar por google_id
    $q = $pdo->prepare('SELECT id, username FROM users WHERE google_id = ? LIMIT 1');
    $q->execute([$googleId]);
    $user = $q->fetch(PDO::FETCH_ASSOC);
    if ($user) {
    $_SESSION['user_id']  = (int)$user['id'];
    $_SESSION['username'] = $user['username'];

    // ✅ Sesión: avatar desde Google (si vino)
    $_SESSION['avatar_url'] = $picture ?: null;

    // (opcional) Persistir en BD si no había avatar
    if ($picture) {
        $up = $pdo->prepare('UPDATE users SET avatar_url = COALESCE(avatar_url, ?) WHERE id = ?');
        $up->execute([$picture, $_SESSION['user_id']]);
    }

    $pdo->commit();
    return closePopupToIndex();
}


    // 2) Buscar por email (enlazar)
    $q = $pdo->prepare('SELECT id, username FROM users WHERE email = ? LIMIT 1');
    $q->execute([$email]);
    $byEmail = $q->fetch(PDO::FETCH_ASSOC);
    if ($byEmail) {
    $u = $pdo->prepare('UPDATE users SET google_id = ? WHERE id = ?');
    $u->execute([$googleId, $byEmail['id']]);

    // (opcional) Persistir avatar si llega desde Google y no había uno
    if ($picture) {
        $up = $pdo->prepare('UPDATE users SET avatar_url = COALESCE(avatar_url, ?) WHERE id = ?');
        $up->execute([$picture, $byEmail['id']]);
    }

    $_SESSION['user_id']  = (int)$byEmail['id'];
    $_SESSION['username'] = $byEmail['username'];
    // ✅ Sesión: avatar
    $_SESSION['avatar_url'] = $picture ?: null;

    $pdo->commit();
    return closePopupToIndex();
}


    // 3) Crear usuario nuevo (password = NULL)
    $username = generarUsername($pdo, $name ?: explode('@', $email)[0]);
$i = $pdo->prepare('INSERT INTO users (username, email, password, google_id) VALUES (?, ?, NULL, ?)');
$i->execute([$username, $email, $googleId]);

$_SESSION['user_id']  = (int)$pdo->lastInsertId();
$_SESSION['username'] = $username;

// ✅ Sesión: avatar desde Google (si vino)
$_SESSION['avatar_url'] = $picture ?: null;

// (opcional) Persistir en BD si tenemos foto
if ($picture) {
    $up = $pdo->prepare('UPDATE users SET avatar_url = ? WHERE id = ?');
    $up->execute([$picture, $_SESSION['user_id']]);
}

$pdo->commit();
return closePopupToIndex();


} catch (Throwable $e) {
    $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="es"><meta charset="utf-8">
<body style="font-family:sans-serif;padding:20px">
  <h3>Error al iniciar con Google</h3>
  <p>$msg</p>
  <button onclick="window.close()">Cerrar</button>
</body></html>
HTML;
    exit;
}

function generarUsername(PDO $pdo, string $base): string {
    $slug = preg_replace('~[^a-z0-9]+~i', '_', trim($base));
    $slug = trim($slug, '_');
    $slug = $slug !== '' ? strtolower($slug) : 'user';
    $candidato = $slug;
    $n = 1;
    $check = $pdo->prepare('SELECT 1 FROM users WHERE username = ? LIMIT 1');
    while (true) {
        $check->execute([$candidato]);
        if (!$check->fetch()) return $candidato;
        $n++;
        $candidato = $slug.'_'.$n;
    }
}

function closePopupToIndex(): void {
    echo <<<HTML
<!DOCTYPE html>
<html lang="es"><meta charset="utf-8">
<body>
<script>
  // Redirige la ventana principal a index.php y cierra el popup inmediatamente
  if (window.opener && !window.opener.closed) {
    window.opener.location = 'admin/index.php';
  }
  window.close();
</script>
</body></html>
HTML;
    exit;
}
