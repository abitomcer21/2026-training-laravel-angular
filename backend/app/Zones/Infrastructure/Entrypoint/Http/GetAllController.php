<?php

namespace App\Zones\Infrastructure\Entrypoint\Http;

use App\Zones\Application\GetAllZones\GetAllZones;
use Illuminate\Http\JsonResponse;

final class GetAllController
{
    public function __invoke(GetAllZones $getAllZones): JsonResponse
    {
        $response = $getAllZones();

        return new JsonResponse($response->toArray(), 200);
    }
}
