<?php

use App\Kernel;

// Quick health check (bypass Symfony bootstrap for faster response)
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/health') {
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

// Load .env.local if it exists (for Render deployment)
$envLocalFile = dirname(__DIR__).'/.env.local';
if (file_exists($envLocalFile)) {
    $envVars = parse_ini_file($envLocalFile);
    foreach ($envVars as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
