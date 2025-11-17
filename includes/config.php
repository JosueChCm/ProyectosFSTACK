<?php
// Carga tu conexión PDO original (no tocada)
require_once __DIR__ . '/../conexionpdo.php'; // debe exponer $pdo

define('APP_NAME', 'TuPanel');
define('BASE_PATH', dirname(__DIR__));

if (!isset($pdo)) {
  http_response_code(500);
  exit('La conexión $pdo no está disponible. Revisa conexionpdo.php');
}
