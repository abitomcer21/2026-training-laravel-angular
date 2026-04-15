<?php

namespace App\User\Application\Auth;

use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\TokenIssuerInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;

class LoginUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenIssuerInterface $tokenIssuer,
    ) {}

    public function __invoke(string $email, string $plainPassword): LoginUserResponse
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new \InvalidArgumentException('Credenciales inválidas');
        }

        $isValidPassword = $this->passwordHasher->verify(
            $plainPassword,
            $user->passwordHash()->value(),
        );

        if (! $isValidPassword) {
            throw new \InvalidArgumentException('Credenciales inválidas');
        }

        $token = $this->tokenIssuer->issueForUser($user);

        return LoginUserResponse::create($user, $token);
    }
}