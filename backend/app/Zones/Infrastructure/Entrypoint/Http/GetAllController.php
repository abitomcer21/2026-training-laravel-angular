<?php

namespace App\Zones\Infrastructure\Entrypoint\Http;

use App\Zones\Application\Handler\GetAllZonesHandler;
use App\Zones\Application\Query\GetAllZonesQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetAllController
{
    public function __construct(
        private GetAllZonesHandler $getAllZonesHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $query = new GetAllZonesQuery(
                restaurantId: $request->user()->restaurant_id,
            );

            $response = ($this->getAllZonesHandler)($query);

            return new JsonResponse($response->toArray(), 200);

} catch (\Throwable $e) {
    return new JsonResponse(['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], 500);
}
    }
}