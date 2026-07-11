<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Vloží do statického HTML shellu (z Vite buildu) SEO meta z nastavení webu:
 * title, description, canonical, Open Graph, Twitter card a JSON-LD LocalBusiness.
 * Díky tomu je vidí i roboti a sociální sítě, které nespouští JavaScript.
 */
final class SeoRenderer
{
    /**
     * @param array<string, string> $settings
     */
    public static function render(string $shellHtml, array $settings, string $baseUrl, string $path): string
    {
        $siteTitle = self::settingValue($settings, 'site_title', 'Firemní web');
        // Obecný slogan je základ; fallback na hlavní text hera (hero_title).
        $slogan = self::settingValue($settings, 'slogan', self::settingValue($settings, 'hero_title', ''));

        // Přednost mají vlastní SEO pole z administrace, jinak se odvodí z názvu/sloganu.
        $derivedTitle = $slogan !== '' ? $siteTitle . ' - ' . $slogan : $siteTitle;
        $title = self::settingValue($settings, 'seo_title', $derivedTitle);
        $description = self::settingValue($settings, 'seo_description', $slogan !== '' ? $slogan : $siteTitle);
        $robots = ($settings['seo_index'] ?? '1') === '0' ? 'noindex,nofollow' : 'index,follow';

        $baseUrl = rtrim($baseUrl, '/');
        $canonical = $baseUrl . ($path === '' ? '/' : $path);

        $image = self::settingValue($settings, 'seo_image', self::settingValue($settings, 'hero_image', ''));
        if ($image !== '' && !preg_match('#^https?://#', $image)) {
            $image = $baseUrl . '/' . ltrim($image, '/');
        }

        $favicon = self::settingValue($settings, 'favicon_path', '');

        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        $tags = [
            '<meta name="description" content="' . $escape($description) . '">',
            '<link rel="canonical" href="' . $escape($canonical) . '">',
            '<meta name="robots" content="' . $robots . '">',
            '<meta property="og:type" content="website">',
            '<meta property="og:site_name" content="' . $escape($siteTitle) . '">',
            '<meta property="og:title" content="' . $escape($title) . '">',
            '<meta property="og:description" content="' . $escape($description) . '">',
            '<meta property="og:url" content="' . $escape($canonical) . '">',
            '<meta property="og:locale" content="cs_CZ">',
            '<meta name="twitter:card" content="' . ($image !== '' ? 'summary_large_image' : 'summary') . '">',
            '<meta name="twitter:title" content="' . $escape($title) . '">',
            '<meta name="twitter:description" content="' . $escape($description) . '">',
        ];
        if ($image !== '') {
            $tags[] = '<meta property="og:image" content="' . $escape($image) . '">';
            $tags[] = '<meta name="twitter:image" content="' . $escape($image) . '">';
        }
        if ($favicon !== '') {
            $tags[] = '<link rel="icon" href="' . $escape($favicon) . '">';
        }

        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $siteTitle,
            'url' => $baseUrl . '/',
            'image' => $image !== '' ? $image : null,
            'email' => self::settingValue($settings, 'contact_email', '') ?: null,
            'telephone' => self::settingValue($settings, 'contact_phone', '') ?: null,
            'address' => self::settingValue($settings, 'contact_address', '') ?: null,
        ], static fn ($value): bool => $value !== null && $value !== '');
        $tags[] = '<script type="application/ld+json">'
            . json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>';

        $head = implode("\n    ", $tags);

        // Nahraď statický <title> dynamickým a odstraň statický description (vkládáme vlastní).
        $html = preg_replace('#<title>.*?</title>#is', '<title>' . $escape($title) . '</title>', $shellHtml, 1) ?? $shellHtml;
        $html = preg_replace('#\s*<meta\s+name="description"[^>]*>#i', '', $html, 1) ?? $html;
        // Vlastní favicon z administrace nahradí statický z buildu.
        if ($favicon !== '') {
            $html = preg_replace('#\s*<link[^>]*rel="icon"[^>]*>#i', '', $html, 1) ?? $html;
        }

        return str_replace('</head>', '    ' . $head . "\n  </head>", $html);
    }

    /** @param array<string, string> $settings */
    private static function settingValue(array $settings, string $key, string $default): string
    {
        $value = trim((string) ($settings[$key] ?? ''));

        return $value !== '' ? $value : $default;
    }
}
