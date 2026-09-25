<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/firebase.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function fail_login(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail_login(405, 'Método inválido.');
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || empty($_SESSION['auth_csrf']) || !hash_equals($_SESSION['auth_csrf'], (string) ($data['csrf'] ?? ''))) {
    fail_login(403, 'Recarregue a página e tente novamente.');
}

$idToken = (string) ($data['idToken'] ?? '');
if ($idToken === '' || strlen($idToken) > 10000) {
    fail_login(400, 'Token inválido.');
}

try {
    // O UID e o e-mail vêm exclusivamente do token verificado e do Firebase.
    $verified = firebase_auth()->verifyIdToken($idToken);
    $uid = (string) $verified->claims()->get('sub');
    $identity = firebase_auth()->getUser($uid);
    $email = strtolower(trim((string) $identity->email));
    if ($uid === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fail_login(400, 'Sua conta do Firebase precisa ter um e-mail válido.');
    }

    $conn = db();
    $byUid = $conn->prepare('SELECT id FROM users WHERE firebase_uid = ? LIMIT 1');
    $byUid->bind_param('s', $uid);
    $byUid->execute();
    $user = $byUid->get_result()->fetch_assoc();

    if (!$user) {
        $byEmail = $conn->prepare('SELECT id, firebase_uid FROM users WHERE email = ? LIMIT 1');
        $byEmail->bind_param('s', $email);
        $byEmail->execute();
        $existing = $byEmail->get_result()->fetch_assoc();
        if ($existing) {
            if ($existing['firebase_uid'] !== null) {
                fail_login(409, 'Este e-mail já está vinculado a outra conta.');
            }
            // Uma conta Firebase de e-mail/senha recém-criada não pode assumir
            // um perfil MySQL antigo sem comprovar a posse do e-mail.
            if (!$identity->emailVerified) {
                fail_login(409, 'Conta antiga: entre com sua senha anterior ou use um Google com o mesmo e-mail.');
            }
            $id = (int) $existing['id'];
            $link = $conn->prepare('UPDATE users SET firebase_uid = ? WHERE id = ? AND firebase_uid IS NULL');
            $link->bind_param('si', $uid, $id);
            $link->execute();
            if ($link->affected_rows !== 1) {
                fail_login(409, 'Não foi possível vincular esta conta.');
            }
        } else {
            $profile = is_array($data['profile'] ?? null) ? $data['profile'] : [];
            $name = trim((string) ($profile['name'] ?? $identity->displayName ?? ''));
            $name = mb_substr($name !== '' ? $name : strstr($email, '@', true), 0, 120);
            $hair = in_array($profile['hair_type'] ?? null, ['Liso', 'Ondulado', 'Cacheado', 'Crespo'], true) ? $profile['hair_type'] : 'Liso';
            $curl = mb_substr((string) ($profile['curl_pattern'] ?? ''), 0, 20);
            $porosity = in_array($profile['porosity'] ?? null, ['Baixa', 'Média', 'Alta'], true) ? $profile['porosity'] : 'Média';
            $thickness = in_array($profile['thickness'] ?? null, ['Fina', 'Média', 'Grossa'], true) ? $profile['thickness'] : 'Média';
            $needs = $profile['needs'] ?? [];
            $needs = is_array($needs) ? implode(', ', array_map('strval', array_slice($needs, 0, 12))) : '';
            $needs = mb_substr($needs, 0, 255);
            // A senha aleatória não é conhecida pelo usuário; Google não ganha uma senha local.
            $unusablePassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
            $insert = $conn->prepare('INSERT INTO users (name,email,password,hair_type,curl_pattern,porosity,thickness,needs,firebase_uid) VALUES (?,?,?,?,?,?,?,?,?)');
            $insert->bind_param('sssssssss', $name, $email, $unusablePassword, $hair, $curl, $porosity, $thickness, $needs, $uid);
            $insert->execute();
            $id = $conn->insert_id;
        }
    } else {
        $id = (int) $user['id'];
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
    $_SESSION['firebase_uid'] = $uid;
    echo json_encode(['ok' => true]);
} catch (\Kreait\Firebase\Exception\Auth\FailedToVerifyToken $e) {
    fail_login(401, 'Não foi possível validar seu login. Tente novamente.');
} catch (Throwable $e) {
    error_log('Firebase login: ' . $e->getMessage());
    fail_login(500, 'Não foi possível concluir o login. Confira a configuração do servidor.');
}
