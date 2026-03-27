<?php
namespace App\User\Application\GetUser;

use App\User\Domain\Interfaces\UserRepositoryInterface;

class GetUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(string $id): ?GetUserResponse
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            return null;
        }

        return GetUserResponse::create($user);
    }
}