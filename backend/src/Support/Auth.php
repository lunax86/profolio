<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Request;
use App\Core\Response;
use App\Repository\AdminUserRepository;

/**
 * Autentizace: přihlášení, vydání JWT a middleware pro API i session pro admin UI.
 */
final class Auth
{
    public function __construct(
        private readonly AdminUserRepository $users = new AdminUserRepository(),
    ) {
    }

    public function attempt(string $email, string $password): ?string
    {
        $user = $this->users->findByEmail($email);
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        return Jwt::encode(['sub' => (int) $user['id'], 'email' => $user['email']]);
    }

    /**
     * Middleware pro API: ověří Bearer token. Vrací false a odešle 401 při selhání.
     */
    public static function apiMiddleware(): callable
    {
        return static function (Request $request): bool {
            $token = $request->bearerToken();
            if ($token === null || Jwt::decode($token) === null) {
                Response::error('Unauthorized', 401);

                return false;
            }

            return true;
        };
    }

    // --- Session (admin UI) ---

    public static function login(array $user): void
    {
        self::ensureSession();
        $_SESSION['admin_id'] = (int) $user['id'];
        $_SESSION['admin_email'] = $user['email'];
    }

    public static function logout(): void
    {
        self::ensureSession();
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        self::ensureSession();

        return isset($_SESSION['admin_id']);
    }

    public static function user(): ?array
    {
        return self::check()
            ? ['id' => $_SESSION['admin_id'], 'email' => $_SESSION['admin_email']]
            : null;
    }

    public static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}
