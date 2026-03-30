<?php

namespace App\User\Application\UpdateUser;

use App\User\Domain\Interfaces\UserRepositoryInterface;
use App\User\Domain\ValueObject\Pin;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserName;

class UpdateUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(string $id, string $name, string $role, string $pin): ?UpdateUserResponse
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return null;
        }

        $user->updateName(UserName::create($name));
        $user->updateRole(Role::create($role));
        $user->updatePin(Pin::create($pin));
        $this->userRepository->save($user);

        return UpdateUserResponse::create($user);
    }
}