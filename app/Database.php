<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Database
{
    private const SCHEMA_VERSION = 20261011;
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo) return self::$pdo;
        validate_sensitive_storage_configuration();
        $path = database_path();
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) throw new RuntimeException('Unable to create the private database directory.');
        @chmod($dir, 0700);
        self::$pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        self::$pdo->exec('PRAGMA foreign_keys = ON');
        self::$pdo->exec('PRAGMA busy_timeout = 5000');
        @chmod($path, 0600);
        $schemaVersion = (int) self::$pdo->query('PRAGMA user_version')->fetchColumn();
        if ($schemaVersion < self::SCHEMA_VERSION) self::migrate();
        return self::$pdo;
    }

    private static function migrate(): void
    {
        // WAL is persistent for the database. Setting it only while migrating
        // avoids taking a journal-mode lock on every web request.
        self::$pdo->exec('PRAGMA journal_mode = WAL');
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
        self::ensureColumn('discount_codes', 'deleted_at', 'TEXT');
        self::ensureColumn('support_ticket_attachments', 'storage_path', 'TEXT');
        self::ensureColumn('oauth_states', 'link_user_id', 'TEXT REFERENCES users(id) ON DELETE CASCADE');
        self::ensureColumn('subscriptions', 'plan_version_id', 'TEXT REFERENCES subscription_plan_versions(id) ON DELETE RESTRICT');
        self::ensureColumn('subscriptions', 'current_period_start', 'TEXT');
        self::ensureColumn('subscriptions', 'current_period_end', 'TEXT');
        self::ensureColumn('subscriptions', 'cancel_at_period_end', 'INTEGER NOT NULL DEFAULT 0');
        self::ensureColumn('subscriptions', 'cancelled_at', 'TEXT');
        self::ensureColumn('subscriptions', 'grace_ends_at', 'TEXT');
        self::ensureColumn('resources', 'subscription_eligible', 'INTEGER NOT NULL DEFAULT 0');
        self::ensureColumn('resources', 'seo_title', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('resources', 'meta_description', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('resources', 'canonical_url', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('resources', 'robots_index', 'INTEGER NOT NULL DEFAULT 1');
        self::ensureColumn('resources', 'social_title', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('resources', 'social_description', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('resources', 'social_image_url', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('business_profile_claims', 'proof_file_name', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('business_profile_claims', 'proof_file_path', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('business_profile_claims', 'proof_file_mime', "TEXT NOT NULL DEFAULT ''");
        self::ensureColumn('business_profile_claims', 'proof_file_size', 'INTEGER NOT NULL DEFAULT 0');
        foreach (['business_companies', 'business_people'] as $profileTable) {
            self::ensureColumn($profileTable, 'seo_title', "TEXT NOT NULL DEFAULT ''");
            self::ensureColumn($profileTable, 'meta_description', "TEXT NOT NULL DEFAULT ''");
            self::ensureColumn($profileTable, 'canonical_url', "TEXT NOT NULL DEFAULT ''");
            self::ensureColumn($profileTable, 'robots_index', 'INTEGER NOT NULL DEFAULT 1');
            self::ensureColumn($profileTable, 'social_title', "TEXT NOT NULL DEFAULT ''");
            self::ensureColumn($profileTable, 'social_description', "TEXT NOT NULL DEFAULT ''");
            self::ensureColumn($profileTable, 'social_image_url', "TEXT NOT NULL DEFAULT ''");
        }
        self::$pdo->exec('PRAGMA user_version = ' . self::SCHEMA_VERSION);
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
