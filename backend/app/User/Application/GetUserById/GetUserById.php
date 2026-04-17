<?php

namespace App\User\Application\GetUserById;

use App\User\Domain\Interfaces\UserRepositoryInterface;

class GetUserById
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(string $id): ?GetUserByIdResponse
    {
        $user = $this->userRepository->findById($id);

        if (! $user) {
            return null;
        }

        return GetUserByIdResponse::create($user);
    }
}
