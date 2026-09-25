<?php

require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$_SESSION['auth_csrf'] ??= bin2hex(random_bytes(32));
$error = '';

?>

<!doctype html>

<html lang="pt-BR">

<head>

    <meta charset="utf-8">
    <meta name="auth-csrf" content="<?= e($_SESSION['auth_csrf']) ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Daily Hair</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="style.css">

</head>

<body class="auth-page" data-auth-page="register">

    <div class="auth-top"></div>

    <section class="auth-card register-card">

        <div class="logo-circle">

            <img src="Logo.png" alt="Daily Hair">

        </div>

        <h1>Crie sua conta</h1>

        <p class="auth-subtitle">

            Conte um pouco sobre seu cabelo para<br>
            personalizarmos sua rotina.

        </p>

        <?php if ($error): ?>

            <div class="alert error">

                <?= e($error) ?>

            </div>

        <?php endif; ?>

        <div id="firebase-message" class="alert error" hidden></div>
        <form method="post" class="auth-form grid-form">

            <label>

                Nome:

                <input
                    name="name"
                    required
                    value="<?= e($_POST['name'] ?? '') ?>"
                >

            </label>

            <label>

                Email:

                <input
                    name="email"
                    type="email"
                    required
                    value="<?= e($_POST['email'] ?? '') ?>"
                >

            </label>

            <label>

                Senha:

                <input
                    name="password"
                    type="password"
                    minlength="6"
                    required
                >

            </label>

            <label>

                Confirmar senha:

                <input
                    name="confirm"
                    type="password"
                    minlength="6"
                    required
                >

            </label>

            <label>

                Tipo de cabelo:

                <select name="hair_type">

                    <option>Liso</option>
                    <option>Ondulado</option>
                    <option>Cacheado</option>
                    <option>Crespo</option>

                </select>

            </label>

            <label>

                Curvatura:

                <select name="curl_pattern">

                    <option value="">Selecione a curvatura</option>

                    <option value="1A">1A</option>
                    <option value="1B">1B</option>
                    <option value="1C">1C</option>

                    <option value="2A">2A</option>
                    <option value="2B">2B</option>
                    <option value="2C">2C</option>

                    <option value="3A">3A</option>
                    <option value="3B">3B</option>
                    <option value="3C">3C</option>

                    <option value="4A">4A</option>
                    <option value="4B">4B</option>
                    <option value="4C">4C</option>

                </select>

            </label>

            <label class="full">

                Principais necessidades:

               <select name="needs[]" multiple size="1" required>

                    <option value="Frizz">Frizz</option>

                    <option value="Ressecamento">Ressecamento</option>

                    <option value="Falta de definição">
                        Falta de definição
                    </option>

                    <option value="Falta de brilho">
                        Falta de brilho
                    </option>

                    <option value="Queda">Queda</option>

                    <option value="Quebra">Quebra</option>

                    <option value="Pontas duplas">
                        Pontas duplas
                    </option>

                    <option value="Oleosidade">
                        Oleosidade
                    </option>

                    <option value="Caspa">Caspa</option>

                    <option value="Danos por química">
                        Danos por química
                    </option>

                    <option value="Danos por calor">
                        Danos por calor
                    </option>

                    <option value="Falta de volume">
                        Falta de volume
                    </option>

                    <option value="Excesso de volume">
                        Excesso de volume
                    </option>

                    <option value="Cabelo poroso">
                        Cabelo poroso
                    </option>

                    <option value="Enfraquecimento">
                        Enfraquecimento
                    </option>

                    <option value="Descoloração">
                        Descoloração
                    </option>

                    <option value="Manter a hidratação">
                        Manter a hidratação
                    </option>

                    <option value="Crescimento saudável">
                        Crescimento saudável
                    </option>

                </select>

        

<label class="full">

    <input type="checkbox" name="aceite_termos" required
           style="width: 14px; height: 14px; margin-right: 5px; vertical-align: middle;">

    <span>
        Li e aceito os

        <a href="termos.php" target="_blank"
           style="color: #0066cc; text-decoration: underline;">
            Termos de Uso
        </a>

        e estou ciente da

        <a href="privacidade.php" target="_blank"
           style="color: #0066cc; text-decoration: underline;">
            Política de Privacidade
        </a>.

    </span>

</label>

            <button
                class="btn primary wide full"
                type="submit"
            >
                Criar conta
            </button>

        </form>

    
<button id="google-login" class="btn wide" type="button">Criar conta com Google</button>
        <p class="create-account">Já tem uma conta? <a href="index.php">Entrar</a></p>
           

        

        <p class="create-account">

            Ao continuar, você concorda com nossos Termos de Serviço e confirma que leu nossa Política de Privacidade. Podemos enviar atualizações de produtos e ofertas especiais por e-mail. Pode cancelar a inscrição a qualquer momento (veja Política de Privacidade). 

            

        </p>

    </section>


    <script type="module" src="firebase-auth.js"></script>
</body>

</html>