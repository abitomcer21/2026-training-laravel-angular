<?php

namespace App\User\Application\Handler;

use App\User\Application\Command\LoginUserCommand;
use App\User\Application\Response\LoginUserResponse;
use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\TokenIssuerInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;

class LoginUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenIssuerInterface $tokenIssuer,
    ) {}

    public function __invoke(LoginUserCommand $command): LoginUserResponse
    {
        $user = $this->userRepository->findByEmail($command->email);

        if ($user === null) {
            throw new \InvalidArgumentException('Credenciales inválidas');
        }

        $isValidPassword = $this->passwordHasher->verify(
            $command->plainPassword,
            $user->passwordHash()->value(),
        );

        if (! $isValidPassword) {
            throw new \InvalidArgumentException('Credenciales inválidas');
        }

        $token = $this->tokenIssuer->issueForUser($user);

        return LoginUserResponse::create($user, $token);
    }
}
