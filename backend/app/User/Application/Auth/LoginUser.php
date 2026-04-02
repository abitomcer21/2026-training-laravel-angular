<?php

namespace App\User\Application\Auth;

use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;
use App\User\Domain\Entity\User;
use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\TokenIssuerInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;

class LoginUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenIssuerInterface $tokenIssuer,
        private RestaurantRepositoryInterface $restaurantRepository,
    ) {}

    public function __invoke(string $email, string $plainPassword): LoginUserResponse
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new \InvalidArgumentException('Credenciales inválidas');
        }

        $isValidPassword = $this->passwordHasher->verify(
            $plainPassword,
            $user->passwordHash(),
        );

        if (! $isValidPassword) {
            throw new \InvalidArgumentException('Credenciales inválidas');
        }

        $token = $this->tokenIssuer->issueForUser($user);
        $restaurants = $this->accessibleRestaurantsFor($user);

        return LoginUserResponse::create($user, $token, $restaurants);
    }

    private function accessibleRestaurantsFor(User $user): array
    {
        if ($user->role()->isAdmin()) {
            return $this->restaurantRepository->all();
        }

        $restaurant = $this->restaurantRepository->findByInternalId($user->restaurantId());

        if ($restaurant === null) {
            return [];
        }

        return [$restaurant];
    }
}
