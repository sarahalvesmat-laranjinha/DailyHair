<?php
require_once __DIR__ . '/config.php';

function db(): mysqli
{
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli("localhost", "root", "", "daily_hair", 3307);
        if ($conn->connect_error) {
            die('Erro ao conectar ao banco de dados:' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
?>