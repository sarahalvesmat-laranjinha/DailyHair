<?php

require_once __DIR__ . '/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Verifica se as senhas são iguais
    if ($password !== $confirm_password) {

        $error = 'As senhas não coincidem.';

    // Verifica os requisitos da senha
    } elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {

        $error = 'A senha precisa ter no mínimo 8 caracteres, uma letra maiúscula, uma minúscula e um número.';

    } else {

        // Procura o usuário pelo e-mail
        $stmt = db()->prepare(
            'SELECT id FROM users WHERE email = ? LIMIT 1'
        );

        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {

            $error = 'E-mail não encontrado.';

        } else {

            // Cria o hash da nova senha
            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Atualiza a senha no banco
            $stmt = db()->prepare(
                'UPDATE users SET password = ? WHERE id = ?'
            );

            $stmt->bind_param(
                'si',
                $password_hash,
                $user['id']
            );
if ($stmt->execute()) {
    header('Location: index.php');
    exit;
} else {
    $error = 'Erro ao alterar a senha.';
}
        }
    }
}
?>

<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    

    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">

    <div class="auth-top"></div>

    <section class="auth-card">

        <div class="logo-circle">
    <img src="Logo.png" alt="Daily Hair">
</div>
        <h1>Redefinir senha:</h1>

        <title>Alterar senha</title>
        
        <form method="post" class="auth-form">

            <label for="email">Email:</label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                required
            >

            <label for="password">Nova senha:</label>
            <input
                id="password"
                name="password"
                type="password"
                required
            >
            <p class="create-account1">
                *Mínimo 8 caracteres, sendo no mínimo 1 letra maiúscula,
                1 minúscula e 1 número
            </p>
            <br>

            <label for="confirm_password">Confirmar senha:</label>
            <input
                id="confirm_password"
                name="confirm_password"
                type="password"
                required
            >

            <button class="btn primary wide" type="submit">
                Alterar senha
            </button>

        </form>

    </section>

</body>
</html>