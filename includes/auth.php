<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}


// protege todo /admin
if (!isset($_SESSION['user_id'])) {
  // evita bucles si ya estás en login/forget/logout
  $self = basename($_SERVER['PHP_SELF'] ?? '');
  if (!in_array($self, ['login.php','forget.php','logout.php'], true)) {
    header('Location: ../login.php');
    exit;
  }
}

function current_user_name(): string {
  return isset($_SESSION['username']) && $_SESSION['username'] !== ''
    ? (string)$_SESSION['username']
    : 'usuario';
}
