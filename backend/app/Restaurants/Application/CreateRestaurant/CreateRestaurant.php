<?php

namespace App\Restaurants\Application\CreateRestaurant;

use App\Restaurants\Domain\Entity\Restaurant;
use App\Restaurants\Domain\Interfaces\RestaurantAdminUserCreatorInterface;
use App\Restaurants\Domain\Interfaces\RestaurantPasswordHasherInterface;
use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;
use App\Restaurants\Domain\ValueObject\RestaurantLegalName;
use App\Restaurants\Domain\ValueObject\RestaurantName;
use App\Restaurants\Domain\ValueObject\RestaurantPassword;
use App\Restaurants\Domain\ValueObject\RestaurantTaxId;
use App\Shared\Domain\ValueObject\Email;

class CreateRestaurant
{
    public function __construct(
        private RestaurantRepositoryInterface $restaurantRepository,
        private RestaurantPasswordHasherInterface $passwordHasher,
        private RestaurantAdminUserCreatorInterface $restaurantAdminUserCreator,
    ) {}

    public function __invoke(
        string $name,
        string $legalName,
        string $taxId,
        string $email,
        string $plainPassword,
    ): CreateRestaurantResponse {
        $restaurant = Restaurant::dddCreate(
            RestaurantName::create($name),
            RestaurantLegalName::create($legalName),
            RestaurantTaxId::create($taxId),
            Email::create($email),
            RestaurantPassword::create($this->passwordHasher->hash($plainPassword)),
        );

        $this->restaurantRepository->save($restaurant);

        $restaurantId = $this->restaurantRepository->getInternalIdByUuid(
            $restaurant->id()->value(),
        );

        if ($restaurantId === null) {
            throw new \RuntimeException('Restaurant could not be persisted correctly.');
        }

        $this->restaurantAdminUserCreator->create(
            $email,
            $name,
            $plainPassword,
            $restaurantId,
        );

        return CreateRestaurantResponse::create($restaurant);
    }
}
