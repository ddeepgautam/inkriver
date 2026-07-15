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
    }
}

