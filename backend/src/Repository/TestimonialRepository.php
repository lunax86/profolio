<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

final class TestimonialRepository
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
            ->query('SELECT * FROM testimonials ORDER BY sort_order, id')
            ->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO testimonials (author, text, role, sort_order)
             VALUES (:author, :text, :role, :sort_order)'
        );
        $statement->execute($this->fields($data));

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
    }

    /** @return array<string, mixed> */
    private function fields(array $data): array
    {
        return [
            'author' => (string) ($data['author'] ?? ''),
            'text' => (string) ($data['text'] ?? ''),
            'role' => (string) ($data['role'] ?? ''),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
