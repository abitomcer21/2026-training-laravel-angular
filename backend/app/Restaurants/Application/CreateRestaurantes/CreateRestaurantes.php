<?php

namespace App\Restaurants\Application\CreateRestaurantes;

use App\Restaurants\Domain\Entity\Restaurants;
use App\Restaurants\Domain\Interfaces\RestaurantsRepositoryInterface;
use App\Restaurants\Domain\ValueObject\RestaurantLegalName;
use App\Restaurants\Domain\ValueObject\RestaurantName;
use App\Restaurants\Domain\ValueObject\RestaurantPassword;
use App\Restaurants\Domain\ValueObject\RestaurantTaxId;
use App\Shared\Domain\ValueObject\Email;
use App\User\Domain\Interfaces\PasswordHasherInterface;

class CreateRestaurantes
{
    public function __construct(
        private RestaurantsRepositoryInterface $restaurantsRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(
        string $name,
        string $legalName,
        string $taxId,
        string $email,
        string $plainPassword,
    ): CreateRestaurantesResponse {
        $restaurants = Restaurants::dddCreate(
            RestaurantName::create($name),
            RestaurantLegalName::create($legalName),
            RestaurantTaxId::create($taxId),
            Email::create($email),
            RestaurantPassword::create($this->passwordHasher->hash($plainPassword)),
        );

        $this->restaurantsRepository->save($restaurants);

        return CreateRestaurantesResponse::create($restaurants);
    }
}