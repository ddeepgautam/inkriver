<?php
declare(strict_types=1);

const APP_NAME = 'InkRiver';

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

function database_path(): string
{
    return env_value('DATABASE_PATH', dirname(__DIR__) . '/data/inkriver.sqlite') ?? dirname(__DIR__) . '/data/inkriver.sqlite';
}

function is_production(): bool
{
    return env_value('NODE_ENV', env_value('APP_ENV', 'development')) === 'production';
}

function session_days(): int
{
    return max(1, (int) env_value('SESSION_DAYS', '30'));
}
