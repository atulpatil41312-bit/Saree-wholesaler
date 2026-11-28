<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// A one-time, localhost-only deployment hook lets the release clear stale
// PHP-FPM OPcache without granting the deployment account root privileges.
$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$capabilityPath = __DIR__.'/../.opcache-refresh';
if ($requestPath === '/opcache-refresh') {
    if (in_array((string) ($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true) && is_file($capabilityPath)) {
        $expectedToken = trim((string) file_get_contents($capabilityPath));
        $providedToken = (string) ($_SERVER['HTTP_X_WHOLEMARKET_REFRESH'] ?? '');

        if ($expectedToken !== '' && $providedToken !== '' && hash_equals($expectedToken, $providedToken)) {
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Content-Type: text/plain; charset=utf-8');
            echo "OPCACHE_RESET\n";
            exit;
        }
    }

    http_response_code(404);
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
