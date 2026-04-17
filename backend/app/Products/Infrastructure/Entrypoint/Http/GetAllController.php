<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\GetAllProducts\GetAllProducts;
use Illuminate\Http\JsonResponse;

final class GetAllController
{
    public function __invoke(GetAllProducts $getAllProducts): JsonResponse
    {
        $response = $getAllProducts();

        return new JsonResponse($response->toArray(), 200);
    }
}
