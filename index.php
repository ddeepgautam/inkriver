<?php
declare(strict_types=1);

require_once __DIR__ . '/app/Api.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (str_starts_with($path, '/api/')) {
    handle_api($path, $method);
}

$file = realpath(__DIR__ . $path);
$root = realpath(__DIR__);
if ($file && $root && str_starts_with($file, $root) && is_file($file) && basename($file) !== 'index.php') {
    $types = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'text/javascript; charset=utf-8',
        'svg' => 'image/svg+xml',
        'webmanifest' => 'application/manifest+json; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
    ];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    readfile($file);
    exit;
}

foreach (security_headers() + ['Content-Type' => 'text/html; charset=utf-8', 'Cache-Control' => 'no-cache', 'Vary' => 'Cookie'] as $key => $value) {
    header($key . ': ' . $value);
}
readfile(__DIR__ . '/index.html');

