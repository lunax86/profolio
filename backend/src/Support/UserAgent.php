<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Lehké odvození zařízení/prohlížeče/OS z User-Agent hlavičky.
 * Záměrně hrubé (žádná externí knihovna): ukládáme jen štítky, ne syrový UA.
 */
final class UserAgent
{
    /**
     * @return array{device: string, browser: string, os: string}
     */
    public static function parse(string $userAgent): array
    {
        if ($userAgent === '') {
            return ['device' => 'Neznámé', 'browser' => 'Neznámý', 'os' => 'Neznámý'];
        }

        if (self::isBot($userAgent)) {
            return ['device' => 'Bot', 'browser' => self::botName($userAgent), 'os' => '-'];
        }

        return [
            'device' => self::device($userAgent),
            'browser' => self::browser($userAgent),
            'os' => self::os($userAgent),
        ];
    }

    private static function isBot(string $userAgent): bool
    {
        return (bool) preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|embedly|preview|monitor|curl|wget|python-requests|headless/i', $userAgent);
    }

    private static function botName(string $userAgent): string
    {
        foreach (['Googlebot', 'Bingbot', 'DuckDuckBot', 'YandexBot', 'facebookexternalhit', 'Applebot', 'PetalBot', 'AhrefsBot', 'SemrushBot'] as $known) {
            if (stripos($userAgent, $known) !== false) {
                return $known;
            }
        }

        return 'Robot';
    }

    private static function device(string $userAgent): string
    {
        if (preg_match('/iPad|Tablet|PlayBook|Silk|(Android(?!.*Mobile))/i', $userAgent)) {
            return 'Tablet';
        }
        if (preg_match('/Mobi|iPhone|iPod|Android.*Mobile|Windows Phone|IEMobile/i', $userAgent)) {
            return 'Mobil';
        }

        return 'Desktop';
    }

    private static function browser(string $userAgent): string
    {
        // Pořadí je důležité: odvozené prohlížeče (Edge/Opera) se hlásí i jako Chrome.
        return match (true) {
            (bool) preg_match('/Edg[A-Z]?\//', $userAgent) => 'Edge',
            (bool) preg_match('/OPR\/|Opera/', $userAgent) => 'Opera',
            (bool) preg_match('/SamsungBrowser/', $userAgent) => 'Samsung Internet',
            (bool) preg_match('/Firefox\/|FxiOS/', $userAgent) => 'Firefox',
            (bool) preg_match('/Chrome\/|CriOS/', $userAgent) => 'Chrome',
            (bool) preg_match('/Safari\//', $userAgent) => 'Safari',
            default => 'Jiný',
        };
    }

    private static function os(string $userAgent): string
    {
        return match (true) {
            (bool) preg_match('/iPhone|iPad|iPod|iOS|CriOS|FxiOS/', $userAgent) => 'iOS',
            (bool) preg_match('/Android/', $userAgent) => 'Android',
            (bool) preg_match('/Windows NT|Windows Phone/', $userAgent) => 'Windows',
            (bool) preg_match('/Mac OS X|Macintosh/', $userAgent) => 'macOS',
            (bool) preg_match('/CrOS/', $userAgent) => 'ChromeOS',
            (bool) preg_match('/Linux/', $userAgent) => 'Linux',
            default => 'Jiný',
        };
    }
}
