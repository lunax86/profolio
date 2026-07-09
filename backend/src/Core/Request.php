<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Obálka nad příchozím HTTP požadavkem.
 */
final class Request
{
    /** @param array<string, string> $params URL parametry (např. {id}) */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private readonly array $server,
        public array $params = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';

        $body = [];
        $raw = file_get_contents('php://input') ?: '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json') && $raw !== '') {
            $body = json_decode($raw, true) ?? [];
        } elseif (!empty($_POST)) {
            $body = $_POST;
        }

        return new self($method, $path, $_GET, $body, $_SERVER);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return [...$this->query, ...$this->body];
    }

    public function param(string $key, ?string $default = null): ?string
    {
        return $this->params[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $this->server['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function clientIp(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '');
    }

    public function userAgent(): string
    {
        return (string) ($this->server['HTTP_USER_AGENT'] ?? '');
    }

    /** Hodnota libovolné HTTP hlavičky (např. „Referer", „Accept-Language"). */
    public function header(string $name): string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return (string) ($this->server[$key] ?? '');
    }
}
