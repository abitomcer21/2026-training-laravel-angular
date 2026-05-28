<?php

namespace App\Restaurants\Application\Handler;

use App\Restaurants\Application\Query\GetMyRestaurantQuery;
use App\Restaurants\Application\Response\GetMyRestaurantResponse;
use App\Restaurants\Domain\Exceptions\RestaurantNotFoundException;
use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;

class GetMyRestaurantHandler
{
    public function __construct(
        private RestaurantRepositoryInterface $restaurantRepository,
    ) {}

    public function __invoke(GetMyRestaurantQuery $query): GetMyRestaurantResponse
    {
        $restaurant = $this->restaurantRepository->findByInternalId($query->restaurantId);

        if ($restaurant === null) {
            throw new RestaurantNotFoundException($query->restaurantId);
        }

        return GetMyRestaurantResponse::create($restaurant);
    }
}
