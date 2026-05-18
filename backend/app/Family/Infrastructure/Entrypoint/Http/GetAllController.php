<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\Handler\GetAllFamilyHandler;
use App\Family\Application\Query\GetAllFamilyQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetAllController
{
    public function __construct(
        private GetAllFamilyHandler $getAllFamilyHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $query = new GetAllFamilyQuery(
                restaurantId: $request->user()->restaurant_id,
            );

            $response = ($this->getAllFamilyHandler)($query);

            return new JsonResponse($response->toArray(), 200);
        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}
