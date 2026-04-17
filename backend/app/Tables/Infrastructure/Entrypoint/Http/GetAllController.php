<?php

namespace App\Tables\Infrastructure\Entrypoint\Http;

use App\Tables\Application\GetAllTables\GetAllTables;
use Illuminate\Http\JsonResponse;

final class GetAllController
{
    public function __invoke(GetAllTables $getAllTables): JsonResponse
    {
        $response = $getAllTables();

        return new JsonResponse($response->toArray(), 200);
    }
}
