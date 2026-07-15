<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Auth.php';

[$script, $email, $password, $name] = array_pad($argv, 4, null);
$email = strtolower(trim((string) $email));
$password = (string) $password;
$name = trim((string) ($name ?: 'InkRiver Administrator'));

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Usage: php scripts/create-admin.php admin@example.com 'strong-password' 'Admin Name'\n");
    fwrite(STDERR, "Password must contain at least 12 characters.\n");
    exit(1);
}

$pdo = Database::pdo();
$now = now_iso();
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
$existing = $stmt->fetch();
if ($existing) {
    $pdo->prepare("UPDATE users SET name = ?, password_hash = ?, role = 'admin', status = 'active', subscription = 'Staff', email_verified = 1, updated_at = ? WHERE id = ?")
        ->execute([$name, hash_password_value($password), $now, $existing['id']]);
    echo "Updated administrator: {$email}\n";
} else {
    $pdo->prepare("INSERT INTO users (id, name, email, password_hash, role, subscription, status, email_verified, created_at, updated_at) VALUES (?, ?, ?, ?, 'admin', 'Staff', 'active', 1, ?, ?)")
        ->execute([uuid_value('USR-'), $name, $email, hash_password_value($password), $now, $now]);
    echo "Created administrator: {$email}\n";
}

