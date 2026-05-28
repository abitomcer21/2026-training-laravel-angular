<?php

namespace App\Restaurants\Infrastructure\Entrypoint\Http;

use App\Restaurants\Application\Handler\GetMyRestaurantHandler;
use App\Restaurants\Application\Query\GetMyRestaurantQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetMyRestaurantController
{
    public function __construct(
        private GetMyRestaurantHandler $getMyRestaurantHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if ($authUser === null) {
            return new JsonResponse([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        try {
            $response = ($this->getMyRestaurantHandler)(
                new GetMyRestaurantQuery(
                    restaurantId: $authUser->restaurant_id,
                ),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}
