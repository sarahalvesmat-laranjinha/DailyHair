<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

function firebase_auth(): \Kreait\Firebase\Contract\Auth
{
    static $auth = null;
    if ($auth !== null) {
        return $auth;
    }

    $factory = new \Kreait\Firebase\Factory();
    $credentialsPath = getenv('FIREBASE_CREDENTIALS') ?: '';
    $credentialsJson = getenv('FIREBASE_SERVICE_ACCOUNT_JSON') ?: '';

    if ($credentialsPath !== '' && is_file($credentialsPath)) {
        $factory = $factory->withServiceAccount($credentialsPath);
    } elseif ($credentialsJson !== '') {
        $credentials = json_decode($credentialsJson, true);
        if (!is_array($credentials)) {
            throw new RuntimeException('FIREBASE_SERVICE_ACCOUNT_JSON inválido.');
        }
        $factory = $factory->withServiceAccount($credentials);
    } else {
        throw new RuntimeException('Credenciais do Firebase não configuradas.');
    }

    $auth = $factory->createAuth();
    return $auth;
}
