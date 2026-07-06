<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

final class InquiryRepository
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
            ->query('SELECT * FROM inquiries ORDER BY created_at DESC, id DESC')
            ->fetchAll();
    }

    /** Aktivní (nearchivované) poptávky.
     * @return array<int, array<string, mixed>> */
    public function active(): array
    {
        return $this->pdo
            ->query('SELECT * FROM inquiries WHERE is_archived = 0 ORDER BY created_at DESC, id DESC')
            ->fetchAll();
    }

    /** Archivované poptávky.
     * @return array<int, array<string, mixed>> */
    public function archived(): array
    {
        return $this->pdo
            ->query('SELECT * FROM inquiries WHERE is_archived = 1 ORDER BY created_at DESC, id DESC')
            ->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO inquiries (name, email, phone, message)
             VALUES (:name, :email, :phone, :message)'
        );
        $stmt->execute([
            'name' => (string) ($data['name'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'phone' => (string) ($data['phone'] ?? ''),
            'message' => (string) ($data['message'] ?? ''),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function markRead(int $id): void
    {
        $this->pdo->prepare('UPDATE inquiries SET is_read = 1 WHERE id = ?')->execute([$id]);
    }

    public function setArchived(int $id, bool $archived): void
    {
        $this->pdo
            ->prepare('UPDATE inquiries SET is_archived = ? WHERE id = ?')
            ->execute([$archived ? 1 : 0, $id]);
    }

    /** Trvale smaže poptávku – jen pokud je archivovaná (pojistka proti smazání aktivní). */
    public function deleteArchived(int $id): void
    {
        $this->pdo
            ->prepare('DELETE FROM inquiries WHERE id = ? AND is_archived = 1')
            ->execute([$id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM inquiries WHERE id = ?')->execute([$id]);
    }

    public function unreadCount(): int
    {
        return (int) $this->pdo
            ->query('SELECT COUNT(*) FROM inquiries WHERE is_read = 0 AND is_archived = 0')
            ->fetchColumn();
    }
}
