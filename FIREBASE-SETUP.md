# Firebase Authentication no Daily Hair

Esta integração está na branch `desenvolvimento`. O Firebase autentica por e-mail/senha ou Google; o PHP valida o ID token e mantém a sessão e os perfis no PostgreSQL do Supabase.

1. No [Firebase Console](https://console.firebase.google.com/), crie ou selecione um projeto, registre um aplicativo Web e ative **Authentication > Sign-in method > Email/Password** e **Google**. Em **Authorized domains**, adicione o domínio onde o site roda (e `localhost` para testes locais, se necessário).
2. Copie `firebase-config.example.js` para `firebase-config.js` e preencha com a configuração Web do **mesmo projeto**. Essa configuração é pública, mas o arquivo real fica fora do Git para facilitar a troca de ambiente.
3. Em **Project settings > Service accounts**, gere uma chave privada para a conta de serviço. Guarde o JSON **fora de `htdocs`/`public_html`**, por exemplo em `C:\xampp\firebase-service-account.json`. Configure a variável de ambiente `FIREBASE_CREDENTIALS` com esse caminho no processo do Apache/PHP. Nunca publique o JSON no GitHub nem em diretório servido pela web.
4. Instale PHP 8.1+ com `pdo_pgsql`, `mbstring`, `openssl` e Composer. Na pasta do projeto execute `composer install`. Se o servidor não tiver Composer, execute na sua máquina e envie `vendor/` junto com o site por FTP; não inclua `vendor/` no Git. O pacote PHP usa `kreait/firebase-php` 7.x por compatibilidade com PHP 8.1/8.2.
5. Siga `SUPABASE-SETUP.md` para a conexão e migração. O schema PostgreSQL já inclui `firebase_uid`. Não execute `firebase-migration.sql` no Supabase. A sessão é iniciada por `bootstrap.php`.
6. Publique também `firebase-config.js` e teste: cadastro por e-mail, login por e-mail, login pelo Google, sair e recuperação de senha. Os e-mails de recuperação são enviados pelo Firebase.

Contas antigas que só existem no MySQL continuam acessíveis por **Entrar com conta anterior**. Uma conta antiga pode ser vinculada ao Firebase ao entrar com um Google que tenha o mesmo e-mail verificado. Para migrar senhas locais em massa ao Firebase, é preciso um processo separado; não copie senhas ou hashes manualmente entre os dois sistemas.

**Antes de usar em produção:** configure e teste a conexão Supabase e o Firebase no servidor conforme `SUPABASE-SETUP.md`.
