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
/* A mesma estrutura visual do login, com a faixa vinho presa ao topo da tela. */
body.auth-page[data-auth-page="password-confirm"]{display:block;min-height:100vh;padding:0;background:var(--cream)}
.dh-reset-entry{min-height:100vh;width:100%;display:flex;align-items:center;justify-content:center;padding:116px 20px 48px;position:static}
body.auth-page[data-auth-page="password-confirm"] .auth-top{position:absolute;top:0;left:0;right:0;width:100%;height:88px;border-radius:0;z-index:0}
.dh-reset-entry .auth-card{width:min(460px,100%);max-width:460px;margin:0 auto;padding:38px 44px 34px;z-index:1}
.dh-reset-entry .auth-card h1{text-align:center}
.dh-reset-entry .auth-subtitle{text-align:center;line-height:1.5}
.dh-reset-entry [hidden]{display:none!important}
.dh-reset-entry .auth-form>label{margin:16px 0 6px}
.dh-reset-entry .password-visibility-field input{border-color:var(--line);margin-bottom:8px}
.dh-reset-entry .password-visibility-field input:focus{border-color:var(--wine)}
.dh-reset-entry .dh-reset-message{margin:16px 0;padding:12px;text-align:center;line-height:1.5}
.dh-reset-entry .create-account{text-align:center}
@media(max-width:480px){.dh-reset-entry{padding:110px 16px 30px}.dh-reset-entry .auth-card{padding:30px 22px}}
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
