<?php

namespace App\User\Application\Handler;

use App\User\Application\Query\GetAllUsersQuery;
use App\User\Application\Response\GetAllUsersResponse;
use App\User\Domain\Interfaces\UserReadRepositoryInterface;

class GetAllUsersHandler
{
    public function __construct(
        private UserReadRepositoryInterface $userReadRepository,
    ) {}

    public function __invoke(GetAllUsersQuery $query): GetAllUsersResponse
    {
        if ($query->restaurantId !== null) {
            $users = $this->userReadRepository->allByRestaurantIdWithNumericId($query->restaurantId);
        } else {
            $users = $this->userReadRepository->allWithNumericId();
        }

        return GetAllUsersResponse::create($users);
    }
}
