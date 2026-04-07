<?php

namespace App\Restaurants\Application\UpdateRestaurant;

use App\Restaurants\Domain\Interfaces\RestaurantPasswordHasherInterface;
use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;
use App\Restaurants\Domain\ValueObject\RestaurantLegalName;
use App\Restaurants\Domain\ValueObject\RestaurantName;
use App\Restaurants\Domain\ValueObject\RestaurantPassword;
use App\Restaurants\Domain\ValueObject\RestaurantTaxId;
use App\Shared\Domain\ValueObject\Email;

class UpdateRestaurant
{
    public function __construct(
        private RestaurantRepositoryInterface $restaurantRepository,
        private RestaurantPasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(
        string $id,
        string $name,
        string $legalName,
        string $taxId,
        string $email,
        ?string $plainPassword = null,
    ): ?UpdateRestaurantResponse {
        $restaurant = $this->restaurantRepository->findById($id);

        if (! $restaurant) {
            return null;
        }

        $restaurant->updateName(RestaurantName::create($name));
        $restaurant->updateLegalName(RestaurantLegalName::create($legalName));
        $restaurant->updateTaxId(RestaurantTaxId::create($taxId));
        $restaurant->updateEmail(Email::create($email));

        if ($plainPassword !== null && $plainPassword !== '') {
            $restaurant->updatePassword(
                RestaurantPassword::create(
                    $this->passwordHasher->hash($plainPassword),
                ),
            );
        }

        $this->restaurantRepository->save($restaurant);

        return UpdateRestaurantResponse::create($restaurant);
    }
}