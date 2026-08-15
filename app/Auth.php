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

function set_session_cookie(string $token, ?int $days = null): void
{
    $days = max(1, min(365, $days ?? session_days()));
    setcookie('inkriver_session', $token, [
        'expires' => time() + $days * 86400,
        'path' => '/',
        'secure' => is_production() || str_starts_with(strtolower(app_origin()), 'https://'),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function clear_session_cookie(): void
{
    setcookie('inkriver_session', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => is_production() || str_starts_with(strtolower(app_origin()), 'https://'),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function create_session_for_user(string $userId, ?int $days = null): string
{
    $pdo = Database::pdo();
    $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $now = now_iso();
    $days = max(1, min(365, $days ?? session_days()));
    $expires = gmdate('Y-m-d\TH:i:s.v\Z', time() + $days * 86400);
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
    $assignedRoles = user_role_keys((string) $session['user']['id'], (string) $session['user']['role']);
    $matchingRoles = $roles ? array_values(array_intersect($assignedRoles, $roles)) : [(string) $session['user']['role']];
    if ($roles && !$matchingRoles) {
        json_response(['error' => 'FORBIDDEN', 'message' => 'This account cannot perform that action.'], 403);
    }
    $policyAllowed = false;
    foreach ($matchingRoles as $role) {
        if (role_permission_allows_request($role)) { $policyAllowed = true; break; }
    }
    if (!$policyAllowed) {
        json_response(['error' => 'FORBIDDEN_BY_POLICY', 'message' => 'This role is blocked by the permission matrix.'], 403);
    }
    $session['roles'] = $assignedRoles;
    return $session;
}

function user_role_keys(string $userId, string $primaryRole): array
{
    $roles = [$primaryRole];
    try {
        $stmt = Database::pdo()->prepare('SELECT role_key FROM user_roles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $roles = array_merge($roles, array_column($stmt->fetchAll(), 'role_key'));
    } catch (Throwable) {
        // Existing databases remain usable while migrations are being applied.
    }
    return array_values(array_unique(array_filter($roles)));
}

function replace_secondary_user_roles(string $userId, array $roles, string $primaryRole, ?string $actorId): array
{
    $allowed = ['reader', 'writer', 'moderator', 'admin'];
    $roles = array_values(array_unique(array_intersect(array_map('strval', $roles), $allowed)));
    $roles = array_values(array_filter($roles, fn($role) => $role !== $primaryRole));
    $pdo = Database::pdo();
    $pdo->prepare('DELETE FROM user_roles WHERE user_id = ?')->execute([$userId]);
    $stmt = $pdo->prepare('INSERT INTO user_roles (user_id, role_key, assigned_by, assigned_at) VALUES (?, ?, ?, ?)');
    foreach ($roles as $role) $stmt->execute([$userId, $role, $actorId, now_iso()]);
    return user_role_keys($userId, $primaryRole);
}

function role_permission_allows_request(string $role): bool
{
    try {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $candidates = [
            '*',
            $method . ' *',
            $path,
            $method . ' ' . $path,
        ];
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        if ($segments) {
            $candidates[] = '/' . $segments[0] . '/*';
            $candidates[] = $method . ' /' . $segments[0] . '/*';
        }
        if (count($segments) >= 2) {
            $candidates[] = '/' . $segments[0] . '/' . $segments[1] . '/*';
            $candidates[] = $method . ' /' . $segments[0] . '/' . $segments[1] . '/*';
        }
        $placeholders = implode(',', array_fill(0, count($candidates), '?'));
        $stmt = Database::pdo()->prepare("SELECT permission, allowed FROM role_permissions WHERE role = ? AND permission IN ($placeholders) ORDER BY LENGTH(permission) DESC LIMIT 1");
        $stmt->execute(array_merge([$role], $candidates));
        $row = $stmt->fetch();
        return !$row || (bool) $row['allowed'];
    } catch (Throwable $error) {
        error_log('InkRiver permission check failed: ' . $error->getMessage());
        return false;
    }
}

function auth_rate_limit_key(string $bucket, string $identity): string
{
    return hash('sha256', strtolower(trim($bucket)) . '|' . strtolower(trim($identity)));
}

function enforce_auth_rate_limit(string $bucket, string $identity, int $maxAttempts = 5, int $windowSeconds = 900): void
{
    $key = auth_rate_limit_key($bucket, $identity);
    $stmt = Database::pdo()->prepare('SELECT attempts, first_attempt_at, blocked_until FROM login_attempts WHERE key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    if (!$row) return;
    $now = time();
    $blockedUntil = strtotime((string) ($row['blocked_until'] ?? '')) ?: 0;
    if ($blockedUntil > $now) {
        json_response(['error' => 'RATE_LIMITED', 'message' => 'Too many attempts. Try again later.', 'retryAfter' => $blockedUntil - $now], 429, ['Retry-After' => (string) ($blockedUntil - $now)]);
    }
    $first = strtotime((string) $row['first_attempt_at']) ?: 0;
    if ($first && $first < $now - $windowSeconds) {
        Database::pdo()->prepare('DELETE FROM login_attempts WHERE key = ?')->execute([$key]);
    }
}

function record_auth_rate_limit_failure(string $bucket, string $identity, int $maxAttempts = 5, int $windowSeconds = 900, int $blockSeconds = 900): void
{
    $pdo = Database::pdo();
    if (random_int(1, 100) === 1) {
        $pdo->prepare('DELETE FROM login_attempts WHERE first_attempt_at < ? AND (blocked_until IS NULL OR blocked_until < ?)')
            ->execute([gmdate('Y-m-d\TH:i:s.v\Z', time() - 86400), now_iso()]);
    }
    $key = auth_rate_limit_key($bucket, $identity);
    $now = now_iso();
    $stmt = $pdo->prepare('SELECT attempts, first_attempt_at FROM login_attempts WHERE key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    $first = $row ? (strtotime((string) $row['first_attempt_at']) ?: 0) : 0;
    $attempts = (!$row || $first < time() - $windowSeconds) ? 1 : ((int) $row['attempts'] + 1);
    $firstAt = (!$row || $first < time() - $windowSeconds) ? $now : $row['first_attempt_at'];
    $blockedUntil = $attempts >= $maxAttempts ? gmdate('Y-m-d\TH:i:s.v\Z', time() + $blockSeconds) : null;
    $pdo->prepare('INSERT INTO login_attempts (key, attempts, first_attempt_at, blocked_until) VALUES (?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET attempts = excluded.attempts, first_attempt_at = excluded.first_attempt_at, blocked_until = excluded.blocked_until')
        ->execute([$key, $attempts, $firstAt, $blockedUntil]);
}

function clear_auth_rate_limit(string $bucket, string $identity): void
{
    Database::pdo()->prepare('DELETE FROM login_attempts WHERE key = ?')->execute([auth_rate_limit_key($bucket, $identity)]);
}

function request_rate_limit_identity(string $account = ''): array
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return [$ip, $account !== '' ? strtolower(trim($account)) : $ip];
}

function enforce_same_origin_for_cookie_request(): void
{
    if (empty($_COOKIE['inkriver_session'])) return;
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) return;
    $expected = strtolower(rtrim(app_origin(), '/'));
    $origin = strtolower(rtrim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''), '/'));
    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $refererOrigin = '';
    if ($referer !== '') {
        $parts = parse_url($referer);
        if (is_array($parts) && isset($parts['scheme'], $parts['host'])) {
            $refererOrigin = strtolower($parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : ''));
        }
    }
    $fetchSite = strtolower((string) ($_SERVER['HTTP_SEC_FETCH_SITE'] ?? ''));
    if ($fetchSite === 'cross-site' || ($origin !== '' && !hash_equals($expected, $origin)) || ($origin === '' && $refererOrigin !== '' && !hash_equals($expected, $refererOrigin))) {
        json_response(['error' => 'INVALID_REQUEST_ORIGIN', 'message' => 'The request origin is not allowed.'], 403);
    }
}

function audit_log(?string $actorUserId, string $action, ?string $targetType = null, ?string $targetId = null, array $metadata = []): void
{
    Database::pdo()->prepare('INSERT INTO audit_logs (id, actor_user_id, action, target_type, target_id, ip_address, metadata, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([uuid_value(), $actorUserId, $action, $targetType, $targetId, $_SERVER['REMOTE_ADDR'] ?? '', json_encode($metadata), now_iso()]);
}
