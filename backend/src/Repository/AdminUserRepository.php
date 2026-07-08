<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

final class AdminUserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return $this->pdo
            ->query('SELECT id, email, is_super, created_at FROM admin_users ORDER BY id')
            ->fetchAll();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE email = ?');
        $statement->execute([$email]);

        return $statement->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE id = ?');
        $statement->execute([$id]);

        return $statement->fetch() ?: null;
    }

    /** Existuje účet s tímto e-mailem? (volitelně kromě daného id při změně vlastního e-mailu) */
    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        if ($exceptId === null) {
            $statement = $this->pdo->prepare('SELECT 1 FROM admin_users WHERE email = ?');
            $statement->execute([$email]);
        } else {
            $statement = $this->pdo->prepare('SELECT 1 FROM admin_users WHERE email = ? AND id != ?');
            $statement->execute([$email, $exceptId]);
        }

        return $statement->fetchColumn() !== false;
    }

    public function create(string $email, string $passwordHash, bool $isSuper = false): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_users (email, password_hash, is_super) VALUES (?, ?, ?)'
        );
        $statement->execute([$email, $passwordHash, $isSuper ? 1 : 0]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $statement = $this->pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
        $statement->execute([$passwordHash, $id]);
    }

    public function updateEmail(int $id, string $email): void
    {
        $statement = $this->pdo->prepare('UPDATE admin_users SET email = ? WHERE id = ?');
        $statement->execute([$email, $id]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM admin_users WHERE id = ?');
        $statement->execute([$id]);
    }
}
