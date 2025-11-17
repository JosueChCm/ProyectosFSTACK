<?php
declare(strict_types=1);
session_start();

require __DIR__.'/vendor/autoload.php';
$config = require __DIR__ . '/includes/config_oauth.php';

$client = new Google\Client();
$client->setClientId($config['google_client_id']);
$client->setClientSecret($config['google_client_secret']);
$client->setRedirectUri($config['google_redirect_uri']);
$client->setAccessType('offline');          // opcional
$client->setPrompt('select_account');       // selector de cuenta
$client->setScopes($config['google_scopes']);

// CSRF state
$state = bin2hex(random_bytes(16));
$_SESSION['oauth2state'] = $state;
$client->setState($state);

// Redirige a Google
header('Location: '.$client->createAuthUrl());
exit;
