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
        if (!$this->userRepository->findById($id)) {
            return false;
        }

        $this->userRepository->delete($id);

        return true;
    }
}