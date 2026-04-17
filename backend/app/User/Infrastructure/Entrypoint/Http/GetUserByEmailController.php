<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\GetUserByEmail\GetUserByEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetUserByEmailController
{
    public function __construct(
        private GetUserByEmail $getUserByEmail,
    ) {}

    public function __invoke(Request $request, string $email): JsonResponse
    {
        $response = ($this->getUserByEmail)($email);

        if ($response === null) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
