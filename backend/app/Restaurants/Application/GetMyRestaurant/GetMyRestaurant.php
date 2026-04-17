<?php

namespace App\Restaurants\Application\GetMyRestaurant;

use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;

class GetMyRestaurant
{
    public function __construct(
        private RestaurantRepositoryInterface $restaurantRepository,
    ) {}

    public function __invoke(int $restaurantId): ?GetMyRestaurantResponse
    {
        $restaurant = $this->restaurantRepository->findByInternalId($restaurantId);

        if ($restaurant === null) {
            return null;
        }

        return GetMyRestaurantResponse::create($restaurant);
    }
}
