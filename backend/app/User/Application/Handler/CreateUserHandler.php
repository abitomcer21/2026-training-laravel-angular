<?php

namespace App\User\Application\Handler;

use App\User\Application\Command\CreateUserCommand;
use App\User\Application\Response\CreateUserResponse;
use App\User\Domain\Entity\User;
use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use App\User\Domain\ValueObject\PasswordHash;

class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(CreateUserCommand $command): CreateUserResponse
    {
        $passwordHashVO = PasswordHash::create(
            $this->passwordHasher->hash($command->plainPassword),
        );

        $user = User::dddCreate(
            email:        $command->email,
            name:         $command->name,
            passwordHash: $passwordHashVO,
            role:         $command->role,
            pin:          $command->pin,
            restaurantId: $command->restaurantId,
            imageSrc:     $command->imageSrc,
        );

        $this->userRepository->save($user);

        return CreateUserResponse::create($user);
    }
}
