<?php

require_once __DIR__ . '/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: product-form.php');
    exit;
}

$conn = db();
$user = current_user();
$created_by = (int) $user['id'];

$name = trim($_POST['name'] ?? '');
$brand = trim($_POST['brand'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');

$errors = [];

if ($name === '') {
    $errors[] = 'O nome do produto é obrigatório.';
}

if ($brand === '') {
    $errors[] = 'A marca é obrigatória.';
}

if ($category === '') {
    $errors[] = 'A categoria é obrigatória.';
}

if ($description === '') {
    $errors[] = 'A descrição é obrigatória.';
}

$categorias_permitidas = [
    'Nutrição',
    'Hidratação',
    'Reconstrução'
];

if (!in_array($category, $categorias_permitidas, true)) {
    $errors[] = 'Categoria inválida.';
}

if (!empty($errors)) {
    header(
        'Location: product-form.php?error=' .
        urlencode(implode(' ', $errors))
    );
    exit;
}

$image_name = null;

if (!empty($_FILES['image']['name'])) {
    try {
        $image_name = uploaded_product_image_to_data_url($_FILES['image']);
    } catch (RuntimeException $e) {
        header(
            'Location: product-form.php?error=' .
            urlencode($e->getMessage())
        );
        exit;
    }
}

$stmt = $conn->prepare("
    INSERT INTO aprove_products
    (
        name,
        brand,
        category,
        description,
        image,
        created_by,
        status
    )
    VALUES (?, ?, ?, ?, ?, ?, 'Em análise')
");

if (!$stmt) {
    die('Erro ao preparar cadastro: Consulte o log do servidor.');
}

$stmt->bindValue(1, $name, PDO::PARAM_STR);
$stmt->bindValue(2, $brand, PDO::PARAM_STR);
$stmt->bindValue(3, $category, PDO::PARAM_STR);
$stmt->bindValue(4, $description, PDO::PARAM_STR);
$stmt->bindValue(5, $image_name, PDO::PARAM_STR);
$stmt->bindValue(6, $created_by, PDO::PARAM_INT);

if (!$stmt->execute()) {
    die('Erro ao cadastrar produto: Consulte o log do servidor.');
}

header('Location: product-form.php?sent=1');
exit;
