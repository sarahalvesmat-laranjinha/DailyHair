<?php
require_once __DIR__ . '/db.php';

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id']))
        return null;
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch() ?: null;
}


function profile_image_url(?array $user): ?string
{
    if (!$user || empty($user['profile_image'])) {
        return null;
    }

    $value = trim((string) $user['profile_image']);

    if ($value === '') {
        return null;
    }

    if (str_starts_with($value, 'data:image/')) {
        return $value;
    }

    if (preg_match('#^https?://#i', $value) === 1) {
        return $value;
    }

    return 'uploads/profiles/' . rawurlencode(basename($value));
}

function product_image_url(?string $value): ?string
{
    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    // Novos uploads ficam persistidos no PostgreSQL como data URL.
    if (str_starts_with($value, 'data:image/')) {
        return $value;
    }

    // Também aceita URLs externas já cadastradas.
    if (preg_match('#^https?://#i', $value) === 1) {
        return $value;
    }

    // Compatibilidade com as imagens antigas versionadas na pasta images.
    $filename = basename($value);
    $path = __DIR__ . '/images/' . $filename;

    if (!is_file($path)) {
        return null;
    }

    return 'images/' . rawurlencode($filename);
}

function uploaded_product_image_to_data_url(array $file): string
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha ao receber a imagem do produto.');
    }

    $size = (int) ($file['size'] ?? 0);

    if ($size <= 0 || $size > 5 * 1024 * 1024) {
        throw new RuntimeException('Imagem muito grande. Máximo de 5MB.');
    }

    $tmp = (string) ($file['tmp_name'] ?? '');

    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Arquivo de imagem inválido.');
    }

    $info = @getimagesize($tmp);
    $mime = is_array($info) ? (string) ($info['mime'] ?? '') : '';

    $allowed = [
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Formato de imagem inválido. Use JPG, PNG ou WEBP.');
    }

    $bytes = @file_get_contents($tmp);

    if ($bytes === false || $bytes === '') {
        throw new RuntimeException('Não foi possível ler a imagem enviada.');
    }

    return 'data:' . $mime . ';base64,' . base64_encode($bytes);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
