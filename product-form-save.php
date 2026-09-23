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


// =====================================================
// VALIDAÇÃO
// =====================================================

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


// Apenas estas categorias são permitidas
$categorias_permitidas = [
    'Nutrição',
    'Hidratação',
    'Reconstrução'
];

if (!in_array($category, $categorias_permitidas, true)) {
    $errors[] = 'Categoria inválida.';
}


if (!empty($errors)) {

    $msg = implode(' ', $errors);

    header(
        'Location: product-form.php?error=' . urlencode($msg)
    );

    exit;
}


// =====================================================
// UPLOAD DA IMAGEM
// =====================================================

$image_name = null;

if (!empty($_FILES['image']['name'])) {

    $allowed_ext = [
        'jpg',
        'jpeg',
        'png'
    ];

    $ext = strtolower(
        pathinfo(
            $_FILES['image']['name'],
            PATHINFO_EXTENSION
        )
    );


    // Verifica extensão
    if (!in_array($ext, $allowed_ext, true)) {

        header(
            'Location: product-form.php?error=' .
            urlencode(
                'Formato de imagem inválido. Use JPG, PNG ou WEBP.'
            )
        );

        exit;
    }


    // Verifica tamanho máximo de 5MB
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {

        header(
            'Location: product-form.php?error=' .
            urlencode(
                'Imagem muito grande. Máximo de 5MB.'
            )
        );

        exit;
    }


    // Cria nome único para a imagem
    $image_name = uniqid('prod_', true) . '.' . $ext;

    $destination = __DIR__ . 'images/' . $image_name;


    // Salva a imagem
    if (
        !move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $destination
        )
    ) {

        header(
            'Location: product-form.php?error=' .
            urlencode(
                'Falha ao enviar a imagem. Tente novamente.'
            )
        );

        exit;
    }
}


// =====================================================
// CADASTRAR NA TABELA DE APROVAÇÃO
// =====================================================

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
    die('Erro ao preparar cadastro: ' . $conn->error);
}


$stmt->bind_param(
    'sssssi',
    $name,
    $brand,
    $category,
    $description,
    $image_name,
    $created_by
);


if (!$stmt->execute()) {
    die('Erro ao cadastrar produto: ' . $stmt->error);
}


// =====================================================
// VOLTAR PARA A PÁGINA DE CADASTRO
// =====================================================

header('Location: product-form.php?sent=1');
exit;