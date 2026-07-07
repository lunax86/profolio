<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

/**
 * Key/value nastavení webu (title, slogan, kontakt, hero…).
 */
final class SettingRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /** @return array<string, string> */
    public function all(): array
    {
        $rows = $this->pdo->query('SELECT key, value FROM site_settings')->fetchAll();

        return array_column($rows, 'value', 'key');
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $statement = $this->pdo->prepare('SELECT value FROM site_settings WHERE key = ?');
        $statement->execute([$key]);
        $value = $statement->fetchColumn();

        return $value === false ? $default : (string) $value;
    }

    /** @param array<string, string|null> $values */
    public function setMany(array $values): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO site_settings (key, value) VALUES (:key, :value)
             ON CONFLICT(key) DO UPDATE SET value = excluded.value'
        );
        foreach ($values as $key => $value) {
            $statement->execute(['key' => $key, 'value' => $value]);
        }
    }
}
