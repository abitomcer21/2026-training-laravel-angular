<?php

namespace App\User\Application\UpdateUser;

use App\Shared\Domain\ValueObject\Email;
use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use App\User\Domain\ValueObject\PasswordHash;
use App\User\Domain\ValueObject\Pin;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserName;

class UpdateUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(
        string $uuid,
        ?string $email,
        ?string $name,
        ?string $plainPassword,
        ?string $role,
        ?string $imageSrc,
        ?string $pin,
    ): ?UpdateUserResponse {
        $user = $this->userRepository->findById($uuid);

        if ($user === null) {
            return null;
        }

        $emailVO = $email !== null
            ? Email::create($email)
            : $user->email();

        $nameVO = $name !== null
            ? UserName::create($name)
            : $user->name();

        $roleVO = $role !== null
            ? Role::create($role)
            : $user->role();

        $pinVO = $pin !== null
            ? Pin::create($pin)
            : $user->pin();

        if ($plainPassword !== null) {
            $hashedPassword = $this->passwordHasher->hash($plainPassword);
            $passwordHashVO = PasswordHash::create($hashedPassword);
        } else {
            $passwordHashVO = PasswordHash::create($user->passwordHash()->value());
        }

        $resolvedImage = $imageSrc ?? $user->imageSrc();

        $updatedUser = $user->updateData(
            $emailVO,
            $nameVO,
            $passwordHashVO,
            $roleVO,
            $resolvedImage,
            $pinVO,
        );

        $this->userRepository->save($updatedUser);

        return UpdateUserResponse::create($updatedUser);
    }
}
