<?php
declare(strict_types=1);

require_once __DIR__ . '/app/Api.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (str_starts_with($path, '/.well-known/oauth-') || $path === '/.well-known/openid-configuration' || str_starts_with($path, '/oauth/') || str_starts_with($path, '/api/oauth/')) {
    handle_oauth($path, $method);
}

if (str_starts_with($path, '/api/')) {
    handle_api($path, $method);
}

if (rtrim($path, '/') === '/mcp') {
    handle_mcp($method);
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

if ($method === 'GET' && $path === '/manifest.webmanifest') {
    $name = configured_site_name();
    foreach (security_headers() + ['Content-Type' => 'application/manifest+json; charset=utf-8', 'Cache-Control' => 'no-cache'] as $key => $value) header($key . ': ' . $value);
    echo json_encode([
        'name' => $name . ' Publishing',
        'short_name' => $name,
        'description' => 'Read, publish, subscribe, and manage an independent editorial platform.',
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#ffffff',
        'theme_color' => '#176b48',
        'icons' => [['src' => '/src/icon.svg', 'sizes' => 'any', 'type' => 'image/svg+xml', 'purpose' => 'any maskable']],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$file = realpath(__DIR__ . $path);
$root = realpath(__DIR__);
if ($file && $root && str_starts_with($file, $root) && is_file($file) && !in_array(basename($file), ['index.php', 'index.html'], true)) {
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
$html = file_get_contents(__DIR__ . '/index.html');
if ($html === false) {
    http_response_code(500);
    echo 'Application shell unavailable.';
    exit;
}
$siteSeo = document_value('site-seo-public', document_value('site-seo', []));
$siteSeo = is_array($siteSeo) ? $siteSeo : [];
$siteName = configured_site_name();
$pageTitle = trim((string) ($siteSeo['homepageSeoTitle'] ?? '')) ?: $siteName . ' - Publishing, Memberships, and Business Network';
$pageDescription = trim((string) ($siteSeo['homepageMetaDescription'] ?? '')) ?: $siteName . ' combines independent publishing, memberships, and a trusted business network.';
$escapedTitle = htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$escapedDescription = htmlspecialchars($pageDescription, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$html = preg_replace_callback('/<title>.*?<\/title>/s', fn() => '<title>' . $escapedTitle . '</title>', $html, 1) ?: $html;
$html = preg_replace_callback('/(<meta\s+name="description"\s+content=")[^"]*("\s*\/?>)/s', fn($match) => $match[1] . $escapedDescription . $match[2], $html, 1) ?: $html;
echo $html;
