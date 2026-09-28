<?php
require_once __DIR__ . '/bootstrap.php';

function database_config(): array
{
    static $config = null;
    if ($config === null) {
        $path = __DIR__ . '/supabase.config.php';
        $config = is_file($path) ? require $path : [];
        if (!is_array($config)) {
            throw new RuntimeException('supabase.config.php deve retornar um array.');
        }
    }
    return $config;
}

function database_setting(string $name, string $default = ''): string
{
    $value = getenv($name);
    return $value !== false ? $value : (string) (database_config()[$name] ?? $default);
}

function db(): PDO
{
    static $conn = null;
    if ($conn instanceof PDO) {
        return $conn;
    }
    $host = database_setting('SUPABASE_DB_HOST');
    $user = database_setting('SUPABASE_DB_USER');
    $password = database_setting('SUPABASE_DB_PASSWORD');
    $port = database_setting('SUPABASE_DB_PORT', '5432');
    $name = database_setting('SUPABASE_DB_NAME', 'postgres');
    $ssl = database_setting('SUPABASE_DB_SSLMODE', 'require');
    $schema = database_setting('SUPABASE_DB_SCHEMA', 'dailyhair');
    if ($host === '' || $user === '' || $password === '') {
        throw new RuntimeException('Configure a conexão PostgreSQL em SUPABASE_DB_* ou supabase.config.php.');
    }
    if (!ctype_digit($port) || !preg_match('/^[a-z_][a-z0-9_]*$/D', $schema)
        || !in_array($ssl, ['require', 'verify-ca', 'verify-full', 'disable'], true)
        || strpbrk($host . $name, ";\r\n") !== false) {
        throw new RuntimeException('Configuração PostgreSQL inválida.');
    }
    // disable é permitido somente para testes locais, nunca para o Supabase remoto.
    if ($ssl === 'disable' && !in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
        throw new RuntimeException('SSL é obrigatório para conexões remotas.');
    }
    try {
        $conn = new PDO("pgsql:host=$host;port=$port;dbname=$name;sslmode=$ssl;connect_timeout=10", $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $conn->exec('SET search_path TO "' . $schema . '"');
        $conn->exec("SET TIME ZONE 'UTC'");
        return $conn;
    } catch (PDOException $e) {
        // Não expõe senha, endereço do banco ou dados SQL na resposta HTTP.
        error_log('DailyHair: falha na conexão PostgreSQL. SQLSTATE ' . $e->getCode());
        throw new RuntimeException('Não foi possível conectar ao banco. Confira a configuração do servidor.');
    }
}
