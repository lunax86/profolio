<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Config;

/**
 * Zjištění nasazené verze (z .git) a porovnání s posledním commitem na GitHubu.
 * Čte .git soubory přímo (bez `exec git`) - obchází to i git "dubious ownership"
 * při běhu pod www-data. GitHub se dotahuje jen na vyžádání (tlačítko v adminu).
 */
final class Version
{
    private static function gitDir(): string
    {
        return dirname(Config::basePath()) . '/.git';
    }

    /** Plný SHA aktuálně nasazeného commitu, nebo null (není git checkout). */
    public static function current(): ?string
    {
        $head = trim((string) @file_get_contents(self::gitDir() . '/HEAD'));
        if ($head === '') {
            return null;
        }
        if (preg_match('/^[0-9a-f]{40}$/', $head)) {
            return $head; // detached HEAD
        }
        if (!str_starts_with($head, 'ref: ')) {
            return null;
        }
        $ref = substr($head, 5);

        $refFile = self::gitDir() . '/' . $ref;
        if (is_file($refFile)) {
            return trim((string) file_get_contents($refFile)) ?: null;
        }
        // fallback: packed-refs
        foreach (@file(self::gitDir() . '/packed-refs', FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if (str_ends_with($line, ' ' . $ref) && preg_match('/^[0-9a-f]{40}/', $line, $matches)) {
                return $matches[0];
            }
        }

        return null;
    }

    /** "owner/repo" z remote origin v .git/config, nebo null. */
    public static function repoSlug(): ?string
    {
        $config = (string) @file_get_contents(self::gitDir() . '/config');
        if (preg_match('#github\.com[:/]([^/]+)/([^/\s.]+)#', $config, $matches)) {
            return $matches[1] . '/' . $matches[2];
        }

        return null;
    }

    /**
     * Stav vůči GitHubu. Dotahuje se jen když je voláno (tlačítko).
     *
     * @return array{current:?string, latest:?string, slug:?string, upToDate:?bool, error:?string}
     */
    public static function status(): array
    {
        $current = self::current();
        $slug = self::repoSlug();
        $latest = null;
        $error = null;

        if ($slug === null) {
            $error = 'Nepodařilo se zjistit GitHub repozitář (chybí remote origin).';
        } else {
            $latest = self::fetchLatest($slug, $error);
        }

        return [
            'current' => $current,
            'latest' => $latest,
            'slug' => $slug,
            'upToDate' => ($current && $latest) ? ($current === $latest) : null,
            'error' => $error,
        ];
    }

    /** Poslední SHA na `main` z GitHub API (krátká cache, timeout, graceful fail). */
    private static function fetchLatest(string $slug, ?string &$error): ?string
    {
        $cacheFile = Config::basePath('/storage/version-cache.json');
        $cached = json_decode((string) @file_get_contents($cacheFile), true);
        if (is_array($cached) && ($cached['slug'] ?? null) === $slug && (time() - (int) ($cached['at'] ?? 0)) < 300) {
            return (string) $cached['sha'] ?: null;
        }

        $context = stream_context_create(['http' => [
            'method' => 'GET',
            'header' => "User-Agent: profolio-admin\r\nAccept: application/vnd.github+json\r\n",
            'timeout' => 5,
        ]]);
        $rawResponse = @file_get_contents("https://api.github.com/repos/{$slug}/commits/main", false, $context);
        if ($rawResponse === false) {
            $error = 'GitHub se nepodařilo kontaktovat (síť / limit API).';

            return null;
        }
        $data = json_decode($rawResponse, true);
        $sha = is_array($data) ? ($data['sha'] ?? null) : null;
        if (!is_string($sha)) {
            $error = 'Neočekávaná odpověď z GitHub API.';

            return null;
        }

        @file_put_contents($cacheFile, json_encode(['slug' => $slug, 'sha' => $sha, 'at' => time()]));

        return $sha;
    }
}
