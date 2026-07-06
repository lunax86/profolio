<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;

require __DIR__ . '/../vendor/autoload.php';

Config::load(dirname(__DIR__));

$pdo = Database::connection();

$pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS admin_users (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        email         TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at    TEXT NOT NULL DEFAULT (datetime('now'))
    );
SQL);

$pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS site_settings (
        key   TEXT PRIMARY KEY,
        value TEXT
    );
SQL);

$pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS services (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        title       TEXT NOT NULL,
        description TEXT NOT NULL DEFAULT '',
        icon        TEXT NOT NULL DEFAULT 'sparkles',
        sort_order  INTEGER NOT NULL DEFAULT 0
    );
SQL);

$pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS portfolio (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        title       TEXT NOT NULL,
        description TEXT NOT NULL DEFAULT '',
        image_path  TEXT NOT NULL,
        sort_order  INTEGER NOT NULL DEFAULT 0
    );
SQL);

$pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS inquiries (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        name        TEXT NOT NULL,
        email       TEXT NOT NULL,
        phone       TEXT NOT NULL DEFAULT '',
        message     TEXT NOT NULL DEFAULT '',
        is_read     INTEGER NOT NULL DEFAULT 0,
        is_archived INTEGER NOT NULL DEFAULT 0,
        created_at  TEXT NOT NULL DEFAULT (datetime('now'))
    );
SQL);

// Idempotentní doplnění nových sloupců u již existujících databází.
$inquiryColumns = array_column($pdo->query('PRAGMA table_info(inquiries)')->fetchAll(), 'name');
if (!in_array('is_archived', $inquiryColumns, true)) {
    $pdo->exec('ALTER TABLE inquiries ADD COLUMN is_archived INTEGER NOT NULL DEFAULT 0');
    echo "Přidán sloupec inquiries.is_archived.\n";
}

echo "Migrace dokončeny.\n";
