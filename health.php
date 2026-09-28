<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$required = [
    'SUPABASE_DB_HOST',
    'SUPABASE_DB_USER',
    'SUPABASE_DB_PASSWORD',
];

$missing = array_values(array_filter(
    $required,
    static fn(string $key): bool => (getenv($key) ?: '') === ''
));

if ($missing !== []) {
    http_response_code(200);
    echo json_encode([
        'ok' => true,
        'database' => 'configuration_pending',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require_once __DIR__ . '/db.php';
    db()->query('SELECT 1');
    http_response_code(200);
    echo json_encode([
        'ok' => true,
        'database' => 'connected',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'database' => 'unavailable',
    ], JSON_UNESCAPED_UNICODE);
}
