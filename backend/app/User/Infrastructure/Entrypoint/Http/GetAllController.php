<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\GetAllUsers\GetAllUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetAllController
{
    public function __invoke(GetAllUsers $getAllUsers, Request $request): JsonResponse
    {
        // Obtener el usuario autenticado y su restaurant_id
        $user = $request->user();
        $restaurantId = $user?->restaurant_id;

        $response = $getAllUsers($restaurantId);

        return new JsonResponse($response->toArray(), 200);
    }
}
