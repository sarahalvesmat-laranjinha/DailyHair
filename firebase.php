<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

function firebase_auth(): \Kreait\Firebase\Contract\Auth
{
    static $auth = null;
    if ($auth === null) {
        $credentials = getenv('FIREBASE_CREDENTIALS');
        if (!$credentials || !is_file($credentials)) {
            throw new RuntimeException('Credenciais do Firebase não configuradas.');
        }
        $auth = (new \Kreait\Firebase\Factory())
            ->withServiceAccount($credentials)
            ->createAuth();
    }
    return $auth;
}
