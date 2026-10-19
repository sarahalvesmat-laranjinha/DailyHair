<?php

require_once __DIR__ . '/auth.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
$_SESSION['auth_csrf'] ??= bin2hex(random_bytes(32));
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $attempts = $_SESSION['login_attempts'] ?? [];
    $attempts = array_values(array_filter($attempts, static fn($time) => is_int($time) && $time > time() - 900));
    $_SESSION['login_attempts'] = $attempts;
    if (count($attempts) >= 10) {
        http_response_code(429);
        $error = 'Muitas tentativas. Aguarde alguns minutos para entrar.';
    } else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = 'Preencha seu e-mail e sua senha.';
    } else {
        $stmt = db()->prepare('SELECT id, password FROM users WHERE lower(email) = lower(?) LIMIT 1');
        $stmt->bindValue(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        $found = $stmt->fetch();
        if ($found && password_verify($password, $found['password'])) {
            session_regenerate_id(true);
            unset($_SESSION['login_attempts']);
            $_SESSION['user_id'] = (int) $found['id'];
            header('Location: dashboard.php');
            exit;
        }
        $_SESSION['login_attempts'][] = time();
        $error = 'E-mail ou senha incorretos.';
    }
    }
}
?>


<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="auth-csrf" content="<?= e($_SESSION['auth_csrf']) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — Daily Hair</title>
    <link rel="icon" type="image/png" href="Logo.png">
    <link rel="apple-touch-icon" href="Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page" data-auth-page="login">
    <style>
      /* A página de entrada também recebe o rodapé, sem deslocar o cartão de login. */
      body.auth-page{display:block;padding:0}
      .dh-auth-entry{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:110px 20px 55px;position:relative}
      .auth-top{z-index:0}
    </style>
    <main class="dh-auth-entry">
    <div class="auth-top"></div>
    <section class="auth-card">
        <div class="logo-circle">
    <img src="Logo.png" alt="Daily Hair">
</div>
        <h1>Bem-vindo de volta!</h1>
        <p class="auth-subtitle">Faça login para continuar cuidando do<br>que é seu.</p>
        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <?php if (!empty($_GET['registered'])): ?>
            <div class="alert success">Conta criada! Agora é só entrar.</div><?php endif; ?>

        <div id="firebase-message" class="alert error" hidden></div>
        <form method="post" class="auth-form">
            <?= csrf_field() ?>
            <label for="email">Email:</label>
            <input id="email" name="email" type="email" autocomplete="email" required>
            <label for="password">Senha:</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <p class="create-account"><a href="senha.php">Esqueci minha senha</a></p>
            <button class="btn primary wide" type="submit">Entrar</button>
        </form>
        <button id="google-login" class="btn wide" type="button">Entrar com Google</button>
        <p class="create-account"><a href="index.php?legacy=1">Entrar com conta anterior</a></p>
        <p class="create-account">Ainda nao tem uma conta? <a href="register.php">Criar conta</a></p>
    </section>
    </main>

    <script type="module" src="firebase-auth.js"></script>
    <?php include __DIR__ . '/cookie-banner.php'; ?>
    <?php $dh_footer_without_main = true; include __DIR__ . '/footer.php'; ?>
