<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Config;

/**
 * Minimalistická implementace JWT (HS256) bez externí závislosti.
 */
final class Jwt
{
    public static function encode(array $payload, ?int $ttl = null): string
    {
        $secret = (string) Config::get('JWT_SECRET', 'insecure-secret');
        $ttl ??= Config::int('JWT_TTL', 3600);

        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $now = time();
        $payload = [...$payload, 'iat' => $now, 'exp' => $now + $ttl];

        $segments = [
            self::base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            self::base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR)),
        ];
        $signature = hash_hmac('sha256', implode('.', $segments), $secret, true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /** @return array<string, mixed>|null Payload nebo null při neplatném/expirovaném tokenu. */
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $secret = (string) Config::get('JWT_SECRET', 'insecure-secret');
        $expected = self::base64UrlEncode(
            hash_hmac('sha256', $headerB64 . '.' . $payloadB64, $secret, true)
        );
        if (!hash_equals($expected, $signatureB64)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payloadB64), true);
        if (!is_array($payload)) {
            return null;
        }
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
