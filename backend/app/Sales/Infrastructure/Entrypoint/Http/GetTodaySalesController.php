<?php

namespace App\Sales\Infrastructure\Entrypoint\Http;

use App\Sales\Application\Handler\GetTodaySalesHandler;
use App\Sales\Application\Query\GetTodaySalesQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetTodaySalesController
{
    public function __construct(
        private GetTodaySalesHandler $getTodaySalesHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $result = ($this->getTodaySalesHandler)(
                new GetTodaySalesQuery(
                    restaurantId: $request->user()?->restaurant_id,
                ),
            );

            return new JsonResponse($result->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}