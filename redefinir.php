<?php
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="referrer" content="no-referrer">
<title>Redefinir senha — Daily Hair</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
[hidden]{display:none!important}
.btn:disabled{opacity:.6;cursor:wait;transform:none}
.password-hint{color:var(--muted);font-size:13px;line-height:1.5;text-align:left}
.auth-form label:not(:first-child){margin-top:16px}
:focus-visible{outline:3px solid var(--terracotta);outline-offset:3px}
</style>
</head>
<body class="auth-page">
<div class="auth-top" aria-hidden="true"></div>
<main class="auth-card">
<div class="logo-circle"><img src="Logo.png" alt="Daily Hair"></div>
<h1 id="action-title">Redefinir senha</h1>
<p class="auth-subtitle" id="action-subtitle">Uma nova senha para continuar cuidando de você.</p>
<div id="action-message" class="alert" role="status" aria-live="polite">Verificando seu link…</div>
<form id="password-form" class="auth-form" hidden>
<label for="new-password">Nova senha</label>
<input id="new-password" type="password" autocomplete="new-password" minlength="8" required placeholder="Crie uma nova senha com pelo menos 8 caracteres">
<label for="confirm-password">Confirmar nova senha</label>
<input id="confirm-password" type="password" autocomplete="new-password" minlength="8" required placeholder="Confirme sua nova senha">
<p class="password-hint">Use pelo menos 8 caracteres e siga os requisitos de senha da sua conta.</p>
<button id="save-password" class="btn primary wide" type="submit">Salvar nova senha</button>
</form>
<button id="confirm-action" class="btn primary wide" type="button" hidden>Confirmar</button>
<p class="create-account"><a id="request-link" href="senha.php" hidden>Solicitar outro link</a></p>
<p class="create-account"><a href="index.php">Voltar ao login</a></p>
<noscript><p>Ative o JavaScript no navegador para continuar.</p></noscript>
</main>
<script type="module" src="firebase-action.js"></script>
</body>
</html>
