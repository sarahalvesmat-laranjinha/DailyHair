DAILY HAIR — PHP + SUPABASE POSTGRESQL

Este branch desenvolvimento usa PostgreSQL via PDO. O Firebase continua
responsável pelo cadastro, login Google e recuperação de senha. Contas antigas
mantêm o login por senha local após a migração dos hashes originais.

REQUISITOS
- PHP 8.1+ com pdo_pgsql, ctype, mbstring e openssl.
- Composer para instalar as dependências Firebase.
- Servidor PHP com conexão TCP ao PostgreSQL do Supabase.

DEPLOY NO RENDER
- A branch `desenvolvimento` já possui Dockerfile, render.yaml e health.php.
- Use o Session Pooler do Supabase na porta 5432, porque o Render não deve usar
  a conexão direta IPv6 do projeto.
- Configure as variáveis privadas somente no Render; não coloque senhas, JSON
  de conta de serviço ou chaves privadas no GitHub.
- Leia RENDER-SETUP.md para a lista completa das variáveis SUPABASE_DB_* e
  FIREBASE_* necessárias.
- O health check de produção é /health.php.

INSTALAÇÃO E MIGRAÇÃO
1. Leia SUPABASE-SETUP.md antes de trocar o banco.
2. Faça backup completo do MySQL e das pastas images/ e uploads/.
3. Configure o destino com variáveis SUPABASE_DB_* ou com o arquivo local
   supabase.config.php, baseado em supabase.config.example.php.
4. Execute composer install e mantenha a configuração Firebase indicada em
   FIREBASE-SETUP.md. Não publique chaves privadas ou senhas no GitHub.
5. O backup real já foi migrado para o Supabase; o script
   scripts/migrate_mysql_to_supabase.php permanece disponível para conferência
   ou futuras migrações controladas.
6. Teste o site no destino antes de liberar novas gravações.

O script de migração copia os dados reais, confere contagens e conteúdo e
preserva IDs, vínculos e hashes de senhas. A tabela antiga password_resets é
ignorada porque a recuperação de senha atual é feita pelo Firebase.

ARQUIVOS PRINCIPAIS
- db.php: conexão PostgreSQL por PDO.
- bootstrap.php: inicialização da sessão PHP.
- supabase/schema.sql: estrutura PostgreSQL privada, sem dados de demonstração.
- scripts/migrate_mysql_to_supabase.php: migração CLI, com simulação por padrão.
- supabase.config.example.php: modelo de configuração sem credenciais reais.
- tests/postgres_smoke.php: validação em PostgreSQL local descartável.
- Dockerfile: imagem PHP/Apache para o Render.
- render.yaml: Blueprint do serviço Render.
- RENDER-SETUP.md: configuração de produção.

FUNCIONALIDADES
Login e cadastro, perfil capilar, produtos, avaliações, sugestões de produtos,
aprovação administrativa e calendário com rotinas personalizadas.

As tabelas principais são users, products, reviews, aprove_products e
wash_routines. As imagens permanecem nas pastas do site; o banco guarda os
caminhos dos arquivos.

Os arquivos SQL na raiz são históricos do MySQL. Não os execute no Supabase.
No deploy Docker, o Apache bloqueia acesso público a arquivos SQL, backups,
documentação interna e pastas técnicas. O schema privado dailyhair não deve ser
exposto pela Data API; a autorização dos usuários continua no backend PHP.
