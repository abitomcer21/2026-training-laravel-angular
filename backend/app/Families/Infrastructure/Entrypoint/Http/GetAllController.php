<?php

namespace App\Families\Infrastructure\Entrypoint\Http;

use App\Families\Application\GetAllFamilies\GetAllFamilies;
use Illuminate\Http\JsonResponse;

final class GetAllController
{
    public function __invoke(GetAllFamilies $getAllFamilies): JsonResponse
    {
        $response = $getAllFamilies();

        return new JsonResponse($response->toArray(),200);
    }
}
