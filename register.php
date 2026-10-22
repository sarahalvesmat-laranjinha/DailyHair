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

    <!-- Guia educativo visível para ajudar na escolha da curvatura durante o cadastro. -->
    <aside class="dh-register-guides" aria-label="Guia de curvaturas capilares">
      <div class="dh-register-bubble dh-register-left dh-register-top">
        <strong>Tipo 1 · Liso</strong>
        <span><b>1A:</b> fio bem reto e sem ondas. <b>1B:</b> liso com leve corpo. <b>1C:</b> liso mais encorpado, podendo apresentar pequenas curvas.</span>
      </div>
      <div class="dh-register-bubble dh-register-left dh-register-bottom">
        <strong>Tipo 2 · Ondulado</strong>
        <span><b>2A:</b> ondas suaves. <b>2B:</b> ondas em S mais visíveis. <b>2C:</b> ondas marcadas, às vezes próximas de cachos.</span>
      </div>
      <div class="dh-register-bubble dh-register-right dh-register-top">
        <strong>Tipo 3 · Cacheado</strong>
        <span><b>3A:</b> cachos largos. <b>3B:</b> espirais mais fechadas. <b>3C:</b> cachos pequenos e densos.</span>
      </div>
      <div class="dh-register-bubble dh-register-right dh-register-bottom">
        <strong>Tipo 4 · Crespo</strong>
        <span><b>4A:</b> espirais bem pequenas. <b>4B:</b> curvas em zigue-zague. <b>4C:</b> curvas muito compactas, com pouca definição aparente.</span>
      </div>
    </aside>
    <style>
      /* Exclusivo do cadastro: mantém a identidade visual e evita sobrepor os campos. */
      .dh-register-bubble {
        background:#fffaf6; color:#65473c; border:1.5px solid #d9a791;
        box-shadow:0 10px 26px rgba(82,53,43,.10); box-sizing:border-box;
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        gap:8px; text-align:center; padding:22px;
      }
      .dh-register-bubble strong { font:600 20px/1.2 'Playfair Display',serif; color:#a94427; }
      .dh-register-bubble span { font-size:13px; line-height:1.5; }
      body[data-auth-page="register"] .register-card { z-index:2; }
      @media(min-width:1300px){
        .dh-register-bubble { position:fixed; z-index:1; width:clamp(210px,16vw,250px); height:clamp(210px,16vw,250px); border-radius:50%; }
        .dh-register-left {left:clamp(18px,3vw,55px)}
        .dh-register-right {right:clamp(18px,3vw,55px)}
        .dh-register-top {top:calc(50% - 270px)}
        .dh-register-bottom {top:calc(50% + 20px)}
      }
      @media(max-width:1299px){
        body[data-auth-page="register"] {flex-direction:column;gap:20px;}
        .dh-register-guides {order:2;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));max-width:760px;width:100%;gap:14px;margin:0 auto;}
        .dh-register-bubble {border-radius:22px;min-height:160px;}
        body[data-auth-page="register"] .register-card {order:1;}
      }
      @media(max-width:600px){.dh-register-guides{grid-template-columns:1fr}}
    </style>

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
                    value="<?= e($_POST['name'] ?? '') ? placeholder="Digite seu nome completo">"
                >

            </label>

            <label>

                Email:

                <input
                    name="email"
                    type="email"
                    required
                    value="<?= e($_POST['email'] ?? '') ? placeholder="Digite seu e-mail (ex.: nome@exemplo.com)">"
                >

            </label>

            <label>

                Senha:

                <input
                    name="password"
                    type="password"
                    minlength="6"
                    required
                 placeholder="Crie uma senha com pelo menos 6 caracteres">

            </label>

            <label>

                Confirmar senha:

                <input
                    name="confirm"
                    type="password"
                    minlength="6"
                    required
                 placeholder="Digite a senha novamente">

            </label>

            <label>

                Tipo de cabelo:

                <select name="hair_type" id="register-hair-type" required>

                    <option>Liso</option>
                    <option>Ondulado</option>
                    <option>Cacheado</option>
                    <option>Crespo</option>

                </select>

            </label>

            <label>

                Curvatura:

                <select name="curl_pattern" id="register-curl-pattern" required>
                    <option value="">Selecione a curvatura</option>
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


    <script>
      (() => {
        const type = document.getElementById('register-hair-type');
        const curl = document.getElementById('register-curl-pattern');
        const options = { Liso: ['1A','1B','1C'], Ondulado: ['2A','2B','2C'], Cacheado: ['3A','3B','3C'], Crespo: ['4A','4B','4C'] };
        function updateCurvatures() {
          const previous = curl.value;
          curl.replaceChildren(new Option('Selecione a curvatura', ''));
          for (const item of (options[type.value] || [])) curl.add(new Option(item, item));
          curl.value = (options[type.value] || []).includes(previous) ? previous : '';
        }
        type.addEventListener('change', updateCurvatures);
        updateCurvatures();
      })();
    </script>
    <script type="module" src="firebase-auth.js"></script>
</body>

</html>