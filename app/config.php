<?php
declare(strict_types=1);

const APP_NAME = 'InkRiver';

function env_value(string $key, ?string $fallback = null): ?string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $fallback : $value;
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

