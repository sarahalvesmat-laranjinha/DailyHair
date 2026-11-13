<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Redefinir senha — Daily Hair</title>
<link rel="icon" type="image/png" href="Logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
/* Apenas ajustes necessários ao formulário de redefinição; o visual vem do style.css do Daily Hair. */
.dh-reset-entry{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:110px 20px 55px;position:relative}
.dh-reset-entry .auth-top{z-index:0}
.dh-reset-entry [hidden]{display:none!important}
.dh-reset-entry .auth-form>label{margin-top:15px}
.dh-reset-entry .password-visibility-field input{border-color:var(--line)}
.dh-reset-entry .password-visibility-field input:focus{border-color:var(--wine)}
.dh-reset-entry .dh-reset-message{margin:15px 0}
.dh-reset-entry .auth-card{max-width:460px}
@media(max-width:480px){.dh-reset-entry .auth-card{padding:30px 24px}}
</style>
</head>
<body class="auth-page" data-auth-page="password-confirm">
<main class="dh-reset-entry">
<div class="auth-top"></div>
<section class="auth-card">
<div class="logo-circle"><img src="Imagem%20do%20ChatGPT%2027%20de%20out.%20de%202026%2C%2014_26_48%20%281%29.png" alt="Daily Hair"></div>
<h1>Redefinir senha</h1>
<p class="auth-subtitle">Escolha uma nova senha para continuar cuidando do que é seu.</p>
<div id="reset-message" class="alert dh-reset-message" role="status" aria-live="polite" hidden></div>
<form id="reset-form" class="auth-form" hidden>
<label for="new-password">Nova senha:</label>
<span class="password-visibility-field"><input id="new-password" type="password" autocomplete="new-password" minlength="6" required placeholder="Digite sua nova senha"><button class="password-visibility-toggle" type="button" data-toggle="new-password" aria-label="Mostrar nova senha">Mostrar</button></span>
<label for="confirm-password">Confirmar nova senha:</label>
<span class="password-visibility-field"><input id="confirm-password" type="password" autocomplete="new-password" minlength="6" required placeholder="Confirme sua nova senha"><button class="password-visibility-toggle" type="button" data-toggle="confirm-password" aria-label="Mostrar confirmação de senha">Mostrar</button></span>
<button class="btn primary wide" type="submit">Salvar nova senha</button>
</form>
<p class="create-account"><a href="index.php">Voltar para o login</a></p>
</section>
</main>
<script type="module" src="redefinir-senha.js"></script>
</body>
</html>
