<?php

namespace App\User\Application\GetAllUsers;

use App\User\Domain\Interfaces\UserRepositoryInterface;

class GetAllUsers
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(?int $restaurantId = null): GetAllUsersResponse
    {
        if ($restaurantId !== null) {
            $users = $this->userRepository->allByRestaurantIdWithNumericId($restaurantId);
        } else {
            $users = $this->userRepository->allWithNumericId();
        }

        return GetAllUsersResponse::create($users);
    }
}
