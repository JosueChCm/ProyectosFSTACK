<?php
declare(strict_types=1);
session_start();
require 'conexionpdo.php';

$errors = ['username' => '', 'email' => '', 'password' => ''];
$old    = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $passNew  = $_POST['password'] ?? '';

    $old['username'] = $username;
    $old['email']    = $email;

    if ($username === '' || preg_match('/\s/', $username)) { $errors['username'] = 'Ingresa un usuario válido (sin espacios).'; }
    if ($email === '') { $errors['email'] = 'Ingresa tu correo.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Correo no válido.'; }
    if ($passNew === '') { $errors['password'] = 'Ingresa tu nueva contraseña.'; }
    elseif (strlen($passNew) < 6) { $errors['password'] = 'La contraseña debe tener al menos 6 caracteres.'; }

    if ($errors['username'] === '' && $errors['email'] === '' && $errors['password'] === '') {
        $stmt = $pdo->prepare('SELECT id, email, password FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $errors['username'] = 'Usuario no encontrado.';
        } elseif (strcasecmp($row['email'], $email) !== 0) {
            $errors['email'] = 'El correo no coincide con el del usuario.';
        } elseif (password_verify($passNew, $row['password'])) {
            $errors['password'] = 'La nueva contraseña no puede ser igual a la actual.';
        } else {
            $hash = password_hash($passNew, PASSWORD_BCRYPT);
            $up   = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $up->execute([$hash, (int)$row['id']]);

            $_SESSION['flash'][] = 'Contraseña actualizada. Ahora inicia sesión.';
            header('Location: login.php?panel=signin');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Olvidé mi contraseña</title>
<!-- (estilos inline originales) -->
</head>
<body>
<!-- (markup original de tu forget) -->
</body>
</html>
