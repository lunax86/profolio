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
    public function allow(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $fileHandle = $this->open($key);
        if ($fileHandle === false) {
            return true; // když nejde zapisovat, radši propustit než blokovat web
        }

        try {
            flock($fileHandle, LOCK_EX);
            $hits = $this->prune($fileHandle, $windowSeconds);
            if (count($hits) >= $maxAttempts) {
                return false;
            }
            $hits[] = time();
            $this->write($fileHandle, $hits);

            return true;
        } finally {
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);
        }
    }

    /** Je počet zaznamenaných pokusů v okně už na/nad limitem? (jen čte, nezapisuje) */
    public function tooMany(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $file = $this->path($key);
        if (!is_file($file)) {
            return false;
        }

        $now = time();
        $hits = array_filter(
            (array) (json_decode((string) @file_get_contents($file), true) ?: []),
            static fn ($timestamp): bool => is_int($timestamp) && $timestamp > $now - $windowSeconds,
        );

        return count($hits) >= $maxAttempts;
    }

    /** Zaznamená jeden pokus (např. neúspěšné přihlášení). */
    public function record(string $key, int $windowSeconds): void
    {
        $fileHandle = $this->open($key);
        if ($fileHandle === false) {
            return;
        }

        try {
            flock($fileHandle, LOCK_EX);
            $hits = $this->prune($fileHandle, $windowSeconds);
            $hits[] = time();
            $this->write($fileHandle, $hits);
        } finally {
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);
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
     * @param resource $fileHandle
     * @return array<int, int> platné (nezastaralé) záznamy
     */
    private function prune($fileHandle, int $windowSeconds): array
    {
        rewind($fileHandle);
        $rawContents = stream_get_contents($fileHandle) ?: '';
        $now = time();

        return array_values(array_filter(
            (array) (json_decode($rawContents, true) ?: []),
            static fn ($timestamp): bool => is_int($timestamp) && $timestamp > $now - $windowSeconds,
        ));
    }

    /**
     * @param resource       $fileHandle
     * @param array<int, int> $hits
     */
    private function write($fileHandle, array $hits): void
    {
        ftruncate($fileHandle, 0);
        rewind($fileHandle);
        fwrite($fileHandle, json_encode($hits));
    }
}
