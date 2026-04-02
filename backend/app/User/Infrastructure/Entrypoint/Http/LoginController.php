<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\Auth\LoginUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController
{
    public function __construct(
        private LoginUser $loginUser,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $response = ($this->loginUser)(
                $validated['email'],
                $validated['password'],
            );
        } catch (\InvalidArgumentException) {
            return new JsonResponse([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
