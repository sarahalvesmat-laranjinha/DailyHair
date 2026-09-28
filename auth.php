<?php
require_once __DIR__ . '/db.php';

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id']))
        return null;
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch() ?: null;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
