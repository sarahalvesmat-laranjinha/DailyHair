<?php
/** CLI only. Reads MySQL; inserts into an empty dailyhair schema atomically. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/db.php';

function identifier(string $name, string $quote = '"'): string
{
    return $quote . str_replace($quote, $quote . $quote, $name) . $quote;
}
function migration_env(string $name, ?string $default = null): string
{
    $value = getenv($name);
    if ($value === false) {
        if ($default !== null) return $default;
        throw new RuntimeException("Configure $name no ambiente (não no GitHub).");
    }
    return $value;
}
function row_digest(array $row): string
{
    // Compara NULL, string vazia, acentos e números sem depender do tipo do driver.
    return json_encode(array_map(static fn($v) => $v === null ? null : (string) $v, array_values($row)), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
}

$options = getopt('', ['apply', 'backup:', 'help']);
if (isset($options['help'])) {
    echo "Uso: php scripts/migrate_mysql_to_supabase.php [--apply --backup=/caminho/backup.sql]\nSem --apply, copia e verifica em uma transação, depois desfaz tudo.\n";
    exit(0);
}
$apply = isset($options['apply']);
$source = $target = null;
try {
    if ($apply) {
        $backup = $options['backup'] ?? '';
        if (!is_file($backup) || !is_readable($backup) || filesize($backup) === 0) {
            throw new RuntimeException('Antes de aplicar, faça um backup MySQL completo e informe --backup=/caminho/backup.sql.');
        }
        echo 'Backup informado, SHA-256: ' . hash_file('sha256', $backup) . "\n";
    }
    $host = migration_env('MYSQL_SOURCE_HOST', '127.0.0.1');
    $port = migration_env('MYSQL_SOURCE_PORT', '3306');
    $name = migration_env('MYSQL_SOURCE_DATABASE');
    if (strpbrk($host . $name, ";\r\n") !== false || !ctype_digit($port)) {
        throw new RuntimeException('Configuração da origem inválida.');
    }
    $source = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", migration_env('MYSQL_SOURCE_USER'), migration_env('MYSQL_SOURCE_PASSWORD', ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $source->exec("SET time_zone = '+00:00'");
    $source->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    $source->exec('SET TRANSACTION READ ONLY');
    $source->beginTransaction();

    $tables = ['users', 'products', 'reviews', 'aprove_products', 'wash_routines'];
    // A recuperação de senha passou a ser feita pelo Firebase; esta tabela antiga não é migrada.
    $ignoredTables = ['password_resets'];

    $inventory = $source->query('SELECT TABLE_NAME, TABLE_TYPE, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->fetchAll();
    $present = [];
    foreach ($inventory as $table) {
        $tableName = $table['TABLE_NAME'];

        if (in_array($tableName, $ignoredTables, true)) {
            echo "$tableName: ignorada; a recuperação de senha usa Firebase.\n";
            continue;
        }

        if ($table['TABLE_TYPE'] !== 'BASE TABLE' || $table['ENGINE'] !== 'InnoDB') {
            throw new RuntimeException("Objeto não suportado: $tableName. Migre-o explicitamente antes de prosseguir; nada será omitido.");
        }
        $present[] = $tableName;
    }
    $unknown = array_diff($present, $tables);
    if ($unknown) {
        throw new RuntimeException('Tabelas adicionais exigem mapeamento antes da migração: ' . implode(', ', $unknown));
    }
    foreach (['users', 'products', 'reviews'] as $required) {
        if (!in_array($required, $present, true)) throw new RuntimeException("Tabela obrigatória ausente: $required.");
    }
    if (database_setting('SUPABASE_DB_SCHEMA', 'dailyhair') !== 'dailyhair') {
        throw new RuntimeException('A migração usa o schema dailyhair; configure SUPABASE_DB_SCHEMA=dailyhair.');
    }
    $target = db();
    $target->beginTransaction();
    $target->exec(file_get_contents(dirname(__DIR__) . '/supabase/schema.sql'));
    // Recusa o destino populado, inclusive tabelas não presentes na origem.
    foreach ($tables as $table) {
        $target->exec('LOCK TABLE ' . identifier($table) . ' IN ACCESS EXCLUSIVE MODE');
        if ((int) $target->query('SELECT COUNT(*) FROM ' . identifier($table))->fetchColumn() !== 0) {
            throw new RuntimeException("Destino contém registros em $table. Use um destino vazio; não há sobrescrita nem deduplicação automática.");
        }
    }
    foreach ($tables as $table) {
        if (!in_array($table, $present, true)) {
            echo "$table: ausente na origem; estrutura vazia criada.\n";
            continue;
        }
        $columns = $source->query('SHOW COLUMNS FROM ' . identifier($table, '`'))->fetchAll();
        $columnNames = array_column($columns, 'Field');
        $targetColumns = $target->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = 'dailyhair' AND table_name = ?");
        $targetColumns->execute([$table]);
        $unknownColumns = array_diff($columnNames, $targetColumns->fetchAll(PDO::FETCH_COLUMN));
        if ($unknownColumns) throw new RuntimeException("Colunas adicionais em $table exigem mapeamento: " . implode(', ', $unknownColumns));
        if (!in_array('id', $columnNames, true)) throw new RuntimeException("Falta a chave id em $table.");
        $mysqlNames = implode(', ', array_map(static fn($c) => identifier($c, '`'), $columnNames));
        $pgNames = implode(', ', array_map('identifier', $columnNames));
        $sourceRows = $source->query('SELECT ' . $mysqlNames . ' FROM ' . identifier($table, '`') . ' ORDER BY id');
        $insert = $target->prepare('INSERT INTO ' . identifier($table) . " ($pgNames) VALUES (" . implode(', ', array_fill(0, count($columnNames), '?')) . ')');
        $count = 0;
        $sourceHash = hash_init('sha256');
        while ($row = $sourceRows->fetch()) {
            hash_update($sourceHash, row_digest($row) . "\n");
            $insert->execute(array_values($row));
            $count++;
        }
        $sourceRows->closeCursor();
        $readBack = $target->query('SELECT ' . $pgNames . ' FROM ' . identifier($table) . ' ORDER BY id');
        $targetHash = hash_init('sha256');
        $copied = 0;
        while ($row = $readBack->fetch()) {
            hash_update($targetHash, row_digest($row) . "\n");
            $copied++;
        }
        $readBack->closeCursor();
        if ($count !== $copied || !hash_equals(hash_final($sourceHash), hash_final($targetHash))) {
            throw new RuntimeException("A conferência de conteúdo falhou em $table. A transação será desfeita.");
        }
        // ALTER RESTART é transacional, inclusive durante a simulação.
        $sequence = $target->query("SELECT pg_get_serial_sequence('dailyhair.$table', 'id')")->fetchColumn();
        $nextId = $target->query('SELECT GREATEST(COALESCE(MAX(id), 0) + 1, 1) FROM ' . identifier($table))->fetchColumn();
        $sequenceParts = explode('.', $sequence);
        $target->exec('ALTER SEQUENCE ' . implode('.', array_map('identifier', $sequenceParts)) . ' RESTART WITH ' . $nextId);
        echo "$table: $count registros; conteúdo e IDs conferidos.\n";
    }
    $source->rollBack(); // Origem permanece somente leitura.
    if ($apply) {
        $target->commit();
        echo "Migração concluída. MySQL preservado. Teste o site antes de liberar novas gravações.\n";
    } else {
        $target->rollBack();
        echo "Simulação aprovada. Nada foi salvo no destino. Para aplicar, use --apply --backup=...\n";
    }
} catch (Throwable $e) {
    foreach ([$target, $source] as $connection) {
        if ($connection instanceof PDO && $connection->inTransaction()) {
            try {
                $connection->rollBack();
            } catch (Throwable $rollbackError) {
                // A conexão pode ter caído: encerrar o processo fecha a sessão
                // e o servidor desfaz qualquer transação ainda não confirmada.
                error_log('Falha ao confirmar rollback; a conexão será encerrada.');
            }
        }
    }
    // PDO pode incluir dados pessoais/valores no erro. Mostre apenas SQLSTATE.
    $message = $e instanceof PDOException ? 'Falha SQL (' . $e->getCode() . '). Confira estrutura, vínculos, datas, permissões e o estado do destino antes de tentar novamente.' : $e->getMessage();
    fwrite(STDERR, "Migração interrompida: $message\n");
    exit(1);
}
