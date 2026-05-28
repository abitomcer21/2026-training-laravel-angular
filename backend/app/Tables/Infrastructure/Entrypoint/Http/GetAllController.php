<?php

namespace App\Tables\Infrastructure\Entrypoint\Http;

use App\Tables\Application\Handler\GetAllTablesHandler;
use App\Tables\Application\Query\GetAllTablesQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetAllController
{
    public function __construct(
        private GetAllTablesHandler $getAllTablesHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $query = new GetAllTablesQuery(
                restaurantId: $request->user()->restaurant_id,
            );

            $response = ($this->getAllTablesHandler)($query);

            return new JsonResponse($response->toArray(), 200);

} catch (\Throwable $e) {
    return new JsonResponse(['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], 500);
}
    }
}