<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Api.php';

if (is_production()) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

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

$publicAsset = preg_match('#^/src/[A-Za-z0-9_./-]+\.(?:css|js|svg|png|jpe?g|webp|gif|woff2?)$#i', $path)
    || preg_match('#^/uploads/(?!support(?:/|$))[A-Za-z0-9_./-]+\.(?:png|jpe?g|webp|gif)$#i', $path)
    || in_array($path, ['/sw.js'], true);
$file = $publicAsset ? realpath(__DIR__ . $path) : false;
$root = realpath(__DIR__);
$insideRoot = $file && $root && path_is_within($file, $root);
if ($insideRoot && is_file($file)) {
    $types = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'text/javascript; charset=utf-8',
        'svg' => 'image/svg+xml',
        'webmanifest' => 'application/manifest+json; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    foreach (security_headers() as $key => $value) header($key . ': ' . $value);
    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=3600');
    readfile($file);
    exit;
}

$sensitivePath = $path !== '/index.html' && (
    preg_match('#^/(?:app|data|scripts|tests|storage|uploads/support)(?:/|$)#i', $path)
    || preg_match('#(?:^|/)\.[^/]+#', $path)
    || preg_match('#(?:^|/)[^/]+\.[A-Za-z0-9]{1,12}$#', $path)
);
if ($sensitivePath) {
    http_response_code(404);
    foreach (security_headers() + ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'no-store'] as $key => $value) header($key . ': ' . $value);
    echo 'Not found';
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
