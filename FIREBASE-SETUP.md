# Firebase Authentication no Daily Hair

Esta integração está na branch `desenvolvimento`. O Firebase autentica por e-mail/senha ou Google; o PHP valida o ID token e mantém a sessão e os perfis no PostgreSQL do Supabase.

## Produção no Render

1. No Firebase Console, crie ou selecione um projeto, registre um aplicativo Web e ative **Authentication > Sign-in method > Email/Password** e **Google**.
2. Em **Project settings > General > Your apps > Web app**, copie os valores do aplicativo Web e configure no Render:
   - `FIREBASE_WEB_API_KEY`
   - `FIREBASE_WEB_AUTH_DOMAIN`
   - `FIREBASE_WEB_PROJECT_ID`
   - `FIREBASE_WEB_APP_ID`
3. O arquivo `firebase-config.php` gera a configuração Web em tempo de execução. Em produção não é necessário criar `firebase-config.js`.
4. Em **Project settings > Service accounts**, gere uma chave privada JSON da conta de serviço e salve o JSON inteiro no Render como `FIREBASE_SERVICE_ACCOUNT_JSON`. Nunca publique esse JSON no GitHub.
5. Depois do primeiro deploy, adicione o hostname `*.onrender.com` específico do seu serviço em **Authentication > Settings > Authorized domains**.
6. Siga `SUPABASE-SETUP.md` e `RENDER-SETUP.md` para a conexão PostgreSQL e o deploy.

## Desenvolvimento local

O backend também aceita `FIREBASE_CREDENTIALS` apontando para um arquivo JSON local fora da pasta pública. Para o frontend, você pode definir as variáveis `FIREBASE_WEB_*` no ambiente do Apache/PHP.

Instale PHP 8.1+ com `pdo_pgsql`, `mbstring`, `openssl` e Composer. Na pasta do projeto execute `composer install`.

## Contas antigas

Contas antigas que vieram do MySQL continuam acessíveis por **Entrar com conta anterior** usando o hash de senha preservado no Supabase. Uma conta antiga pode ser vinculada ao Firebase ao entrar com um Google que tenha o mesmo e-mail verificado.

Os e-mails de recuperação de senha são enviados pelo Firebase. A tabela antiga `password_resets` não é necessária para esse fluxo.

Nunca copie senhas, hashes, chaves privadas ou JSON de conta de serviço para o GitHub.
