<?php

namespace App\User\Domain\Interfaces;

use App\User\Application\Response\GetAllUsersItem;

interface UserReadRepositoryInterface
{
    public function allWithNumericId(): array;

    public function allByRestaurantIdWithNumericId(int $restaurantId): array;
}
