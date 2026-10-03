<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Api.php';
require_once dirname(__DIR__) . '/app/Seo.php';

if (is_production()) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($redirect = seo_canonical_redirect($path)) {
    header('Location: ' . $redirect, true, 308);
    exit;
}

if ($method === 'GET' && $path === '/index.html') {
    header('Location: ' . rtrim(app_origin(), '/') . '/', true, 301);
    exit;
}

if ($method === 'GET' && preg_match('#^/publications/inkriver/?$#i', $path)) {
    header('Location: ' . rtrim(app_origin(), '/') . '/', true, 301);
    exit;
}

if ($method === 'GET' && rtrim($path, '/') === '/health') {
    json_response(['status' => 'ok', 'service' => 'nitross-mcp'], 200, ['X-Robots-Tag' => 'noindex, nofollow']);
}

if ($method === 'GET' && rtrim($path, '/') === '/version') {
    json_response(['service' => 'nitross-mcp', 'version' => mcp_version()], 200, ['X-Robots-Tag' => 'noindex, nofollow']);
}

if (str_starts_with($path, '/.well-known/oauth-') || $path === '/.well-known/openid-configuration' || str_starts_with($path, '/oauth/') || str_starts_with($path, '/api/oauth/')) {
    handle_oauth($path, $method);
}

if (str_starts_with($path, '/api/')) {
    handle_api($path, $method);
}

if (rtrim($path, '/') === '/mcp') {
    handle_mcp($method);
}

if (is_mcp_host_request()) {
    http_response_code(404);
    foreach (security_headers() + ['Content-Type' => 'application/json; charset=utf-8', 'Cache-Control' => 'no-store', 'X-Robots-Tag' => 'noindex, nofollow'] as $key => $value) header($key . ': ' . $value);
    echo json_encode(['error' => 'NOT_FOUND', 'message' => 'This host serves only the Nitross MCP API.'], JSON_UNESCAPED_SLASHES);
    exit;
}

if ($method === 'GET' && in_array($path, ['/sitemap.xml', '/sitemap_index.xml'], true)) {
    foreach (security_headers() + ['Content-Type' => 'application/xml; charset=utf-8', 'Cache-Control' => 'public, max-age=900'] as $key => $value) header($key . ': ' . $value);
    echo seo_sitemap_index();
    exit;
}

if ($method === 'GET' && preg_match('#^/sitemaps/(pages|articles|categories|founders|companies|resources)\.xml$#', $path, $match)) {
    foreach (security_headers() + ['Content-Type' => 'application/xml; charset=utf-8', 'Cache-Control' => 'public, max-age=900'] as $key => $value) header($key . ': ' . $value);
    echo seo_sitemap_urlset($match[1]);
    exit;
}

if ($method === 'GET' && $path === '/robots.txt') {
    $artifact = seo_artifact_content('robots.txt');
    foreach (security_headers() + ['Content-Type' => $artifact['mimeType'] ?? 'text/plain; charset=utf-8', 'Cache-Control' => 'public, max-age=900'] as $key => $value) header($key . ': ' . $value);
    echo seo_robots_txt($artifact['content'] ?? null);
    exit;
}

if ($method === 'GET' && $path === '/manifest.webmanifest') {
    $name = configured_site_name();
    foreach (security_headers() + ['Content-Type' => 'application/manifest+json; charset=utf-8', 'Cache-Control' => 'no-cache'] as $key => $value) header($key . ': ' . $value);
    echo json_encode([
        'name' => $name,
        'short_name' => $name,
        'description' => 'Learn entrepreneurship, explore startup insights, and use practical resources to build and grow a business.',
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#ffffff',
        'theme_color' => '#176b48',
        'icons' => [['src' => '/src/icon.svg', 'sizes' => 'any', 'type' => 'image/svg+xml', 'purpose' => 'any maskable']],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$publicAsset = preg_match('#^/(?:src|dist)/[A-Za-z0-9_./-]+\.(?:css|js|svg|png|jpe?g|webp|gif|woff2?)$#i', $path)
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
    if ($path === '/sw.js') {
        header('Cache-Control: no-cache');
    } elseif (str_starts_with($path, '/src/') || str_starts_with($path, '/dist/')) {
        header('Cache-Control: public, max-age=31536000, immutable');
    } else {
        header('Cache-Control: public, max-age=3600');
    }
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

$page = seo_resolve_page($path);
http_response_code((int) $page['status']);
foreach (security_headers() + ['Content-Type' => 'text/html; charset=utf-8', 'Cache-Control' => 'no-cache', 'Vary' => 'Cookie'] as $key => $value) {
    header($key . ': ' . $value);
}
$html = file_get_contents(__DIR__ . '/index.html');
if ($html === false) {
    http_response_code(500);
    echo 'Application shell unavailable.';
    exit;
}
echo seo_render_document($html, $page);
