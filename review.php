<?php
require_once __DIR__ . '/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

verify_csrf();
// Limite por sessão para reduzir spam automático de avaliações.
if (($_POST['action'] ?? 'create') === 'create') {
    $times = array_values(array_filter($_SESSION['review_submit_times'] ?? [],
        static fn($t) => is_int($t) && $t > time() - 600));
    if (count($times) >= 10) {
        http_response_code(429);
        exit('Muitas avaliações enviadas. Aguarde alguns minutos.');
    }
    $times[] = time();
    $_SESSION['review_submit_times'] = $times;
}
$user_id = (int) $_SESSION['user_id'];
$action = $_POST['action'] ?? 'create';


/* =====================================================
   EXCLUIR A PRÓPRIA AVALIAÇÃO
===================================================== */

if ($action === 'delete') {
    $review_id = (int) ($_POST['review_id'] ?? 0);
    $product_id = (int) ($_POST['product_id'] ?? 0);

    if (!$review_id || !$product_id) {
        header('Location: products.php');
        exit;
    }

    /*
     * A condição user_id = ? é a proteção principal:
     * mesmo que alguém altere o review_id no formulário,
     * só consegue apagar uma avaliação que pertence à própria sessão.
     */
    $stmt = db()->prepare(
        'DELETE FROM reviews
         WHERE id = ? AND user_id = ? AND product_id = ?'
    );

    $stmt->bindValue(1, $review_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(3, $product_id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: product.php?id=' . $product_id . '#avaliar');
    exit;
}


/* =====================================================
   PUBLICAR AVALIAÇÃO
===================================================== */

if ($action !== 'create') {
    header('Location: products.php');
    exit;
}

$product_id = (int) ($_POST['product_id'] ?? 0);
$rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
$comment = trim($_POST['comment'] ?? '');

if (!$product_id || !$comment || mb_strlen($comment) > 3000 || $rating < 1 || $rating > 5) {
    header('Location: product.php?id=' . $product_id . '&error=1#avaliar');
    exit;
}

$stmt = db()->prepare(
    'INSERT INTO reviews(product_id,user_id,rating,comment)
     VALUES(?,?,?,?)'
);

$stmt->bindValue(1, $product_id, PDO::PARAM_INT);
$stmt->bindValue(2, $user_id, PDO::PARAM_INT);
$stmt->bindValue(3, $rating, PDO::PARAM_INT);
$stmt->bindValue(4, $comment, PDO::PARAM_STR);
$stmt->execute();

header('Location: product.php?id=' . $product_id . '#avaliar');
exit;
?>
