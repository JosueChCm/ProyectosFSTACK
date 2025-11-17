<?php
declare(strict_types=1);
session_start();
require 'conexionpdo.php'; // Debe crear $pdo (PDO conectado a tu BD)

// ==============================
// LECTURA DE MENSAJES DE SESIÓN
// ==============================
$flash  = $_SESSION['flash'] ?? [];
$errors = $_SESSION['field_errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['field_errors'], $_SESSION['old'], $_SESSION['flash']);

// =======================
// PROCESAMIENTO DE POST
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';           // 'register' | 'login'
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // ---------- REGISTRO ----------
    if ($action === 'register') {
        $email = trim($_POST['email'] ?? '');
        $errs  = [];

        if ($username === '')                              	$errs['register.username'] = 'Ingresa un nombre de usuario.';
        if ($email === '')                                  $errs['register.email']    = 'Ingresa tu correo.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errs['register.email']    = 'Correo no válido.';
        if ($password === '')                               $errs['register.password'] = 'Ingresa una contraseña.';
        elseif (strlen($password) < 6)                      $errs['register.password'] = 'La contraseña debe tener al menos 6 caracteres.';

        if ($errs) {
            $_SESSION['field_errors'] = $errs;
            $_SESSION['old'] = [
                'register.username' => $username,
                'register.email'    => $email,
            ];
            header('Location: login.php?panel=signup');
            exit;
        }

        $check = $pdo->prepare('SELECT username, email FROM users WHERE username = ? OR email = ? LIMIT 1');
        $check->execute([$username, $email]);
        if ($row = $check->fetch(PDO::FETCH_ASSOC)) {
            if (strcasecmp($row['username'], $username) === 0) $errs['register.username'] = 'El usuario ya está en uso.';
            if (strcasecmp($row['email'], $email) === 0)       $errs['register.email']    = 'El correo ya está en uso.';

            $_SESSION['field_errors'] = $errs;
            $_SESSION['old'] = [
                'register.username' => $username,
                'register.email'    => $email,
            ];
            header('Location: login.php?panel=signup');
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, $hash]);

        $_SESSION['flash'][] = 'Usuario registrado. Ahora inicia sesión.';
        header('Location: login.php?panel=signin');
        exit;
    }

    // ---------- LOGIN ----------
    if ($action === 'login') {
        $errs = [];
        if ($username === '')      $errs['login.username'] = 'Ingresa tu usuario o correo.';
        elseif ($password === '')  $errs['login.username'] = 'Ingresa tu contraseña.';

        if ($errs) {
            $_SESSION['field_errors'] = $errs;
            $_SESSION['old'] = ['login.username' => $username];
            header('Location: login.php?panel=signin');
            exit;
        }

        $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
          $_SESSION['user_id']  = (int)$user['id'];
          $_SESSION['username'] = $user['username'];

          // ✅ CARGAR AVATAR DESDE BD A LA SESIÓN (AQUÍ)
          $picStmt = $pdo->prepare('SELECT avatar_url FROM users WHERE id = ?');
          $picStmt->execute([$_SESSION['user_id']]);
          $_SESSION['avatar_url'] = ($picStmt->fetchColumn()) ?: null;

          // 🔁 ADAPTACIÓN: llevar al dashboard nuevo
          header('Location: admin/index.php');
          exit;
        }else {
            $_SESSION['field_errors'] = ['login.username' => 'Usuario o contraseña incorrectos.'];
            $_SESSION['old'] = ['login.username' => $username];
            header('Location: login.php?panel=signin');
            exit;
        }
    }
}

// ================================
// UI (idéntica a tu archivo)
// ================================
$panel          = $_GET['panel'] ?? 'signin';
$containerClass = ($panel === 'signup') ? 'right-panel-active' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <style>
    /* ======================================================
       BASE
       ====================================================== */
    @import url('https://fonts.googleapis.com/css?family=Montserrat:400,800');
    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      /* TRANSPARENTE para ver el fondo animado */
      background: transparent;
      display: flex; justify-content: center; align-items: center; flex-direction: column;
      font-family: 'Montserrat', sans-serif;
      height: 100vh; margin: -20px 0 50px;
    }
    h1 { font-weight: bold; margin: 0; }
    h2 { text-align: center; }
    p  { font-size: 14px; font-weight: 100; line-height: 20px; letter-spacing: .5px; margin: 20px 0 30px; }
    span { font-size: 12px; }

    a {
      color: #34a39eff; font-size: 14px; text-decoration: none; margin: 15px 0;
    }

    button {
      border-radius: 20px;
      border: 1px solid #93acf3ff;
      background-color: #1b1363ff;
      color: #fdfdfdff;
      font-size: 12px; font-weight: bold;
      padding: 12px 45px; letter-spacing: 1px; text-transform: uppercase;
      transition: transform 80ms ease-in;
    }
    button:active { transform: scale(0.95); }
    button:focus  { outline: none; }
    button.ghost  { background-color: transparent; border-color: #fff; }

    form {
      background-color: rgba(255,255,255,.92);
      display: flex; align-items: center; justify-content: center; flex-direction: column;
      padding: 0 50px; height: 100%; text-align: center;
    }
    input {
      background-color: #c8dcf8ff;
      border: none; padding: 12px 15px; margin: 8px 0; width: 100%;
    }

    /* ======================================================
       FONDO DIAGONAL ANIMADO
       ====================================================== */
    .bg {
      position: fixed;
      top: 0; right: -50%; bottom: 0; left: -50%;
      opacity: .5;
      background-image: linear-gradient(-60deg, rgba(13, 1, 122, 1) 50%, rgba(15, 89, 158, 1) 50%);
      animation: slide 3s ease-in-out infinite alternate;
      z-index: 0; /* al fondo */
    }
    .bg2 { animation-duration: 4s; animation-direction: alternate-reverse; z-index: 0; }
    .bg3 { animation-duration: 5s; z-index: 0; }

    @keyframes slide {
      0% { transform: translateX(-25%); }
      100% { transform: translateX(25%); }
    }

    /* ======================================================
       TARJETA PRINCIPAL + PANELES
       ====================================================== */
    .container {
      background-color: #d7e7f7ff;
      border-radius: 10px;
      box-shadow: 0 14px 28px rgba(101,203,250,.25), 0 10px 10px rgba(100,255,255,.22);
      position: relative; overflow: hidden;
      width: 768px; max-width: 100%; min-height: 480px;
      z-index: 10; /* sobre el fondo */
    }
    .form-container { position: absolute; top: 0; height: 100%; transition: all .6s ease-in-out; }

    /* --- LOGIN (visible por defecto) --- */
    .sign-in-container { left: 0; width: 50%; z-index: 2; transform: translateX(0); opacity: 1; }
    .container.right-panel-active .sign-in-container { transform: translateX(100%); }

    /* --- REGISTRO (oculto por defecto) --- */
    .sign-up-container { left: 0; width: 50%; z-index: 1; opacity: 0; transform: translateX(0); }
    .container.right-panel-active .sign-up-container {
      transform: translateX(100%); opacity: 1; z-index: 5; animation: show .6s;
    }

    @keyframes show { 0%,49.99%{opacity:0;z-index:1;} 50%,100%{opacity:1;z-index:5;} }

    /* ======================================================
       OVERLAY (capa de color que se desliza)
       ====================================================== */
    .overlay-container {
      position: absolute; top: 0; left: 50%; width: 50%; height: 100%;
      overflow: hidden; transition: transform .6s ease-in-out;
      z-index: 20; /* por encima de formularios inactivos */
    }
    .container.right-panel-active .overlay-container { transform: translateX(-100%); }

    .overlay {
      background: linear-gradient(to right, #021b88ff, #0073f7ff);
      color: #57d4f3ff;
      position: relative; left: -100%;
      height: 100%; width: 200%;
      transform: translateX(0); transition: transform .6s ease-in-out;
      pointer-events: none;              /* la capa en sí no bloquea */
    }
    .overlay .overlay-panel { pointer-events: auto; } /* pero los botones sí clickeables */

    .container.right-panel-active .overlay { transform: translateX(50%); }

    .overlay-panel {
      position: absolute; display: flex; align-items: center; justify-content: center; flex-direction: column;
      padding: 0 40px; text-align: center; top: 0; height: 100%; width: 50%;
      transform: translateX(0); transition: transform .6s ease-in-out;
    }
    .overlay-left  { transform: translateX(-20%); }
    .overlay-right { right: 0; transform: translateX(0); }
    .container.right-panel-active .overlay-left  { transform: translateX(0); }
    .container.right-panel-active .overlay-right { transform: translateX(20%); }

    /* ======================================================
       REDES Y FOOTER (opcionales)
       ====================================================== */
    .social-container { margin: 20px 0; }
    .social-container a {
      border: 1px solid #a4bdf1ff; border-radius: 50%;
      display: inline-flex; justify-content: center; align-items: center;
      margin: 0 5px; height: 40px; width: 40px;
    }
    footer {
      background: #222; color: #fff; font-size: 14px;
      bottom: 0; position: fixed; left: 0; right: 0; text-align: center; z-index: 999;
    }
    footer p { margin: 10px 0; }
    footer i { color: red; }
    footer a { color: #3c97bf; text-decoration: none; }

    /* Respeta preferencia de movimiento reducido */
    @media (prefers-reduced-motion: reduce) {
      .bg, .bg2, .bg3 { animation: none; }
    }
  </style>
</head>
<body>
  <!-- Fondo diagonal animado (justo después de <body>, fuera del .container) -->
  <div class="bg"></div>
  <div class="bg bg2"></div>
  <div class="bg bg3"></div>

  <h2>PAGINA DE ACCESO A LA WEB</h2>

  <div class="container <?= htmlspecialchars($containerClass, ENT_QUOTES) ?>" id="container">
    <!-- REGISTRO -->
    <div class="form-container sign-up-container">
      <form method="post" action="">
        <input type="hidden" name="action" value="register">
        <h1>Crea una cuenta</h1>

        <input
          type="text" name="username" placeholder="Nombre de usuario" required
          value="<?= htmlspecialchars($old['register.username'] ?? '', ENT_QUOTES) ?>">

        <input
          type="email" name="email" placeholder="Correo" required
          value="<?= htmlspecialchars($old['register.email'] ?? '', ENT_QUOTES) ?>">

        <input
          type="password" name="password" placeholder="Contraseña" required>

        <a href="javascript:void(0);" onclick="openGooglePopup()" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #ddd;padding:10px 14px;border-radius:8px">
          <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" style="height:20px;width:20px;border-radius:50%">
          <span>Continuar con Google</span>
        </a>
        
        <button type="submit">Registrarse</button>
      </form>
    </div>

    <!-- LOGIN -->
    <div class="form-container sign-in-container">
      <form method="post" action="">
        <input type="hidden" name="action" value="login">
        <h1>Inicio de Sesión</h1>

        <input
          type="text" name="username" placeholder="Usuario o correo" required
          value="<?= htmlspecialchars($old['login.username'] ?? '', ENT_QUOTES) ?>">

        <input type="password" name="password" placeholder="Contraseña" required>

        <a href="javascript:void(0);" onclick="openGooglePopup()" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #ddd;padding:10px 14px;border-radius:8px">
          <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" style="height:20px;width:20px;border-radius:50%">
          <span>Continuar con Google</span>
        </a>

        <a href="forget.php">¿Olvidaste tu contraseña?</a>
        <button type="submit">Iniciar Sesión</button>
      </form>
    </div>

    <!-- OVERLAY -->
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <h1>¡Bienvenido!</h1>
          <p>Para mantenerte conectado, inicia sesión con tu información.</p>
          <button class="ghost" id="signIn">Inicia Sesión</button>
        </div>
        <div class="overlay-panel overlay-right">
          <h1>¡Hola, amigo!</h1>
          <p>Introduce tus datos personales y comienza a navegar</p>
          <button class="ghost" id="signUp">Regístrate</button>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($flash)): ?>
  <script>
    <?php foreach ($flash as $msg): ?>
      alert(<?= json_encode($msg, JSON_UNESCAPED_UNICODE) ?>);
    <?php endforeach; ?>
  </script>
  <?php endif; ?>

  <script>
    // Cambiar de panel (UI)
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container    = document.getElementById('container');

    signUpButton.addEventListener('click', () => container.classList.add('right-panel-active'));
    signInButton.addEventListener('click', () => container.classList.remove('right-panel-active'));

    // Pasar errores del servidor a JS para tooltips nativos
    window.SERVER_FIELD_ERRORS = <?= json_encode($errors, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    // Mostrar mensajes nativos (igual al “Completa este campo”)
    (() => {
      const errs = (typeof window.SERVER_FIELD_ERRORS === 'object' && window.SERVER_FIELD_ERRORS) ? window.SERVER_FIELD_ERRORS : {};

      function showNativeError(input, msg) {
        if (!input || !msg) return;
        input.setCustomValidity(msg);
        setTimeout(() => input.reportValidity(), 0);
        const clear = () => input.setCustomValidity('');
        input.addEventListener('input',  clear, { once: true });
        input.addEventListener('change', clear, { once: true });
      }

      // LOGIN: solo en usuario
      if (errs['login.username']) {
        const userLogin = document.querySelector('.sign-in-container input[name="username"]');
        showNativeError(userLogin, errs['login.username']);
      }

      // REGISTRO: por campo
      if (errs['register.username']) {
        const userReg = document.querySelector('.sign-up-container input[name="username"]');
        showNativeError(userReg, errs['register.username']);
      }
      if (errs['register.email']) {
        const emailReg = document.querySelector('.sign-up-container input[name="email"]');
        showNativeError(emailReg, errs['register.email']);
      }
      if (errs['register.password']) {
        const passReg = document.querySelector('.sign-up-container input[name="password"]');
        showNativeError(passReg, errs['register.password']);
      }
    })();

    function openGooglePopup() {
    // Abrir la ventana emergente
    const width = 500;
    const height = 600;
    const left = (window.innerWidth / 2) - (width / 2);
    const top = (window.innerHeight / 2) - (height / 2);

    const popup = window.open('google_login.php', 'GoogleLogin', `width=${width},height=${height},top=${top},left=${left},resizable=yes`);

    // Esperar que el popup se cierre y recargar la página de login
    const interval = setInterval(function () {
      if (popup.closed) {
        clearInterval(interval);
        location.reload();  // Recarga la página de login
      }
    }, 1000);
  }
  </script>
</body>
</html>
