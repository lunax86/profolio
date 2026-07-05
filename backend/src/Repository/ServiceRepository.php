<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

final class ServiceRepository
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
            ->query('SELECT * FROM services ORDER BY sort_order, id')
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM services WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO services (title, description, icon, sort_order)
             VALUES (:title, :description, :icon, :sort_order)'
        );
        $stmt->execute($this->fields($data));

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE services SET title = :title, description = :description,
             icon = :icon, sort_order = :sort_order WHERE id = :id'
        );
        $stmt->execute([...$this->fields($data), 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
    }

    /** @return array<string, mixed> */
    private function fields(array $data): array
    {
        return [
            'title' => (string) ($data['title'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'icon' => (string) ($data['icon'] ?? 'sparkles'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
