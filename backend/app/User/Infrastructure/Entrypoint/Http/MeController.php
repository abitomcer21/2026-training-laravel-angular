<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\Auth\Me\GetMe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController
{
    public function __construct(
        private GetMe $getMe,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if ($authUser === null) {
            return new JsonResponse([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $response = ($this->getMe)($authUser->uuid);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'User not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}