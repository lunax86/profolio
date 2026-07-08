<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Support\Clock;
use PDO;

final class PageViewRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /** Zaznamená návštěvníka pro daný den - jen jednou (INSERT OR IGNORE na den+hash). */
    public function record(string $day, string $visitorHash): void
    {
        $this->pdo
            ->prepare('INSERT OR IGNORE INTO page_views (day, visitor_hash) VALUES (?, ?)')
            ->execute([$day, $visitorHash]);
    }

    /**
     * Základní statistiky (unikátní návštěvníci za den).
     *
     * @return array{today:int, last7:int, total:int, perDay:array<int, array{day:string, count:int}>}
     */
    public function stats(int $chartDays = 14): array
    {
        $now = Clock::now();
        $today = $now->format('Y-m-d');
        $weekAgo = $now->modify('-6 days')->format('Y-m-d');
        $chartFrom = $now->modify('-' . ($chartDays - 1) . ' days')->format('Y-m-d');

        $count = function (string $sql, array $args = []): int {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($args);

            return (int) $statement->fetchColumn();
        };

        // Naměřené dny → mapa den => počet
        $statement = $this->pdo->prepare(
            'SELECT day, COUNT(*) AS c FROM page_views WHERE day >= ? GROUP BY day'
        );
        $statement->execute([$chartFrom]);
        $byDay = [];
        foreach ($statement->fetchAll() as $row) {
            $byDay[(string) $row['day']] = (int) $row['c'];
        }

        // Souvislá řada i s prázdnými dny (count 0)
        $perDay = [];
        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $day = $now->modify('-' . $i . ' days')->format('Y-m-d');
            $perDay[] = ['day' => $day, 'count' => $byDay[$day] ?? 0];
        }

        return [
            'today' => $count('SELECT COUNT(*) FROM page_views WHERE day = ?', [$today]),
            'last7' => $count('SELECT COUNT(*) FROM page_views WHERE day >= ?', [$weekAgo]),
            'total' => $count('SELECT COUNT(*) FROM page_views'),
            'perDay' => $perDay,
        ];
    }
}
