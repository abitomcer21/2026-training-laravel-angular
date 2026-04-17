<?php

namespace App\Restaurants\Infrastructure\Entrypoint\Http;

use App\Restaurants\Application\GetMyRestaurant\GetMyRestaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetMyRestaurantController
{
    public function __construct(
        private GetMyRestaurant $getMyRestaurant,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if ($authUser === null) {
            return new JsonResponse([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $response = ($this->getMyRestaurant)($authUser->restaurant_id);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Restaurant not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
