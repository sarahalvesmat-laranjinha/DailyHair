# DailyHair: MySQL para Supabase

O aplicativo usa PHP/PDO com PostgreSQL e SSL. O schema `dailyhair` é privado e não deve ser adicionado à lista de schemas expostos pela Data API. O Firebase continua responsável pelo cadastro, Google e recuperação de senha; o login com senha antiga continua usando os hashes originais. Não é necessário recriar as contas no Supabase Auth.

## 1. Preparação

- Faça backup completo do MySQL (estrutura e dados) no phpMyAdmin: selecione o banco correto > Exportar > Personalizado > todas as tabelas > SQL. Guarde fora do diretório público do site e do GitHub.
- Faça também backup das pastas `images/` e `uploads/`: o banco contém os caminhos, não os arquivos das fotos.
- Não execute novamente os antigos arquivos `*.sql` da raiz: eles são históricos do MySQL e não são uma cópia atual do banco. Não importe esses arquivos no SQL Editor do Supabase.
- Instale PHP 8.1+ com `pdo_pgsql`, `mbstring`, `openssl` e as dependências de `composer install`. Na máquina que fará a migração, habilite também `pdo_mysql`.
- O servidor PHP precisa permitir conexão TCP PostgreSQL de saída. No XAMPP, habilite `extension=pdo_pgsql` e, para migrar, `extension=pdo_mysql` no php.ini e reinicie o Apache. Se o host não oferecer essas extensões/conexões, será preciso hospedar o PHP em outro servidor.

## 2. Configuração do destino

No projeto correto do Supabase, abra **Connect > Session pooler > View parameters**. Copie host, porta, database, user e a senha do banco. A chave anon/service_role não serve como senha PostgreSQL.

Copie `supabase.config.example.php` para `supabase.config.php` e preencha somente o arquivo local, ou defina `SUPABASE_DB_HOST`, `SUPABASE_DB_PORT`, `SUPABASE_DB_NAME`, `SUPABASE_DB_USER`, `SUPABASE_DB_PASSWORD`, `SUPABASE_DB_SSLMODE` e `SUPABASE_DB_SCHEMA` no ambiente do servidor. Variáveis de ambiente têm prioridade. O arquivo `.env` não é carregado automaticamente.

Use `require` para SSL (padrão) e `dailyhair` como schema. Para verificar o certificado, use `verify-full` e configure o certificado raiz PostgreSQL conforme a documentação do Supabase. Prefira a conexão direta ou Session pooler, não Transaction pooler. A conta PostgreSQL usada pelo PHP deve ser proprietária das tabelas do schema: as sessões são autorizadas pelo backend, e RLS bloqueia acesso direto sem políticas.

O aplicativo não carrega mais o antigo `config.php`. A sessão é iniciada por `bootstrap.php`. Mantenha a configuração e o projeto Firebase existentes. Se o antigo config.php definia timezone ou outros ajustes do site, transfira esses ajustes para a configuração do PHP.

## 3. Simular e migrar sem apagar o MySQL

Execute na máquina que consegue acessar o MySQL atual (por exemplo, seu XAMPP). Para um host que bloqueia MySQL externo, importe o backup completo em um MySQL local separado e use essa cópia como origem. Não use os seeds antigos como substituto do backup.

Defina no ambiente do terminal:

| Variável | Valor |
|---|---|
| `MYSQL_SOURCE_HOST` | Host do MySQL atual; padrão `127.0.0.1` |
| `MYSQL_SOURCE_PORT` | Porta atual, por exemplo `3306` ou `3307` |
| `MYSQL_SOURCE_DATABASE` | Nome exato do banco atual |
| `MYSQL_SOURCE_USER` | Usuário com permissão de leitura |
| `MYSQL_SOURCE_PASSWORD` | Senha do MySQL; pode ser vazia em um ambiente local |

No PowerShell, a sintaxe é `$env:NOME_DA_VARIAVEL = 'valor'`. Não copie senhas para código versionado.

Primeiro, simule:

```sh
php scripts/migrate_mysql_to_supabase.php
```

O script cria a estrutura numa transação, copia todas as colunas das tabelas conhecidas, compara contagem e SHA-256 de todo o conteúdo lido novamente no destino, e desfaz a simulação. Preserva IDs, hashes de senhas, Firebase UIDs, imagens, perfis, produtos, avaliações, solicitações e rotinas. Reajusta as sequências para que novos cadastros não colidam com IDs antigos.

Se houver tabela/coluna adicional, tipo incompatível, data inválida, e-mail duplicado sem distinção de maiúsculas ou vínculo órfão, o script interrompe e desfaz a transação. Não elimina, corrige nem ignora registros silenciosamente. Amplie `supabase/schema.sql` e o mapeamento do script para qualquer estrutura adicional antes de continuar. A origem deve usar InnoDB; views e outros motores exigem migração específica. Campos conhecidos usam `text` para evitar truncamento por tamanho.

Para a passagem final, pare as gravações no site antigo (manutenção), faça um novo backup completo e execute:

```sh
php scripts/migrate_mysql_to_supabase.php --apply --backup=/caminho/backup-completo.sql
```

No Windows, use por exemplo `--backup=C:/backups/dailyhair.sql`. O script exige um arquivo não vazio e registra seu hash; isso não substitui a verificação de que o backup corresponde ao banco correto e pode ser restaurado. O destino deve estar vazio. Uma segunda execução após sucesso é recusada, evitando duplicações. O schema é criado automaticamente; não é obrigatório executar SQL manualmente.

## 4. Conferência antes de liberar o site

Configure o PHP de desenvolvimento para esse destino e teste: login antigo, login Google, cadastro, perfil/foto, produtos/avaliações, aprovação administrativa e gravação/ordem da rotina. Confira quantidades e amostras no SQL Editor, por exemplo `SELECT count(*) FROM dailyhair.users;`. O script não transforma hashes de senhas nem importa contas para Supabase Auth.

Apenas depois da validação, aponte o site para o Supabase e libere as gravações. Preserve o MySQL e o backup. Se precisar voltar antes de haver novas gravações, use o código e a configuração MySQL anteriores. Depois de novas gravações no Supabase, faça backup e reconcilie esses dados antes de voltar ao MySQL, para não perder alterações recentes.

O schema vazio pode ser criado isoladamente com `supabase/schema.sql`, mas não contém catálogo ou usuários. A migração transfere os dados reais. As imagens continuam no armazenamento de arquivos existente; esta alteração não move arquivos para Supabase Storage.

Referências oficiais: [conexões PostgreSQL](https://supabase.com/docs/guides/database/connecting-to-postgres) e [migração MySQL](https://supabase.com/docs/guides/platform/migrating-to-supabase/mysql).

## Testes de desenvolvimento

`tests/postgres_smoke.php` verifica o login com hash local, acentos, IDs retornados, agregações de avaliações e prepara com `EXPLAIN` todas as consultas SQL literais do aplicativo. Execute apenas em PostgreSQL descartável local, com `DAILYHAIR_TEST=1` e `SUPABASE_DB_HOST=127.0.0.1`; requer a extensão PHP `tokenizer`. O teste usa uma transação e desfaz suas alterações.

Nesta adaptação, foram verificados PHP 8.3, 28 consultas, e a migração de fixtures MariaDB para PostgreSQL/PGlite: simulação, conteúdo completo, rollback em falha, rejeição de campos não mapeados, rejeição de destino populado e sequência do próximo ID. Isso não substitui o teste no projeto Supabase real nem valida a configuração externa do Firebase.
