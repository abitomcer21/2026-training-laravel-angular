<?php

namespace App\User\Application\GetAllUsers;

use App\User\Domain\Interfaces\UserRepositoryInterface;

class GetAllUsers
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(): GetAllUsersResponse
    {
        $users = $this->userRepository->all();

        return GetAllUsersResponse::create($users);
    }
}
