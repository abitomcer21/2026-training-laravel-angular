<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\GetUsers\GetAllUsers;
use Illuminate\Http\JsonResponse;

final class GetAllController
{
    public function __invoke(GetAllUsers $getAllUsers): JsonResponse
    {
        $response = $getAllUsers();

        return new JsonResponse($response->toArray(), 200);
    }
}
