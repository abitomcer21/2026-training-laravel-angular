<?php

namespace App\User\Application\Auth\Me;

use App\User\Domain\Interfaces\UserRepositoryInterface;

class GetMe
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(string $uuid): ?GetMeResponse
    {
        $user = $this->userRepository->findById($uuid);

        if (!$user) {
            return null;
        }

        return GetMeResponse::create($user);
    }
}