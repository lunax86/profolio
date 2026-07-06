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
     * Vhodné pro jednorázovou ochranu (např. odeslání formuláře).
     */
    public function allow(string $key, int $max, int $windowSeconds): bool
    {
        $fh = $this->open($key);
        if ($fh === false) {
            return true; // když nejde zapisovat, radši propustit než blokovat web
        }

        try {
            flock($fh, LOCK_EX);
            $hits = $this->prune($fh, $windowSeconds);
            if (count($hits) >= $max) {
                return false;
            }
            $hits[] = time();
            $this->write($fh, $hits);

            return true;
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    }

    /** Je počet zaznamenaných pokusů v okně už na/nad limitem? (jen čte, nezapisuje) */
    public function tooMany(string $key, int $max, int $windowSeconds): bool
    {
        $file = $this->path($key);
        if (!is_file($file)) {
            return false;
        }

        $now = time();
        $hits = array_filter(
            (array) (json_decode((string) @file_get_contents($file), true) ?: []),
            static fn ($ts): bool => is_int($ts) && $ts > $now - $windowSeconds,
        );

        return count($hits) >= $max;
    }

    /** Zaznamená jeden pokus (např. neúspěšné přihlášení). */
    public function record(string $key, int $windowSeconds): void
    {
        $fh = $this->open($key);
        if ($fh === false) {
            return;
        }

        try {
            flock($fh, LOCK_EX);
            $hits = $this->prune($fh, $windowSeconds);
            $hits[] = time();
            $this->write($fh, $hits);
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    }

    private function path(string $key): string
    {
        return $this->dir . '/' . sha1($key) . '.json';
    }

    /** @return resource|false */
    private function open(string $key)
    {
        if (!is_dir($this->dir) && !@mkdir($this->dir, 0775, true) && !is_dir($this->dir)) {
            return false;
        }

        return @fopen($this->path($key), 'c+');
    }

    /**
     * @param resource $fh
     * @return array<int, int> platné (nezastaralé) záznamy
     */
    private function prune($fh, int $windowSeconds): array
    {
        rewind($fh);
        $raw = stream_get_contents($fh) ?: '';
        $now = time();

        return array_values(array_filter(
            (array) (json_decode($raw, true) ?: []),
            static fn ($ts): bool => is_int($ts) && $ts > $now - $windowSeconds,
        ));
    }

    /**
     * @param resource       $fh
     * @param array<int, int> $hits
     */
    private function write($fh, array $hits): void
    {
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($hits));
    }
}
