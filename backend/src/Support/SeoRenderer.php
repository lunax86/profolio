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
        $siteTitle = self::val($settings, 'site_title', 'Firemní web');
        $slogan = self::val($settings, 'hero_slogan', self::val($settings, 'hero_title', ''));

        // Přednost mají vlastní SEO pole z administrace, jinak se odvodí z názvu/sloganu.
        $derivedTitle = $slogan !== '' ? $siteTitle . ' – ' . $slogan : $siteTitle;
        $title = self::val($settings, 'seo_title', $derivedTitle);
        $description = self::val($settings, 'seo_description', $slogan !== '' ? $slogan : $siteTitle);
        $robots = ($settings['seo_index'] ?? '1') === '0' ? 'noindex,nofollow' : 'index,follow';

        $baseUrl = rtrim($baseUrl, '/');
        $canonical = $baseUrl . ($path === '' ? '/' : $path);

        $image = self::val($settings, 'seo_image', self::val($settings, 'hero_image', ''));
        if ($image !== '' && !preg_match('#^https?://#', $image)) {
            $image = $baseUrl . '/' . ltrim($image, '/');
        }

        $favicon = self::val($settings, 'favicon_path', '');

        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        $tags = [
            '<meta name="description" content="' . $e($description) . '">',
            '<link rel="canonical" href="' . $e($canonical) . '">',
            '<meta name="robots" content="' . $robots . '">',
            '<meta property="og:type" content="website">',
            '<meta property="og:site_name" content="' . $e($siteTitle) . '">',
            '<meta property="og:title" content="' . $e($title) . '">',
            '<meta property="og:description" content="' . $e($description) . '">',
            '<meta property="og:url" content="' . $e($canonical) . '">',
            '<meta property="og:locale" content="cs_CZ">',
            '<meta name="twitter:card" content="' . ($image !== '' ? 'summary_large_image' : 'summary') . '">',
            '<meta name="twitter:title" content="' . $e($title) . '">',
            '<meta name="twitter:description" content="' . $e($description) . '">',
        ];
        if ($image !== '') {
            $tags[] = '<meta property="og:image" content="' . $e($image) . '">';
            $tags[] = '<meta name="twitter:image" content="' . $e($image) . '">';
        }
        if ($favicon !== '') {
            $tags[] = '<link rel="icon" href="' . $e($favicon) . '">';
        }

        $ld = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $siteTitle,
            'url' => $baseUrl . '/',
            'image' => $image !== '' ? $image : null,
            'email' => self::val($settings, 'contact_email', '') ?: null,
            'telephone' => self::val($settings, 'contact_phone', '') ?: null,
            'address' => self::val($settings, 'contact_address', '') ?: null,
        ], static fn ($v): bool => $v !== null && $v !== '');
        $tags[] = '<script type="application/ld+json">'
            . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>';

        $head = implode("\n    ", $tags);

        // Nahraď statický <title> dynamickým a odstraň statický description (vkládáme vlastní).
        $html = preg_replace('#<title>.*?</title>#is', '<title>' . $e($title) . '</title>', $shellHtml, 1) ?? $shellHtml;
        $html = preg_replace('#\s*<meta\s+name="description"[^>]*>#i', '', $html, 1) ?? $html;
        // Vlastní favicon z administrace nahradí statický z buildu.
        if ($favicon !== '') {
            $html = preg_replace('#\s*<link[^>]*rel="icon"[^>]*>#i', '', $html, 1) ?? $html;
        }

        return str_replace('</head>', '    ' . $head . "\n  </head>", $html);
    }

    /** @param array<string, string> $s */
    private static function val(array $s, string $key, string $default): string
    {
        $v = trim((string) ($s[$key] ?? ''));

        return $v !== '' ? $v : $default;
    }
}
