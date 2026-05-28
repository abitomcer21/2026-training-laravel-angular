<?php

namespace App\Restaurants\Infrastructure\Services;

use App\Restaurants\Domain\Interfaces\RestaurantAdminUserCreatorInterface;
use App\User\Application\Command\CreateUserCommand;
use App\User\Application\Handler\CreateUserHandler;

final readonly class CreateRestaurantAdminUser implements RestaurantAdminUserCreatorInterface
{
    public function __construct(
        private CreateUserHandler $createUserHandler,
    ) {}

    public function create(
        string $email,
        string $name,
        string $plainPassword,
        int $restaurantId,
    ): void {
        ($this->createUserHandler)(
            CreateUserCommand::create(
                email:        $email,
                name:         $name,
                plainPassword: $plainPassword,
                role:         'admin',
                pin:          '1234',
                restaurantId: $restaurantId,
                imageSrc:     null,
            ),
        );
    }
}
