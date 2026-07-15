<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/helpers.php';

function hash_password_value(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password_value(string $password, string $hash): bool
{
    if (str_starts_with($hash, 'scrypt$')) return false;
    return password_verify($password, $hash);
}

function session_token_hash(string $token): string
{
    return hash('sha256', $token);
}

function set_session_cookie(string $token): void
{
    setcookie('inkriver_session', $token, [
        'expires' => time() + session_days() * 86400,
        'path' => '/',
        'secure' => is_production(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function clear_session_cookie(): void
{
    setcookie('inkriver_session', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => is_production(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function create_session_for_user(string $userId): string
{
    $pdo = Database::pdo();
    $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $now = now_iso();
    $expires = gmdate('Y-m-d\TH:i:s.v\Z', time() + session_days() * 86400);
    $stmt = $pdo->prepare('INSERT INTO sessions (id, user_id, token_hash, expires_at, created_at, last_seen_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([uuid_value(), $userId, session_token_hash($token), $expires, $now, $now, $_SERVER['REMOTE_ADDR'] ?? '', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)]);
    return $token;
}

function current_session(): ?array
{
    $token = $_COOKIE['inkriver_session'] ?? '';
    if (!$token) return null;
    $pdo = Database::pdo();
    $stmt = $pdo->prepare('SELECT users.*, sessions.id AS session_id FROM sessions JOIN users ON users.id = sessions.user_id WHERE sessions.token_hash = ? AND sessions.expires_at > ? AND users.status = ?');
    $stmt->execute([session_token_hash($token), now_iso(), 'active']);
    $row = $stmt->fetch();
    if (!$row) return null;
    $pdo->prepare('UPDATE sessions SET last_seen_at = ? WHERE id = ?')->execute([now_iso(), $row['session_id']]);
    return ['user' => public_user($row), 'sessionId' => $row['session_id'], 'raw' => $row];
}

function require_auth(array $roles = []): array
{
    $session = current_session();
    if (!$session) json_response(['error' => 'AUTH_REQUIRED', 'message' => 'Sign in to continue.'], 401);
    if ($roles && !in_array($session['user']['role'], $roles, true)) {
        json_response(['error' => 'FORBIDDEN', 'message' => 'This account cannot perform that action.'], 403);
    }
    return $session;
}

function audit_log(?string $actorUserId, string $action, ?string $targetType = null, ?string $targetId = null, array $metadata = []): void
{
    Database::pdo()->prepare('INSERT INTO audit_logs (id, actor_user_id, action, target_type, target_id, ip_address, metadata, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([uuid_value(), $actorUserId, $action, $targetType, $targetId, $_SERVER['REMOTE_ADDR'] ?? '', json_encode($metadata), now_iso()]);
}

