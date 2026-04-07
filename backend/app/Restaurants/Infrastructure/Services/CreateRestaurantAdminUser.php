<?php

namespace App\Restaurants\Infrastructure\Services;

use App\Restaurants\Domain\Interfaces\RestaurantAdminUserCreatorInterface;
use App\User\Application\CreateUser\CreateUser;

final readonly class CreateRestaurantAdminUser implements RestaurantAdminUserCreatorInterface
{
    public function __construct(
        private CreateUser $createUser,
    ) {}

    public function create(
        string $email,
        string $name,
        string $plainPassword,
        int $restaurantId,
    ): void {
        ($this->createUser)(
            $email,
            $name,
            $plainPassword,
            'admin',
            '1234',
            $restaurantId,
            null,
        );
    }
}
