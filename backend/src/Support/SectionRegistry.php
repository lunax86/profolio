<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Registr sekcí veřejného webu pro administraci „Vzhled". Modulární sekce lze
 * zapnout/vypnout a přeuspořádat (uloženo v site_settings.sections jako JSON pole
 * [{ key, enabled }]); fixní sekce se zobrazují vždy a jsou tu jen informativně.
 * Frontend má vlastní seznam v lib/api.ts (dvě aplikace, sdílené jen datově).
 */
final class SectionRegistry
{
    /** Modulární sekce (key => popisek). Pořadí zde je výchozí. Každá síť má vlastní modul. */
    public const MODULAR = [
        'services' => 'Služby',
        'inquiry' => 'Poptávka',
        'portfolio' => 'Portfolio',
        'instagram' => 'Instagram',
    ];

    /** Fixní sekce (vždy zobrazené), jen pro přehled v administraci. */
    public const FIXED = [
        'hero' => 'Úvod (hero)',
        'footer' => 'Patička',
    ];

    /** @return list<string> */
    public static function modularKeys(): array
    {
        return array_keys(self::MODULAR);
    }

    /**
     * Vrátí modulární sekce ve zvoleném pořadí s viditelností: neznámé klíče zahodí,
     * chybějící doplní vypnuté na konec. Nevalidní/prázdný vstup → výchozí (vše
     * zapnuté kromě sociálních sítí, shodně s migrací).
     *
     * @return list<array{key: string, enabled: bool}>
     */
    public static function ordered(?string $json): array
    {
        $ordered = [];
        $seen = [];
        $decoded = $json !== null && $json !== '' ? json_decode($json, true) : null;
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                $key = is_array($item) ? (string) ($item['key'] ?? '') : '';
                if (isset(self::MODULAR[$key]) && !isset($seen[$key])) {
                    $ordered[] = ['key' => $key, 'enabled' => !empty($item['enabled'])];
                    $seen[$key] = true;
                }
            }
        }

        if ($ordered === []) {
            foreach (self::modularKeys() as $key) {
                $ordered[] = ['key' => $key, 'enabled' => $key !== 'instagram'];
            }

            return $ordered;
        }

        foreach (self::modularKeys() as $key) {
            if (!isset($seen[$key])) {
                $ordered[] = ['key' => $key, 'enabled' => false];
            }
        }

        return $ordered;
    }
}
