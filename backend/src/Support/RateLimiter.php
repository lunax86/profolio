<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Jednoduchý souborový rate-limiter (klouzavé okno).
 * Bez DB – stav se drží v malých JSON souborech, jeden na klíč (např. IP).
 */
final class RateLimiter
{
    public function __construct(private readonly string $dir)
    {
    }

    /**
     * Zaznamená pokus a vrátí true, pokud je v rámci limitu.
     *
     * @param string $key           rozlišovací klíč (např. "inquiry:1.2.3.4")
     * @param int    $max           max. počet pokusů v okně
     * @param int    $windowSeconds délka okna v sekundách
     */
    public function allow(string $key, int $max, int $windowSeconds): bool
    {
        if (!is_dir($this->dir) && !@mkdir($this->dir, 0775, true) && !is_dir($this->dir)) {
            return true; // když nejde vytvořit úložiště, radši propustit než blokovat web
        }

        $file = $this->dir . '/' . sha1($key) . '.json';
        $now = time();

        $fh = @fopen($file, 'c+');
        if ($fh === false) {
            return true;
        }

        try {
            flock($fh, LOCK_EX);
            $raw = stream_get_contents($fh) ?: '';
            /** @var array<int, int> $hits */
            $hits = array_values(array_filter(
                (array) (json_decode($raw, true) ?: []),
                static fn ($ts): bool => is_int($ts) && $ts > $now - $windowSeconds,
            ));

            if (count($hits) >= $max) {
                return false;
            }

            $hits[] = $now;
            ftruncate($fh, 0);
            rewind($fh);
            fwrite($fh, json_encode($hits));

            return true;
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    }
}
