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

$pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS page_views (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        day          TEXT NOT NULL,
        visitor_hash TEXT NOT NULL,
        created_at   TEXT NOT NULL DEFAULT (datetime('now')),
        UNIQUE (day, visitor_hash)
    );
SQL);

$pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS login_attempts (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        email      TEXT NOT NULL DEFAULT '',
        ip         TEXT NOT NULL DEFAULT '',
        success    INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );
SQL);

// Interní (neveřejné) nastavení - secrets jako SMTP. Záměrně mimo site_settings,
// aby se nedostalo do veřejného GET /api/settings.
$pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS private_settings (
        key   TEXT PRIMARY KEY,
        value TEXT
    );
SQL);

// Idempotentní doplnění nových sloupců u již existujících databází.
$inquiryColumns = array_column($pdo->query('PRAGMA table_info(inquiries)')->fetchAll(), 'name');
if (!in_array('is_archived', $inquiryColumns, true)) {
    $pdo->exec('ALTER TABLE inquiries ADD COLUMN is_archived INTEGER NOT NULL DEFAULT 0');
    echo "Přidán sloupec inquiries.is_archived.\n";
}

// Role admina: is_super (super admin spravuje ostatní účty). Idempotentní.
$adminColumns = array_column($pdo->query('PRAGMA table_info(admin_users)')->fetchAll(), 'name');
if (!in_array('is_super', $adminColumns, true)) {
    $pdo->exec('ALTER TABLE admin_users ADD COLUMN is_super INTEGER NOT NULL DEFAULT 0');
    echo "Přidán sloupec admin_users.is_super.\n";
}
// Když ještě žádný super admin není (i po upgradu staré DB), povýšit nejstarší účet.
$hasSuperAdmin = (int) $pdo->query('SELECT COUNT(*) FROM admin_users WHERE is_super = 1')->fetchColumn() > 0;
if (!$hasSuperAdmin) {
    $oldestAdminId = $pdo->query('SELECT id FROM admin_users ORDER BY id LIMIT 1')->fetchColumn();
    if ($oldestAdminId !== false) {
        $pdo->prepare('UPDATE admin_users SET is_super = 1 WHERE id = ?')->execute([(int) $oldestAdminId]);
        echo "Nejstarší admin povýšen na super admina.\n";
    }
}

// Výchozí text zásad ochrany osobních údajů - jen pokud ještě není nastaven.
$privacyDefault = <<<'TXT'
Zásady ochrany osobních údajů

Správce údajů
Správcem osobních údajů je provozovatel tohoto webu; kontaktní údaje najdete v sekci Kontakt. Doplňte prosím v administraci konkrétní údaje své firmy (název, IČO, sídlo).

Jaké údaje zpracováváme
Prostřednictvím poptávkového formuláře zpracováváme jméno, e-mail, telefonní číslo a text zprávy, které nám sami zašlete.

Účel a právní základ
Údaje zpracováváme výhradně za účelem vyřízení vaší poptávky. Právním základem je opatření před uzavřením smlouvy, případně náš oprávněný zájem odpovědět na váš dotaz.

Doba uchování
Údaje uchováváme po dobu nezbytnou k vyřízení poptávky a navazující komunikace.

Předání třetím stranám
Vaše údaje nepředáváme třetím stranám ani je nevyužíváme k marketingu.

Cookies a analytika
Web nepoužívá marketingové ani sledovací cookies a návštěvnost měříme pouze anonymně.

Vaše práva
Máte právo na přístup ke svým údajům, jejich opravu či vymazání, omezení zpracování a vznést námitku. Pro uplatnění práv nás kontaktujte na e-mailu uvedeném v sekci Kontakt.
TXT;
$pdo->prepare("INSERT OR IGNORE INTO site_settings (key, value) VALUES ('privacy_policy', ?)")
    ->execute([$privacyDefault]);

// Výchozí časová zóna webu - jen pokud ještě není nastavena.
$pdo->prepare("INSERT OR IGNORE INTO site_settings (key, value) VALUES ('timezone', ?)")
    ->execute(['Europe/Prague']);

// Výchozí hodnoty SMTP - jen pokud ještě nejsou nastaveny.
foreach (['smtp_port' => '587', 'smtp_encryption' => 'tls'] as $smtpKey => $smtpDefault) {
    $pdo->prepare('INSERT OR IGNORE INTO private_settings (key, value) VALUES (?, ?)')
        ->execute([$smtpKey, $smtpDefault]);
}

echo "Migrace dokončeny.\n";
