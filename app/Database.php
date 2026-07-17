<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo) return self::$pdo;
        $path = database_path();
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        self::$pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        self::$pdo->exec('PRAGMA foreign_keys = ON');
        self::$pdo->exec('PRAGMA journal_mode = WAL');
        self::$pdo->exec('PRAGMA busy_timeout = 5000');
        self::migrate();
        return self::$pdo;
    }

    private static function migrate(): void
    {
        $schema = file_get_contents(dirname(__DIR__) . '/schema.sql');
        if ($schema === false) throw new RuntimeException('schema.sql not found');
        self::$pdo->exec($schema);
        self::ensureColumn('users', 'username', 'TEXT COLLATE NOCASE');
        self::$pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_users_username ON users(username) WHERE username IS NOT NULL AND username != ''");
        self::ensureColumn('users', 'avatar_url', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('users', 'headline', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('users', 'bio', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('users', 'website', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('users', 'location', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('users', 'social_links_json', "TEXT NOT NULL DEFAULT '{}'");
        self::ensureColumn('users', 'expertise_json', "TEXT NOT NULL DEFAULT '[]'");
        self::ensureColumn('feature_flags', 'rollout_percent', "INTEGER NOT NULL DEFAULT 100");
        self::ensureColumn('feature_flags', 'roles_json', "TEXT NOT NULL DEFAULT '[]'");
        self::ensureColumn('feature_flags', 'starts_at', 'TEXT');
        self::ensureColumn('feature_flags', 'ends_at', 'TEXT');
        self::ensureColumn('feature_flags', 'environment', "TEXT NOT NULL DEFAULT 'all'");
        self::ensureColumn('feature_flag_history', 'environment', "TEXT NOT NULL DEFAULT 'all'");
        self::ensureColumn('content_imports', 'metadata_json', "TEXT NOT NULL DEFAULT '{}'");
        self::ensureColumn('content_imports', 'snapshot_json', "TEXT NOT NULL DEFAULT '[]'");
    }

    private static function ensureColumn(string $table, string $column, string $definition): void
    {
        $columns = self::$pdo->query("PRAGMA table_info($table)")->fetchAll();
        foreach ($columns as $existing) {
            if (($existing['name'] ?? '') === $column) return;
        }
        self::$pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
    }
}
