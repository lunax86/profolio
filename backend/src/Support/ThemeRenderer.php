<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Vloží do <head> statického HTML shellu <style> s CSS proměnnými zvoleného
 * barevného tématu (shade + accent z nastavení). Vkládá se na konec <head>,
 * tedy za bundlovaný CSS z Vite buildu, takže přepíše výchozí hodnoty v index.css
 * bez probliknutí. SEO řeší samostatně {@see SeoRenderer}.
 */
final class ThemeRenderer
{
    /**
     * @param array<string, string> $settings
     */
    public static function render(string $shellHtml, array $settings): string
    {
        $shade = trim((string) ($settings['theme_shade'] ?? '')) ?: ThemeRegistry::DEFAULT_SHADE;
        $accent = trim((string) ($settings['theme_accent'] ?? '')) ?: ThemeRegistry::DEFAULT_ACCENT;

        $variables = ThemeRegistry::cssVariables($shade, $accent);

        // Hodnoty pocházejí z interního registru (ne z uživatelského vstupu),
        // klíče shade/accent slouží jen k vyhledání, do výstupu se neinterpolují.
        $style = '<style id="app-theme">'
            . ':root{' . self::declarations($variables['root']) . '}'
            . '.dark{' . self::declarations($variables['dark']) . '}'
            . '</style>';

        return str_replace('</head>', '    ' . $style . "\n  </head>", $shellHtml);
    }

    /**
     * @param array<string, string> $variables
     */
    private static function declarations(array $variables): string
    {
        $declarations = '';
        foreach ($variables as $name => $value) {
            $declarations .= $name . ':' . $value . ';';
        }

        return $declarations;
    }
}
