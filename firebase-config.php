<?php
declare(strict_types=1);

header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-store');

$config = [
    'apiKey' => getenv('FIREBASE_WEB_API_KEY') ?: '',
    'authDomain' => getenv('FIREBASE_WEB_AUTH_DOMAIN') ?: '',
    'projectId' => getenv('FIREBASE_WEB_PROJECT_ID') ?: '',
    'appId' => getenv('FIREBASE_WEB_APP_ID') ?: '',
];

echo 'export const firebaseConfig = ' . json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';';
