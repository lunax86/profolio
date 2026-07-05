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
        $stmt = $this->pdo->prepare('SELECT * FROM portfolio WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO portfolio (title, description, image_path, sort_order)
             VALUES (:title, :description, :image_path, :sort_order)'
        );
        $stmt->execute($this->fields($data));

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE portfolio SET title = :title, description = :description,
             image_path = :image_path, sort_order = :sort_order WHERE id = :id'
        );
        $stmt->execute([...$this->fields($data), 'id' => $id]);
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
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
