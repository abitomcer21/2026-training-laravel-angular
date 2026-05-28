<?php

namespace App\Restaurants\Application\Query;

final readonly class GetMyRestaurantQuery
{
    public function __construct(
        public int $restaurantId,
    ) {}
}
