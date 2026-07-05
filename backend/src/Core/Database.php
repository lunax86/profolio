<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Jednoduchý singleton nad PDO/SQLite.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $path = Config::get('DB_PATH', 'database/database.sqlite');
        $fullPath = str_starts_with((string) $path, '/')
            ? (string) $path
            : Config::basePath('/' . ltrim((string) $path, '/'));

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        $pdo = new PDO('sqlite:' . $fullPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');

        return self::$pdo = $pdo;
    }
}
