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

        if ($email === null) {
            $emailVO = $user->email();
        } else {
            $emailVO = Email::create($email);
        }

        if ($name === null) {
            $nameVO = UserName::create($user->name());
        } else {
            $nameVO = UserName::create($name);
        }

        if ($plainPassword === null) {
            $passwordHashVO = PasswordHash::create($user->passwordHash());
        } else {
            $passwordHashVO = PasswordHash::create($this->passwordHasher->hash($plainPassword));
        }

        if ($role === null) {
            $roleVO = $user->role();
        } else {
            $roleVO = Role::create($role);
        }

        $resolvedImage = $imageSrc ?? $user->imageSrc();

        if ($pin === null) {
            $pinVO = Pin::create($user->pin());
        } else {
            $pinVO = Pin::create($pin);
        }

        $user->updateData($emailVO, $nameVO, $passwordHashVO, $roleVO, $resolvedImage, $pinVO);
        $this->userRepository->save($user);

        return UpdateUserResponse::create($user);
    }
}