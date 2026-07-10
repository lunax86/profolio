<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

final class PortfolioRepository
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
            ->query('SELECT * FROM portfolio ORDER BY sort_order, id')
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM portfolio WHERE id = ?');
        $statement->execute([$id]);

        return $statement->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO portfolio (title, description, image_path, image_before, sort_order)
             VALUES (:title, :description, :image_path, :image_before, :sort_order)'
        );
        $statement->execute($this->fields($data));

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE portfolio SET title = :title, description = :description,
             image_path = :image_path, image_before = :image_before, sort_order = :sort_order WHERE id = :id'
        );
        $statement->execute([...$this->fields($data), 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM portfolio WHERE id = ?')->execute([$id]);
    }

    /** @return array<string, mixed> */
    private function fields(array $data): array
    {
        return [
            'title' => (string) ($data['title'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'image_path' => (string) ($data['image_path'] ?? ''),
            'image_before' => (string) ($data['image_before'] ?? ''),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
