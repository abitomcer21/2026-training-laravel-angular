<?php 

namespace App\User\Application\DeleteUser;

use App\User\Domain\Interfaces\UserRepositoryInterface; 

class DeleteUser 
{

    public function __construct(
        private UserRepositoryInterface $userRepository,
    ){}

    public function __invoke (string $id): bool
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return false;
        }

        $user->markAsDeleted();
        $this->userRepository->save($user);

        return true;
    }
}