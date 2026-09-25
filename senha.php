<?php
require_once __DIR__ . '/auth.php';
$_SESSION['auth_csrf'] ??= bin2hex(random_bytes(32));
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="auth-csrf" content="<?= e($_SESSION['auth_csrf']) ?>">
    <title>Recuperar senha — Daily Hair</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page" data-auth-page="reset">
    <div class="auth-top"></div>
    <section class="auth-card">
        <div class="logo-circle"><img src="Logo.png" alt="Daily Hair"></div>
        <h1>Recuperar senha</h1>
        <p class="auth-subtitle">Receba um link de redefinição pelo e-mail cadastrado no Firebase.</p>
        <div id="firebase-message" class="alert error" hidden></div>
        <form class="auth-form" method="post">
            <label for="email">E-mail:</label>
            <input id="email" name="email" type="email" autocomplete="email" required>
            <button class="btn primary wide" type="submit">Enviar link</button>
        </form>
        <p class="create-account"><a href="index.php">Voltar ao login</a></p>
    </section>
    <script type="module" src="firebase-auth.js"></script>
</body>
</html>
