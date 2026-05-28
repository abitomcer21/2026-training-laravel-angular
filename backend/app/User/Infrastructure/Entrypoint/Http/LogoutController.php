<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\Command\LogoutUserCommand;
use App\User\Application\Handler\LogoutUserHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController
{
    public function __construct(
        private LogoutUserHandler $logoutUserHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return new JsonResponse([
                'message' => 'Token no proporcionado.',
            ], 401);
        }

        ($this->logoutUserHandler)(
            LogoutUserCommand::create(token: $token),
        );

        return new JsonResponse([
            'message' => 'Logout correcto.',
        ], 200);
    }
}
