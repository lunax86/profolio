<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Config;
use RuntimeException;

/**
 * Ukládání nahraných obrázků do storage/uploads a vrácení veřejné cesty.
 */
final class Uploader
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /**
     * @param array{tmp_name:string, name:string, size:int} $file záznam z $_FILES
     * @return string veřejná cesta (/uploads/xxx.jpg)
     */
    public static function store(array $file): string
    {
        $mime = mime_content_type($file['tmp_name']) ?: '';
        if (!isset(self::ALLOWED[$mime])) {
            throw new RuntimeException('Nepodporovaný typ souboru.');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Soubor je příliš velký (max 5 MB).');
        }

        $ext = self::ALLOWED[$mime];
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $target = Config::basePath('/storage/uploads/' . $name);

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            // fallback pro CLI/testy
            if (!rename($file['tmp_name'], $target)) {
                throw new RuntimeException('Uložení souboru selhalo.');
            }
        }

        return '/uploads/' . $name;
    }
}
