<?php

declare(strict_types=1);

$appRoot = dirname(dirname(dirname(__DIR__)));
$capabilityPath = null;
foreach ([$appRoot.'/shared/.opcache-refresh', $appRoot.'/.opcache-refresh'] as $candidate) {
    if (is_file($candidate)) {
        $capabilityPath = $candidate;
        break;
    }
}
$remoteAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
$providedToken = (string) ($_SERVER['HTTP_X_WHOLEMARKET_REFRESH'] ?? '');

if (! in_array($remoteAddress, ['127.0.0.1', '::1'], true) || $capabilityPath === null) {
    http_response_code(404);

    exit;
}

$expectedToken = trim((string) file_get_contents($capabilityPath));

if ($expectedToken === '' || $providedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
    http_response_code(404);

    exit;
}

if (function_exists('opcache_reset')) {
    opcache_reset();
}

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Type: text/plain; charset=utf-8');

echo "OPCACHE_RESET\n";
