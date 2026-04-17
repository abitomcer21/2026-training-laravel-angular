<?php

namespace App\Tax\Infrastructure\Entrypoint\Http;

use App\Tax\Application\GetAllTax\GetAllTax;
use Illuminate\Http\JsonResponse;

final class GetAllController
{
    public function __invoke(GetAllTax $getAllTax): JsonResponse
    {
        $response = $getAllTax();

        return new JsonResponse($response->toArray(), 200);
    }
}
