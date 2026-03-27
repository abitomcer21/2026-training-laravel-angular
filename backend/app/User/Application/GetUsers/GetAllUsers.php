<?php

namespace App\User\Application\GetUsers;

use App\User\Domain\Interfaces\UserRepositoryInterface;
use App\User\Application\GetUsers\GetAllUsersResponse;


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