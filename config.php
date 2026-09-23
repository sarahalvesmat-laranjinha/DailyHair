<?php
// Configuração do Daily Hair
// Ambiente local: MySQL em localhost, usuário root e senha 1234.
define('DB_HOST', 'localhost');
define('DB_NAME', 'daily_hair');
define('DB_USER', 'root');
define('DB_PASS', '1234');
define('BASE_URL', '');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
