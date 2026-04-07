<?php

namespace App\Restaurants\Domain\Interfaces;

interface RestaurantAdminUserCreatorInterface
{
    public function create(
        string $email,
        string $name,
        string $plainPassword,
        int $restaurantId,
    ): void;
}
