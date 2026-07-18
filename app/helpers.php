<?php
declare(strict_types=1);

function now_iso(): string
{
    return gmdate('Y-m-d\TH:i:s.v\Z');
}

function json_response(array $payload, int $status = 200, array $headers = []): never
{
    http_response_code($status);
    foreach (security_headers() + ['Content-Type' => 'application/json; charset=utf-8', 'Cache-Control' => 'no-store'] + $headers as $key => $value) {
        header($key . ': ' . $value);
    }
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function redirect_response(string $location, array $headers = []): never
{
    http_response_code(302);
    foreach (security_headers() + ['Location' => $location, 'Cache-Control' => 'no-store'] + $headers as $key => $value) {
        header($key . ': ' . $value);
    }
    exit;
}

function read_json(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $maxBytes = max(1024 * 1024, (int) (env_value('JSON_REQUEST_MAX_BYTES', '12582912') ?? '12582912'));
    if (strlen($raw) > $maxBytes) json_response(['error' => 'REQUEST_TOO_LARGE', 'message' => 'Request body is too large.'], 413);
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function uuid_value(string $prefix = ''): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);
    $uuid = sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20));
    return $prefix . $uuid;
}

function security_headers(): array
{
    return [
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' https://checkout.razorpay.com https://sdk.cashfree.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self' https:; frame-src https://api.razorpay.com https://checkout.razorpay.com https://sdk.cashfree.com https://payments.cashfree.com https://sandbox.cashfree.com; object-src 'none'; base-uri 'self'; form-action 'self' https://secure.payu.in https://test.payu.in; frame-ancestors 'none'",
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(self)',
    ];
}

function public_user(?array $row): ?array
{
    if (!$row) return null;
    return [
        'id' => $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'username' => $row['username'] ?? '',
        'avatarUrl' => $row['avatar_url'] ?? '',
        'headline' => $row['headline'] ?? '',
        'bio' => $row['bio'] ?? '',
        'website' => $row['website'] ?? '',
        'location' => $row['location'] ?? '',
        'socialLinks' => parse_json_field($row['social_links_json'] ?? '{}', []),
        'expertise' => parse_json_field($row['expertise_json'] ?? '[]', []),
        'role' => $row['role'],
        'subscription' => $row['subscription'],
        'status' => $row['status'],
        'emailVerified' => (bool) ($row['email_verified'] ?? false),
        'joined' => $row['created_at'],
        'lastLoginAt' => $row['last_login_at'] ?? null,
    ];
}

function parse_json_field(?string $value, mixed $fallback): mixed
{
    if ($value === null || $value === '') return $fallback;
    $decoded = json_decode($value, true);
    return json_last_error() === JSON_ERROR_NONE ? $decoded : $fallback;
}
