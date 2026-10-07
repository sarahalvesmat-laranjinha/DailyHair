<?php

require_once __DIR__ . '/auth.php';

require_login();

$user = current_user();

if (!$user || empty($user['is_admin'])) {
    http_response_code(403);
    exit('Acesso negado.');
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: products.php');

    exit;
}

$conn = db();

$id = !empty($_POST['id']) ? (int) $_POST['id'] : null;

$name = trim($_POST['name'] ?? '');

$brand = trim($_POST['brand'] ?? '');

$category = trim($_POST['category'] ?? '');

$description = trim($_POST['description'] ?? '');

$current_image = $_POST['current_image'] ?? '';


// -------- Validação básica --------

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

if (!empty($errors)) {

    $msg = implode(' ', $errors);

    header(
        'Location: product-form.php'
        . ($id ? "?id=$id" : '')
        . '&error='
        . urlencode($msg)
    );

    exit;
}


// -------- Upload de imagem (opcional) --------

$image_name = $current_image;

if (!empty($_FILES['image']['name'])) {
    try {
        $image_name = uploaded_product_image_to_data_url($_FILES['image']);
    } catch (RuntimeException $e) {
        header(
            'Location: product-form.php'
            . ($id ? "?id=$id" : '')
            . '&error='
            . urlencode($e->getMessage())
        );
        exit;
    }
}


// -------- Atualizar produto existente --------

if (!$id) {
    header('Location: products.php');
    exit;
}

$stmt = $conn->prepare(
    'UPDATE products
     SET name = ?, brand = ?, category = ?, description = ?, image = ?
     WHERE id = ?'
);

$stmt->bindValue(1, $name, PDO::PARAM_STR);
$stmt->bindValue(2, $brand, PDO::PARAM_STR);
$stmt->bindValue(3, $category, PDO::PARAM_STR);
$stmt->bindValue(4, $description, PDO::PARAM_STR);
$stmt->bindValue(5, $image_name, PDO::PARAM_STR);
$stmt->bindValue(6, $id, PDO::PARAM_INT);

if (!$stmt->execute()) {
    die('Erro ao atualizar produto: ' . 'Consulte o log do servidor.');
}

header('Location: products.php');

exit;
