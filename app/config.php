<?php
declare(strict_types=1);

const APP_NAME = 'InkRiver';

function project_root(): string
{
    return dirname(__DIR__);
}

function public_root(): string
{
    return project_root() . DIRECTORY_SEPARATOR . 'public';
}

function public_path(string $relative = ''): string
{
    $segments = preg_split('#[\\\\/]+#', ltrim($relative, '/\\')) ?: [];
    $safeSegments = [];
    foreach ($segments as $segment) {
        if ($segment === '' || $segment === '.') continue;
        if ($segment === '..') throw new InvalidArgumentException('Public paths cannot traverse outside the document root.');
        $safeSegments[] = $segment;
    }
    return $safeSegments ? public_root() . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $safeSegments) : public_root();
}

function configured_document_root(): string
{
    $configured = trim((string) env_value('PUBLIC_DOCUMENT_ROOT', ''));
    return $configured !== '' ? rtrim($configured, "\\/ ") : public_root();
}

function env_file_values(): array
{
    static $values = null;
    if ($values !== null) return $values;
    $values = [];
    foreach ([dirname(__DIR__) . '/.env', dirname(__DIR__) . '/.env.local'] as $file) {
        if (!is_file($file) || !is_readable($file)) continue;
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if ($key === '') continue;
            $values[$key] = trim($value, "\"'");
        }
    }
    return $values;
}

function env_value(string $key, ?string $fallback = null): ?string
{
    $value = getenv($key);
    if ($value !== false && $value !== '') return $value;
    $fileValues = env_file_values();
    return isset($fileValues[$key]) && $fileValues[$key] !== '' ? $fileValues[$key] : $fallback;
}

function app_origin(): string
{
    return rtrim(env_value('APP_ORIGIN', 'http://127.0.0.1:8080') ?? '', '/');
}

function private_storage_root(): string
{
    $configured = trim((string) env_value('PRIVATE_STORAGE_PATH', ''));
    $path = $configured !== '' ? $configured : dirname(project_root()) . DIRECTORY_SEPARATOR . 'inkriver-private';
    if (!preg_match('#^(?:[A-Za-z]:[\\\\/]|/)#', $path)) $path = project_root() . DIRECTORY_SEPARATOR . $path;
    return rtrim($path, "\\/ ");
}

function private_storage_path(string $relative = ''): string
{
    $root = private_storage_root();
    if (!is_dir($root) && !mkdir($root, 0700, true) && !is_dir($root)) {
        throw new RuntimeException('Unable to create private application storage.');
    }
    @chmod($root, 0700);
    $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relative, '/\\'));
    if ($relative === '' || str_contains($relative, '..' . DIRECTORY_SEPARATOR)) return $root;
    return $root . DIRECTORY_SEPARATOR . $relative;
}

function database_path(): string
{
    $configured = trim((string) env_value('DATABASE_PATH', ''));
    if ($configured !== '') return $configured;
    $legacyDevelopmentPath = project_root() . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'inkriver.sqlite';
    if (!is_production() && is_file($legacyDevelopmentPath)) return $legacyDevelopmentPath;
    return private_storage_path('database' . DIRECTORY_SEPARATOR . 'inkriver.sqlite');
}

function is_production(): bool
{
    return env_value('NODE_ENV', env_value('APP_ENV', 'development')) === 'production';
}

function session_days(): int
{
    return max(1, (int) env_value('SESSION_DAYS', '30'));
}

function path_is_within(string $path, string $directory): bool
{
    $path = str_replace('\\', '/', rtrim($path, "\\/"));
    $directory = str_replace('\\', '/', rtrim($directory, "\\/"));
    if (PHP_OS_FAMILY === 'Windows') {
        $path = strtolower($path);
        $directory = strtolower($directory);
    }
    return $path === $directory || str_starts_with($path . '/', $directory . '/');
}

function validate_sensitive_storage_configuration(): void
{
    if (!is_production()) return;
    if (PHP_SAPI !== 'cli') {
        $documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $expectedRoot = realpath(configured_document_root());
        if (!$documentRoot || !$expectedRoot || !path_is_within($documentRoot, $expectedRoot) || !path_is_within($expectedRoot, $documentRoot)) {
            throw new RuntimeException('The production web server document root must match the configured public directory.');
        }
    }
    if (!str_starts_with(strtolower(app_origin()), 'https://')) {
        throw new RuntimeException('APP_ORIGIN must use HTTPS in production.');
    }
    if (path_is_within(database_path(), project_root()) || path_is_within(private_storage_root(), project_root())) {
        throw new RuntimeException('Production database and private storage must be located outside the application repository.');
    }
    application_secret_value();
}

function application_secret_value(): string
{
    $configured = (string) (env_value('APP_SECRET') ?: env_value('APP_KEY') ?: '');
    if ($configured !== '') {
        if (is_production() && strlen($configured) < 32) throw new RuntimeException('APP_SECRET must contain at least 32 characters in production.');
        return $configured;
    }
    if (is_production()) throw new RuntimeException('APP_SECRET is required in production.');

    $file = private_storage_path('app-secret.key');
    if (is_file($file)) {
        $stored = trim((string) file_get_contents($file));
        if (strlen($stored) >= 32) return $stored;
    }
    $generated = bin2hex(random_bytes(32));
    if (file_put_contents($file, $generated . PHP_EOL, LOCK_EX) === false) throw new RuntimeException('Unable to persist the local application secret.');
    @chmod($file, 0600);
    return $generated;
}
