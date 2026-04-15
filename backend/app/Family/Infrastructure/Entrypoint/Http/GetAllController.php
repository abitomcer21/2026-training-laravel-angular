<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\GetAllFamily\GetAllFamily;
use Illuminate\Http\JsonResponse;

final class GetAllController
{
    public function __invoke(GetAllFamily $getAllFamily): JsonResponse
    {
        $response = $getAllFamily();

        return new JsonResponse($response->toArray(),200);
    }
}
