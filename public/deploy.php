<?php

// Mengambil secret dari file .env (misalnya parameter GITHUB_WEBHOOK_SECRET)
$envFile = __DIR__ . '/../.env';
$envVars = file_exists($envFile) ? parse_ini_file($envFile) : [];
$secret = $envVars['GITHUB_WEBHOOK_SECRET'];

$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';

if ($signature) {
    $hash = "sha1=" . hash_hmac('sha1', file_get_contents('php://input'), $secret);
    if (hash_equals($hash, $signature)) {
        // Karena file ini ada di folder public/, kita naik 1 level (/..) ke root folder laravel
        echo shell_exec("cd " . __DIR__ . "/.. && git pull origin main 2>&1");
        // Bila suatu saat ingin auto php artisan migrate dan clear cache, bisa tambahkan di belakangnya
        exit;
    }
}

http_response_code(403);
