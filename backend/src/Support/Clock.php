<?php

declare(strict_types=1);

namespace App\Support;

use App\Repository\SettingRepository;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

/**
 * Časová zóna webu podle nastavení (klíč `timezone`), s fallbackem na Europe/Prague.
 * Časy se v DB ukládají v UTC; tady se převádějí na lokální zónu pro zobrazení a
 * pro výpočet „dne" (návštěvnost). Jediné místo, které zná časovou zónu webu.
 */
final class Clock
{
    public const DEFAULT_TIMEZONE = 'Europe/Prague';
    private const DISPLAY_FORMAT = 'j. n. Y H:i';

    private static ?DateTimeZone $zone = null;

    /** Nastavená časová zóna webu (validovaná, s fallbackem). Cache v rámci requestu. */
    public static function zone(): DateTimeZone
    {
        if (self::$zone === null) {
            $name = (new SettingRepository())->get('timezone', self::DEFAULT_TIMEZONE) ?: self::DEFAULT_TIMEZONE;
            try {
                self::$zone = new DateTimeZone($name);
            } catch (Exception) {
                self::$zone = new DateTimeZone(self::DEFAULT_TIMEZONE);
            }
        }

        return self::$zone;
    }

    /** Aktuální čas v časové zóně webu. */
    public static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', self::zone());
    }

    /** Dnešní datum (Y-m-d) v časové zóně webu. */
    public static function today(): string
    {
        return self::now()->format('Y-m-d');
    }

    /** Převede uložený UTC čas na zónu webu a zformátuje ho pro zobrazení. */
    public static function formatUtc(?string $utcDateTime, ?string $format = null): string
    {
        $value = (string) $utcDateTime;
        try {
            return (new DateTimeImmutable($value, new DateTimeZone('UTC')))
                ->setTimezone(self::zone())
                ->format($format ?? self::DISPLAY_FORMAT);
        } catch (Exception) {
            return $value;
        }
    }
}
