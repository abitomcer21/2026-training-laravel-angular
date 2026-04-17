<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\Auth\LogoutUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController
{
    public function __construct(
        private LogoutUser $logoutUser,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return new JsonResponse([
                'message' => 'Token no proporcionado.',
            ], 401);
        }

        ($this->logoutUser)($token);

        return new JsonResponse([
            'message' => 'Logout correcto.',
        ], 200);
    }
}
