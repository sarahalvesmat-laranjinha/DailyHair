<?php
// Página pública: o Firebase valida o código de recuperação no navegador.
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Redefinir senha — Daily Hair</title>
<link rel="icon" type="image/png" href="Logo.png">
<link rel="stylesheet" href="style.css">
<style>
/* Estilos exclusivos da página de redefinição de senha. */
body.dh-reset-page{margin:0;min-height:100vh;background:#f8f2e9;color:#382a30;font-family:"DM Sans",Arial,sans-serif;display:grid;place-items:center;padding:24px;box-sizing:border-box}
.dh-reset-card{width:min(100%,430px);box-sizing:border-box;background:#fff;padding:36px 30px;border:1px solid #e9dfe0;border-radius:4px;box-shadow:0 8px 30px #542e3812}
.dh-reset-brand{text-align:center;color:#762f48;font-family:"Playfair Display",Georgia,serif;font-size:25px;margin:0 0 24px}
.dh-reset-card h1{font-size:25px;color:#762f48;margin:0 0 10px;text-align:center}
.dh-reset-card p{line-height:1.6;text-align:center}
.dh-reset-form{display:grid;gap:12px;margin-top:24px}
.dh-reset-form label{font-size:14px;font-weight:600}
.dh-reset-field{display:flex;align-items:center;border:1px solid #bdaab0;border-radius:3px;background:#fff}
.dh-reset-field:focus-within{outline:2px solid #762f48;outline-offset:1px}
.dh-reset-field input{width:100%;min-width:0;padding:13px;border:0;outline:0;background:transparent;color:#382a30;font:inherit}
.dh-reset-field button{border:0;background:transparent;color:#762f48;padding:10px;cursor:pointer;font:inherit;font-size:13px}
.dh-reset-submit{margin-top:10px;padding:14px;border:0;border-radius:3px;background:#762f48;color:white;font:inherit;font-weight:700;cursor:pointer}
.dh-reset-submit:disabled{opacity:.65;cursor:wait}
.dh-reset-message{padding:12px;border-radius:3px;text-align:center;font-size:14px}
.dh-reset-message.error{background:#fff0f0;color:#912c37}
.dh-reset-message.success{background:#edf6ef;color:#235b38}
.dh-reset-back{display:block;margin-top:22px;text-align:center;color:#762f48;text-decoration:underline}
@media(max-width:480px){.dh-reset-card{padding:28px 20px}}
</style>
</head>
<body class="dh-reset-page">
<main class="dh-reset-card">
<div class="dh-reset-brand">Daily Hair</div>
<h1>Redefinir senha</h1>
<p>Escolha uma nova senha para acessar sua conta.</p>
<div id="reset-message" class="dh-reset-message" role="status" aria-live="polite" hidden></div>
<form id="reset-form" class="dh-reset-form" hidden>
<label for="new-password">Nova senha</label>
<div class="dh-reset-field"><input id="new-password" type="password" autocomplete="new-password" minlength="6" required><button type="button" data-toggle="new-password" aria-label="Mostrar nova senha">Mostrar</button></div>
<label for="confirm-password">Confirmar nova senha</label>
<div class="dh-reset-field"><input id="confirm-password" type="password" autocomplete="new-password" minlength="6" required><button type="button" data-toggle="confirm-password" aria-label="Mostrar confirmação de senha">Mostrar</button></div>
<button class="dh-reset-submit" type="submit">Salvar nova senha</button>
</form>
<a class="dh-reset-back" href="index.php">Voltar para o login</a>
</main>
<script type="module" src="redefinir-senha.js"></script>
</body>
</html>
