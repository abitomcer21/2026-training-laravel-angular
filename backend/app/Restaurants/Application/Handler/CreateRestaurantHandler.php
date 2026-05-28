<?php

namespace App\Restaurants\Application\Handler;

use App\Restaurants\Application\Command\CreateRestaurantCommand;
use App\Restaurants\Application\Response\CreateRestaurantResponse;
use App\Restaurants\Domain\Entity\Restaurant;
use App\Restaurants\Domain\Interfaces\RestaurantAdminUserCreatorInterface;
use App\Restaurants\Domain\Interfaces\RestaurantPasswordHasherInterface;
use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;
use App\Restaurants\Domain\ValueObject\RestaurantPassword;

class CreateRestaurantHandler
{
    public function __construct(
        private RestaurantRepositoryInterface $restaurantRepository,
        private RestaurantPasswordHasherInterface $passwordHasher,
        private RestaurantAdminUserCreatorInterface $restaurantAdminUserCreator,
    ) {}

    public function __invoke(CreateRestaurantCommand $command): CreateRestaurantResponse
    {
        $restaurant = Restaurant::dddCreate(
            $command->name,
            $command->legalName,
            $command->taxId,
            $command->email,
            RestaurantPassword::create($this->passwordHasher->hash($command->plainPassword)),
        );

        $this->restaurantRepository->save($restaurant);

        $restaurantId = $this->restaurantRepository->getInternalIdByUuid(
            $restaurant->id()->value(),
        );

        if ($restaurantId === null) {
            throw new \RuntimeException('Restaurant could not be persisted correctly.');
        }

        $this->restaurantAdminUserCreator->create(
            $command->email->value(),
            $command->name->value(),
            $command->plainPassword,
            $restaurantId,
        );

        return CreateRestaurantResponse::create($restaurant);
    }
}
