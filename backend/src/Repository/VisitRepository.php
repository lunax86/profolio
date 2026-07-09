<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Support\Clock;
use PDO;

final class VisitRepository
{
    /** Sloupce povolené pro rozpady (whitelist proti SQL injection). */
    private const BREAKDOWN_COLUMNS = ['referrer_host', 'device', 'browser', 'os', 'language'];

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /**
     * Zaznamená jeden přístup.
     *
     * @param array{day:string, visitor_hash:string, referrer_host:string, device:string, browser:string, os:string, language:string} $visit
     */
    public function record(array $visit): void
    {
        $this->pdo->prepare(
            'INSERT INTO visits (day, visitor_hash, referrer_host, device, browser, os, language)
             VALUES (:day, :visitor_hash, :referrer_host, :device, :browser, :os, :language)'
        )->execute($visit);
    }

    /**
     * Souhrn pro dashboard (unikátní návštěvníci za den) - zachovává původní význam čísel.
     *
     * @return array{today:int, last7:int, total:int, perDay:array<int, array{day:string, count:int}>}
     */
    public function stats(int $chartDays = 14): array
    {
        $now = Clock::now();
        $today = $now->format('Y-m-d');
        $weekAgo = $now->modify('-6 days')->format('Y-m-d');
        $chartFrom = $now->modify('-' . ($chartDays - 1) . ' days')->format('Y-m-d');

        // Souvislá denní řada s prázdnými dny (unikáti = DISTINCT visitor_hash za den).
        $statement = $this->pdo->prepare(
            'SELECT day, COUNT(DISTINCT visitor_hash) AS c FROM visits WHERE day >= ? GROUP BY day'
        );
        $statement->execute([$chartFrom]);
        $byDay = [];
        foreach ($statement->fetchAll() as $row) {
            $byDay[(string) $row['day']] = (int) $row['c'];
        }
        $perDay = [];
        for ($index = $chartDays - 1; $index >= 0; $index--) {
            $day = $now->modify('-' . $index . ' days')->format('Y-m-d');
            $perDay[] = ['day' => $day, 'count' => $byDay[$day] ?? 0];
        }

        return [
            'today' => $this->scalar('SELECT COUNT(DISTINCT visitor_hash) FROM visits WHERE day = ?', [$today]),
            // last7 a total: součet denních unikátů (návštěvník počítán jednou za každý den).
            'last7' => $this->scalar('SELECT COUNT(*) FROM (SELECT 1 FROM visits WHERE day >= ? GROUP BY day, visitor_hash)', [$weekAgo]),
            'total' => $this->scalar('SELECT COUNT(*) FROM (SELECT 1 FROM visits GROUP BY day, visitor_hash)'),
            'perDay' => $perDay,
        ];
    }

    /**
     * Unikáti (DISTINCT visitor_hash) a přístupy (počet řádků) za posledních N sekund.
     *
     * @return array{uniques:int, hits:int}
     */
    public function periodSummary(int $seconds): array
    {
        $modifier = '-' . $seconds . ' seconds';

        return [
            'uniques' => $this->scalar("SELECT COUNT(DISTINCT visitor_hash) FROM visits WHERE created_at >= datetime('now', ?)", [$modifier]),
            'hits' => $this->scalar("SELECT COUNT(*) FROM visits WHERE created_at >= datetime('now', ?)", [$modifier]),
        ];
    }

    /**
     * Rozpad přístupů podle sloupce (top hodnoty za období).
     *
     * @return array<int, array{label:string, count:int}>
     */
    public function breakdown(string $column, int $seconds, int $limit = 8): array
    {
        if (!in_array($column, self::BREAKDOWN_COLUMNS, true)) {
            throw new \InvalidArgumentException("Nepovolený sloupec: {$column}");
        }
        $statement = $this->pdo->prepare(
            "SELECT {$column} AS label, COUNT(*) AS c
             FROM visits
             WHERE created_at >= datetime('now', ?)
             GROUP BY {$column}
             ORDER BY c DESC, label ASC
             LIMIT ?"
        );
        $statement->bindValue(1, '-' . $seconds . ' seconds');
        $statement->bindValue(2, $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map(
            static fn (array $row): array => ['label' => (string) $row['label'], 'count' => (int) $row['c']],
            $statement->fetchAll()
        );
    }

    /**
     * Poslední přístupy za období.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $seconds, int $limit = 100): array
    {
        $statement = $this->pdo->prepare(
            "SELECT created_at, referrer_host, device, browser, os, language
             FROM visits
             WHERE created_at >= datetime('now', ?)
             ORDER BY id DESC
             LIMIT ?"
        );
        $statement->bindValue(1, '-' . $seconds . ' seconds');
        $statement->bindValue(2, $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /** @param array<int, mixed> $args */
    private function scalar(string $sql, array $args = []): int
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($args);

        return (int) $statement->fetchColumn();
    }
}
