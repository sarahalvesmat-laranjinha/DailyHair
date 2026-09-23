<?php

require_once __DIR__ . '/auth.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = 'Preencha seu e-mail e sua senha.';
    } else {
        $stmt = db()->prepare('SELECT id, password FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $found = $stmt->get_result()->fetch_assoc();
        if ($found && password_verify($password, $found['password'])) {
            $_SESSION['user_id'] = (int) $found['id'];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'E-mail ou senha incorretos.';
    }
}
?>


<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — Daily Hair</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">
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

        <form method="post" class="auth-form">
            <label for="email">Email:</label>
            <input id="email" name="email" type="email" autocomplete="email" required>
            <label for="password">Senha:</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <p class="create-account"><a href="senha.php">Esqueci minha senha</a></p>
            <button class="btn primary wide" type="submit">Entrar</button>
        </form>
        <p class="create-account">Ainda nao tem uma conta? <a href="register.php">Criar conta</a></p>
    </section>
</body>

</html>