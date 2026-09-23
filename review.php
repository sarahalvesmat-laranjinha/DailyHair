<?php
require_once __DIR__ . '/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}
$product_id = (int) ($_POST['product_id'] ?? 0);
$rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
$comment = trim($_POST['comment'] ?? '');
$user_id = (int) $_SESSION['user_id'];
if (!$product_id || !$comment) {
    header('Location: product.php?id=' . $product_id . '&error=1#avaliar');
    exit;
}
$stmt = db()->prepare('INSERT INTO reviews(product_id,user_id,rating,comment) VALUES(?,?,?,?)');
$stmt->bind_param('iiis', $product_id, $user_id, $rating, $comment);
$stmt->execute();
header('Location: product.php?id=' . $product_id . '#avaliar');
exit;
?>