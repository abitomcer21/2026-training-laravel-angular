<?php

namespace App\User\Application\Handler;

use App\User\Application\Command\UpdateUserCommand;
use App\User\Application\Response\UpdateUserResponse;
use App\User\Domain\Exceptions\UserNotFoundException;
use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use App\User\Domain\ValueObject\PasswordHash;

class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(UpdateUserCommand $command): UpdateUserResponse
    {
        $user = $this->userRepository->findById($command->id->value());

        if ($user === null) {
            throw new UserNotFoundException($command->id->value());
        }

        $email        = $command->email ?? $user->email();
        $name         = $command->name ?? $user->name();
        $role         = $command->role ?? $user->role();
        $pin          = $command->pin ?? $user->pin();
        $imageSrc     = $command->imageSrc ?? $user->imageSrc();

        if ($command->plainPassword !== null) {
            $passwordHashVO = PasswordHash::create(
                $this->passwordHasher->hash($command->plainPassword),
            );
        } else {
            $passwordHashVO = $user->passwordHash();
        }

        $updatedUser = $user->updateData(
            $email,
            $name,
            $passwordHashVO,
            $role,
            $imageSrc,
            $pin,
        );

        $this->userRepository->save($updatedUser);

        return UpdateUserResponse::create($updatedUser);
    }
}
