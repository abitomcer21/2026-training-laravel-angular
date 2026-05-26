<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\Handler\GetProductByFamilyHandler;
use App\Products\Application\Query\GetProductByFamilyQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetByFamilyController
{
    public function __construct(
        private GetProductByFamilyHandler $getProductByFamilyHandler,
    ) {}

    public function __invoke(Request $request, string $familyId): JsonResponse
    {
        try {
            $query = new GetProductByFamilyQuery(
                familyId:     $familyId,
                restaurantId: $request->user()->restaurant_id,
            );

            $response = ($this->getProductByFamilyHandler)($query);

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}