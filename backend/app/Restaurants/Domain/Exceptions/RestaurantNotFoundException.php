<?php

namespace App\Restaurants\Domain\Exceptions;

class RestaurantNotFoundException extends \DomainException
{
    public function __construct(int|string $restaurantId)
    {
        parent::__construct(
            sprintf('Restaurant with ID %s not found', $restaurantId),
            404,
        );
    }
}
