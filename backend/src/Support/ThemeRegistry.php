<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Zdroj pravdy pro barevné varianty webu: neutrální „shade" (slate/stone/zinc)
 * a „accent" (primární barva). Volba se ukládá v site_settings (theme_shade,
 * theme_accent); {@see ThemeRenderer} z ní složí CSS proměnné do <head>.
 * Hodnoty jsou HSL složky bez obalu hsl(), stejně jako v index.css.
 */
final class ThemeRegistry
{
    public const DEFAULT_SHADE = 'slate';
    public const DEFAULT_ACCENT = 'indigo';

    /**
     * Neutrální varianty; každá má tokeny pro světlý (:root) i tmavý (.dark) režim.
     *
     * @return array<string, array{label: string, hint: string, swatch: array{string, string}, light: array<string, string>, dark: array<string, string>}>
     */
    public static function shades(): array
    {
        return [
            'slate' => [
                'label' => 'Slate',
                'hint' => 'chladná',
                'swatch' => ['214 32% 91%', '222 47% 11%'],
                'light' => self::neutralTokens('0 0% 100%', '222 47% 11%', '0 0% 100%', '210 40% 96%', '215 16% 47%', '214 32% 91%'),
                'dark' => self::neutralTokens('222 47% 11%', '210 40% 98%', '222 44% 13%', '217 33% 18%', '215 20% 65%', '217 33% 20%'),
            ],
            'stone' => [
                'label' => 'Stone',
                'hint' => 'hřejivá',
                'swatch' => ['20 6% 90%', '20 14% 8%'],
                'light' => self::neutralTokens('0 0% 100%', '20 14% 8%', '0 0% 100%', '60 5% 96%', '25 5% 45%', '20 6% 90%'),
                'dark' => self::neutralTokens('20 14% 8%', '60 9% 98%', '20 12% 10%', '12 7% 15%', '24 5% 64%', '12 7% 18%'),
            ],
            'zinc' => [
                'label' => 'Zinc',
                'hint' => 'neutrální',
                'swatch' => ['240 6% 90%', '240 10% 9%'],
                'light' => self::neutralTokens('0 0% 100%', '240 10% 9%', '0 0% 100%', '240 5% 96%', '240 4% 46%', '240 6% 90%'),
                'dark' => self::neutralTokens('240 10% 9%', '0 0% 98%', '240 8% 11%', '240 4% 16%', '240 5% 65%', '240 4% 19%'),
            ],
        ];
    }

    /**
     * Accent varianty (primární barva). Světlé odstíny (zelené, amber, pastelová
     * magenta) mají tmavý text kvůli kontrastu, tmavší (violet/blue/rose) bílý.
     *
     * @return array<string, array{label: string, hint: string, light: array<string, string>, dark: array<string, string>}>
     */
    public static function accents(): array
    {
        return [
            'indigo' => self::accentTokens('Indigo', 'solidní', 243, 75, 59, 66, '0 0% 100%'),
            'violet' => self::accentTokens('Violet', 'kreativní', 262, 68, 56, 66, '0 0% 100%'),
            'teal' => self::accentTokens('Teal', 'svěží', 178, 74, 48, 56, '185 70% 11%'),
            'emerald' => self::accentTokens('Emerald', 'přírodní', 158, 72, 46, 54, '158 65% 10%'),
            'amber' => self::accentTokens('Amber', 'energická', 38, 92, 54, 60, '30 50% 13%'),
            'magenta' => self::accentTokens('Magenta', 'hravá', 300, 66, 64, 66, '305 45% 18%'),
            'rose' => self::accentTokens('Rose', 'elegantní', 347, 75, 52, 60, '0 0% 100%'),
        ];
    }

    /**
     * Složí CSS proměnné pro zvolený shade + accent. Neznámá volba spadne na výchozí.
     *
     * @return array{root: array<string, string>, dark: array<string, string>}
     */
    public static function cssVariables(string $shade, string $accent): array
    {
        $shades = self::shades();
        $accents = self::accents();
        $shadeDefinition = $shades[$shade] ?? $shades[self::DEFAULT_SHADE];
        $accentDefinition = $accents[$accent] ?? $accents[self::DEFAULT_ACCENT];

        return [
            'root' => array_merge($shadeDefinition['light'], $accentDefinition['light']),
            'dark' => array_merge($shadeDefinition['dark'], $accentDefinition['dark']),
        ];
    }

    public static function isShade(string $shade): bool
    {
        return isset(self::shades()[$shade]);
    }

    public static function isAccent(string $accent): bool
    {
        return isset(self::accents()[$accent]);
    }

    /**
     * Neutrální tokeny; card-foreground = foreground, secondary = muted,
     * secondary-foreground = foreground, input = border (konvence z index.css).
     *
     * @return array<string, string>
     */
    private static function neutralTokens(string $background, string $foreground, string $card, string $muted, string $mutedForeground, string $border): array
    {
        return [
            '--background' => $background,
            '--foreground' => $foreground,
            '--card' => $card,
            '--card-foreground' => $foreground,
            '--secondary' => $muted,
            '--secondary-foreground' => $foreground,
            '--muted' => $muted,
            '--muted-foreground' => $mutedForeground,
            '--border' => $border,
            '--input' => $border,
        ];
    }

    /**
     * Accent tokeny; ring = primary. Lightness se liší pro světlý a tmavý režim.
     *
     * @return array{label: string, hint: string, light: array<string, string>, dark: array<string, string>}
     */
    private static function accentTokens(string $label, string $hint, int $hue, int $saturation, int $lightnessLight, int $lightnessDark, string $foreground): array
    {
        $primaryLight = "{$hue} {$saturation}% {$lightnessLight}%";
        $primaryDark = "{$hue} {$saturation}% {$lightnessDark}%";

        return [
            'label' => $label,
            'hint' => $hint,
            'light' => [
                '--primary' => $primaryLight,
                '--primary-foreground' => $foreground,
                '--ring' => $primaryLight,
            ],
            'dark' => [
                '--primary' => $primaryDark,
                '--primary-foreground' => $foreground,
                '--ring' => $primaryDark,
            ],
        ];
    }
}
