<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Core\Request;
use App\Core\Response;
use App\Support\Auth;
use OpenApi\Attributes as OA;

final class AuthController
{
    public function __construct(private readonly Auth $auth = new Auth())
    {
    }

    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Přihlášení administrátora, vrací JWT token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'JWT token'),
            new OA\Response(response: 401, description: 'Neplatné přihlašovací údaje'),
        ]
    )]
    public function login(Request $request): void
    {
        $token = $this->auth->attempt(
            (string) $request->input('email', ''),
            (string) $request->input('password', ''),
        );

        if ($token === null) {
            Response::error('Neplatné přihlašovací údaje', 401);

            return;
        }

        Response::json(['token' => $token, 'token_type' => 'Bearer']);
    }
}
