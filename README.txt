DAILY HAIR — PHP + SUPABASE POSTGRESQL

Este branch desenvolvimento usa PostgreSQL via PDO. O Firebase continua
responsável pelo cadastro, login Google e recuperação de senha. Contas antigas
mantêm o login por senha local após a migração dos hashes originais.

REQUISITOS
- PHP 8.1+ com pdo_pgsql, ctype, mbstring e openssl.
- Composer para instalar as dependências Firebase.
- Para a migração: pdo_mysql e acesso ao MySQL atual ou a uma cópia completa.
- Servidor PHP com conexão TCP ao PostgreSQL do Supabase.

INSTALAÇÃO E MIGRAÇÃO
1. Leia SUPABASE-SETUP.md antes de trocar o banco.
2. Faça backup completo do MySQL e das pastas images/ e uploads/.
3. Configure o destino com variáveis SUPABASE_DB_* ou com o arquivo local
   supabase.config.php, baseado em supabase.config.example.php.
4. Execute composer install e mantenha a configuração Firebase indicada em
   FIREBASE-SETUP.md. Não publique chaves privadas ou senhas no GitHub.
5. Execute a simulação de scripts/migrate_mysql_to_supabase.php. Confira o
   resultado antes de aplicar, com o site antigo em manutenção.
6. Teste o site no destino antes de liberar novas gravações.

O script copia os dados reais, confere contagens e conteúdo e preserva IDs,
vínculos, hashes de senhas e Firebase UIDs. Não altera o MySQL. Destinos com
registros e estruturas de origem não mapeadas são recusados, sem descarte
silencioso. Consulte SUPABASE-SETUP.md para comandos e limitações.

ARQUIVOS PRINCIPAIS
- db.php: conexão PostgreSQL por PDO.
- bootstrap.php: inicialização da sessão PHP.
- supabase/schema.sql: estrutura PostgreSQL privada, sem dados de demonstração.
- scripts/migrate_mysql_to_supabase.php: migração CLI, com simulação por padrão.
- supabase.config.example.php: modelo de configuração sem credenciais reais.
- tests/postgres_smoke.php: validação em PostgreSQL local descartável.

FUNCIONALIDADES
Login e cadastro, perfil capilar, produtos, avaliações, sugestões de produtos,
aprovação administrativa e calendário com rotinas personalizadas.

As tabelas principais são users, products, reviews, aprove_products e
wash_routines. As imagens permanecem nas pastas do site; o banco guarda os
caminhos dos arquivos.

Os arquivos SQL na raiz são históricos do MySQL. Não os execute no Supabase
nem os use como substituto de um backup atual. O config.php antigo não é
carregado pelo aplicativo. O schema privado dailyhair não deve ser exposto
pela Data API; a autorização dos usuários continua no backend PHP.
