<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\Handler\GetAllProductsHandler;
use App\Products\Application\Query\GetAllProductsQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetAllController
{
    public function __construct(
        private GetAllProductsHandler $getAllProductsHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $query = new GetAllProductsQuery(
                restaurantId: $request->user()->restaurant_id,
            );

            $response = ($this->getAllProductsHandler)($query);

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
    return new JsonResponse(['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], 500);
}
    }
}