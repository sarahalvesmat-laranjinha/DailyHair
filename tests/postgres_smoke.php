<?php
// Execute apenas contra um PostgreSQL LOCAL de teste vazio.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/db.php';
if (getenv('DAILYHAIR_TEST') !== '1' || database_setting('SUPABASE_DB_HOST') !== '127.0.0.1') {
    throw new RuntimeException('Use DAILYHAIR_TEST=1 e um PostgreSQL local descartável.');
}
$conn = db();
$conn->beginTransaction();
try {
    $conn->exec(file_get_contents(dirname(__DIR__) . '/supabase/schema.sql'));
    $password = password_hash('senha-de-teste', PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?) RETURNING id');
    $stmt->execute(['Teste João', 'smoke@example.invalid', $password]);
    $userId = (int) $stmt->fetchColumn();
    $stmt = $conn->prepare('SELECT password FROM users WHERE lower(email)=lower(?)');
    $stmt->execute(['SMOKE@example.invalid']);
    if (!password_verify('senha-de-teste', $stmt->fetchColumn())) throw new RuntimeException('Login antigo falhou.');
    $stmt = $conn->prepare('INSERT INTO products(name,brand,category,description) VALUES(?,?,?,?) RETURNING id');
    $stmt->execute(['Máscara', 'Teste', 'Hidratação', 'Acentos e apóstrofo: d’água']);
    $productId = (int) $stmt->fetchColumn();
    $stmt = $conn->prepare('INSERT INTO reviews(product_id,user_id,rating,comment) VALUES(?,?,?,?)');
    $stmt->execute([$productId, $userId, 5, 'Ótimo']);
    $average = $conn->query('SELECT p.id, AVG(r.rating) AS average FROM products p JOIN reviews r ON r.product_id=p.id GROUP BY p.id')->fetch();
    if ((float) $average['average'] !== 5.0) throw new RuntimeException('Agregação falhou.');

    // Valida todas as consultas literais reais do app com o parser/planner PostgreSQL.
    $count = 0;
    foreach (glob(dirname(__DIR__) . '/*.php') as $path) {
        $tokens = token_get_all(file_get_contents($path));
        foreach ($tokens as $i => $token) {
            if (!is_array($token) || $token[0] !== T_STRING || !in_array($token[1], ['prepare', 'query'], true)) continue;
            $j = $i + 1;
            while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
            if (($tokens[$j++] ?? null) !== '(') continue;
            while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
            if (!is_array($tokens[$j] ?? null) || $tokens[$j][0] !== T_CONSTANT_ENCAPSED_STRING) continue;
            // Apenas um token literal PHP, sem interpolação nem código executável.
            $sql = eval('return ' . $tokens[$j][1] . ';');
            $explain = $conn->prepare('EXPLAIN ' . $sql);
            $explain->execute(array_fill(0, substr_count($sql, '?'), '1'));
            $explain->fetchAll();
            $count++;
        }
    }
    $conn->rollBack();
    echo "OK: login antigo, UTF-8, INSERT RETURNING, avaliações e $count consultas reais do aplicativo. Transação desfeita.\n";
} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    throw $e;
}
