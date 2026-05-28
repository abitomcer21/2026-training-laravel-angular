<?php

namespace App\Tax\Infrastructure\Entrypoint\Http;

use App\Tax\Application\Handler\GetAllTaxHandler;
use App\Tax\Application\Query\GetAllTaxQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetAllController
{
    public function __construct(
        private GetAllTaxHandler $getAllTaxHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $query = new GetAllTaxQuery(
                restaurantId: $request->user()->restaurant_id,
            );

            $response = ($this->getAllTaxHandler)($query);

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}