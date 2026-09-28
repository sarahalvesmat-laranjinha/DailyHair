# Deploy do DailyHair no Render

A branch `desenvolvimento` já está preparada para deploy com Docker no Render.

## Arquivos de deploy

- `Dockerfile`: PHP 8.2 + Apache + PDO PostgreSQL + Composer.
- `render-entrypoint.sh`: faz o Apache escutar a porta definida por `PORT`.
- `render.yaml`: Blueprint do Web Service.
- `health.php`: health check que valida PHP e a conexão PostgreSQL.
- `firebase-config.php`: entrega ao navegador apenas a configuração Web pública do Firebase a partir das variáveis de ambiente.

## Supabase

O Render não deve usar a conexão direta IPv6 do banco. No painel do Supabase, abra **Connect** e selecione **Session pooler**.

Configure no Render:

- `SUPABASE_DB_HOST`: host do Session Pooler, no formato fornecido pelo Supabase.
- `SUPABASE_DB_USER`: usuário do Session Pooler. Normalmente inclui o project ref.
- `SUPABASE_DB_PASSWORD`: senha do banco PostgreSQL.
- `SUPABASE_DB_PORT`: `5432`.
- `SUPABASE_DB_NAME`: `postgres`.
- `SUPABASE_DB_SSLMODE`: `require`.
- `SUPABASE_DB_SCHEMA`: `dailyhair`.

Nunca coloque a senha do banco em arquivos do GitHub.

## Firebase Web

No Firebase Console, em **Project settings > General > Your apps > Web app**, copie os valores correspondentes e configure no Render:

- `FIREBASE_WEB_API_KEY`
- `FIREBASE_WEB_AUTH_DOMAIN`
- `FIREBASE_WEB_PROJECT_ID`
- `FIREBASE_WEB_APP_ID`

O arquivo `firebase-config.php` gera o módulo JavaScript em tempo de execução. Não é necessário criar `firebase-config.js` em produção.

## Firebase Admin / backend PHP

Em **Firebase Console > Project settings > Service accounts**, gere uma chave privada JSON para a conta de serviço.

No Render, salve o JSON inteiro como variável secreta:

- `FIREBASE_SERVICE_ACCOUNT_JSON`

O código também aceita `FIREBASE_CREDENTIALS` apontando para um arquivo local/Secret File, mas no Blueprint atual a opção usada é `FIREBASE_SERVICE_ACCOUNT_JSON`.

Nunca coloque o JSON da conta de serviço no GitHub.

## Firebase Authorized domains

Depois que o Render gerar o endereço `https://...onrender.com`, adicione apenas o hostname em:

**Firebase Console > Authentication > Settings > Authorized domains**

Exemplo: `dailyhair.onrender.com`.

## Criar o serviço

No Render, crie um Blueprint/Web Service usando o repositório DailyHair e a branch `desenvolvimento`. O arquivo `render.yaml` define o serviço Docker e pedirá os valores secretos marcados com `sync: false`.

O health check configurado é `/health.php`. Ele deve responder HTTP 200 quando a conexão com o Supabase estiver correta.

## Antes de liberar

Teste:

1. página inicial;
2. login de conta antiga;
3. cadastro pelo Firebase;
4. login por e-mail/senha Firebase;
5. login Google;
6. recuperação de senha;
7. produtos e avaliações;
8. perfil;
9. calendário;
10. criação de novos registros para confirmar que as sequências PostgreSQL continuam corretas.
