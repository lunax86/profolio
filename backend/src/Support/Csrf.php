<?php

declare(strict_types=1);

namespace App\Support;

/**
 * CSRF ochrana pro admin formuláře.
 */
final class Csrf
{
    public static function token(): string
    {
        Auth::ensureSession();
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    public static function check(?string $token): bool
    {
        Auth::ensureSession();

        return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
    }
}
