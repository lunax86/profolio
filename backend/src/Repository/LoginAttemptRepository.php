<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

final class LoginAttemptRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function record(string $email, string $ip, bool $success): void
    {
        // Ořízni délku před zápisem, ať nelze log nafouknout obřím vstupem z login pole.
        // 254 = max délka platné e-mailové adresy dle RFC 5321; 45 = max délka IPv6 se zónou.
        $email = mb_substr($email, 0, 254);
        $ip = mb_substr($ip, 0, 45);
        $this->pdo
            ->prepare('INSERT INTO login_attempts (email, ip, success) VALUES (?, ?, ?)')
            ->execute([$email, $ip, $success ? 1 : 0]);
    }

    /**
     * Pokusy za posledních `$seconds` sekund (nejnovější první, omezeno na `$limit`).
     *
     * @return array<int, array<string, mixed>>
     */
    public function since(int $seconds, int $limit = 200): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM login_attempts WHERE created_at >= :cutoff ORDER BY id DESC LIMIT :limit'
        );
        $statement->bindValue(':cutoff', self::cutoff($seconds));
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /** Počet pokusů za posledních `$seconds` sekund (volitelně jen neúspěšných). */
    public function countSince(int $seconds, bool $onlyFailed = false): int
    {
        $condition = $onlyFailed ? 'created_at >= ? AND success = 0' : 'created_at >= ?';
        $statement = $this->pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE {$condition}");
        $statement->execute([self::cutoff($seconds)]);

        return (int) $statement->fetchColumn();
    }

    /**
     * IP adresy, které jsou právě blokované rate-limitem
     * (≥ `$maxAttempts` neúspěchů za `$windowSeconds`).
     *
     * @return array<int, array{ip: string, count: int}>
     */
    public function blockedIps(int $maxAttempts, int $windowSeconds): array
    {
        $statement = $this->pdo->prepare(
            'SELECT ip, COUNT(*) AS attempts FROM login_attempts
             WHERE success = 0 AND created_at >= :cutoff
             GROUP BY ip HAVING COUNT(*) >= :maxAttempts
             ORDER BY attempts DESC'
        );
        $statement->bindValue(':cutoff', self::cutoff($windowSeconds));
        $statement->bindValue(':maxAttempts', $maxAttempts, PDO::PARAM_INT);
        $statement->execute();

        $blocked = [];
        foreach ($statement->fetchAll() as $row) {
            $blocked[] = ['ip' => (string) $row['ip'], 'count' => (int) $row['attempts']];
        }

        return $blocked;
    }

    /** UTC hranice „před N sekundami" ve formátu SQLite datetime('now'). */
    private static function cutoff(int $seconds): string
    {
        return gmdate('Y-m-d H:i:s', time() - $seconds);
    }
}
