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

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE email = ?');
        $statement->execute([$email]);

        return $statement->fetch() ?: null;
    }

    public function create(string $email, string $passwordHash): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_users (email, password_hash) VALUES (?, ?)'
        );
        $statement->execute([$email, $passwordHash]);

        return (int) $this->pdo->lastInsertId();
    }
}
