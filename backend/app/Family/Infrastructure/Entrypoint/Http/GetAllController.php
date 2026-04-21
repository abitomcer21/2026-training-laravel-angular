<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\GetAllFamily\GetAllFamily;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetAllController
{
    public function __invoke(GetAllFamily $getAllFamily, Request $request): JsonResponse
    {
        $user = $request->user();
        $restaurantId = $user?->restaurant_id;

        $response = $getAllFamily($restaurantId);

        return new JsonResponse($response->toArray(), 200);
    }
}
