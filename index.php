<?php
declare(strict_types=1);

require_once __DIR__ . '/app/Api.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (str_starts_with($path, '/api/')) {
    handle_api($path, $method);
}

if ($method === 'GET' && $path === '/sitemap.xml') {
    $artifact = seo_artifact_content('sitemap.xml');
    foreach (security_headers() + ['Content-Type' => $artifact['mimeType'] ?? 'application/xml; charset=utf-8', 'Cache-Control' => 'public, max-age=900'] as $key => $value) header($key . ': ' . $value);
    echo $artifact['content'] ?? sitemap_xml();
    exit;
}

if ($method === 'GET' && $path === '/robots.txt') {
    $artifact = seo_artifact_content('robots.txt');
    foreach (security_headers() + ['Content-Type' => $artifact['mimeType'] ?? 'text/plain; charset=utf-8', 'Cache-Control' => 'public, max-age=900'] as $key => $value) header($key . ': ' . $value);
    echo $artifact['content'] ?? "User-agent: *\nAllow: /\nSitemap: " . rtrim(app_origin(), '/') . "/sitemap.xml\n";
    exit;
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
