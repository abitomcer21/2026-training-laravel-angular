<?php

namespace App\User\Application\CreateUser;

use App\Shared\Domain\ValueObject\Email;
use App\User\Domain\Entity\User;
use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use App\User\Domain\ValueObject\PasswordHash;
use App\User\Domain\ValueObject\Pin;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserName;

class CreateUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,

    ) {}

    public function __invoke(
        string $email,
        string $name,
        string $plainPassword,
        string $role,
        string $pin,
        int $restaurantId,
        ?string $imageSrc = null,
    ): CreateUserResponse {
        $emailVO = Email::create($email);
        $nameVO = UserName::create($name);
        $roleVO = Role::create($role);
        $passwordHashVO = PasswordHash::create($this->passwordHasher->hash($plainPassword));

        $pinVO = Pin::create($pin);

        $user = User::dddCreate(
            email: $emailVO,
            name: $nameVO,
            passwordHash: $passwordHashVO,
            role: $roleVO,
            pin: $pinVO,
            restaurantId: $restaurantId,
            imageSrc: $imageSrc,
        );

        $this->userRepository->save($user);

        return CreateUserResponse::create($user);
    }
}
