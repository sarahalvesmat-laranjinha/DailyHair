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

    $allowed_ext = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    $ext = strtolower(
        pathinfo(
            $_FILES['image']['name'],
            PATHINFO_EXTENSION
        )
    );

    if (!in_array($ext, $allowed_ext, true)) {

        header(
            'Location: product-form.php'
            . ($id ? "?id=$id" : '')
            . '&error='
            . urlencode(
                'Formato de imagem inválido. Use jpg ou png.'
            )
        );

        exit;
    }

    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {

        header(
            'Location: product-form.php'
            . ($id ? "?id=$id" : '')
            . '&error='
            . urlencode(
                'Imagem muito grande. Máximo de 5MB.'
            )
        );

        exit;
    }

    $image_name = uniqid('prod_', true) . '.' . $ext;

    $destination = __DIR__ . '/images/' . $image_name;

    if (
        !move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $destination
        )
    ) {

        header(
            'Location: product-form.php'
            . ($id ? "?id=$id" : '')
            . '&error='
            . urlencode(
                'Falha ao enviar a imagem. Tente novamente.'
            )
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

$stmt->bind_param(
    'sssssi',
    $name,
    $brand,
    $category,
    $description,
    $image_name,
    $id
);

if (!$stmt->execute()) {
    die('Erro ao atualizar produto: ' . $stmt->error);
}

header('Location: products.php');

exit;