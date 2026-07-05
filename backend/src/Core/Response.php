<?php

declare(strict_types=1);

namespace App\Core;

/**
 * JSON HTTP odpověď.
 */
final class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function error(string $message, int $status = 400, array $extra = []): void
    {
        self::json(['error' => $message, ...$extra], $status);
    }

    public static function noContent(): void
    {
        http_response_code(204);
    }
}
