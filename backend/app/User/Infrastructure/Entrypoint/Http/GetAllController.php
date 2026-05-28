<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use App\User\Application\Handler\GetAllUsersHandler;
use App\User\Application\Query\GetAllUsersQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetAllController
{
    public function __construct(
        private GetAllUsersHandler $getAllUsersHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $query = new GetAllUsersQuery(
                restaurantId: $request->user()?->restaurant_id,
            );

            $response = ($this->getAllUsersHandler)($query);

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}
