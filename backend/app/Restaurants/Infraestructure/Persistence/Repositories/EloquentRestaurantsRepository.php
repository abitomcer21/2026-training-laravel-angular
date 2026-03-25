<?php

namespace App\Restaurants\Infraestructure\Persistence\Repositories;

use App\Restaurants\Domain\Entity\Restaurants;
use App\Restaurants\Domain\Interfaces\RestaurantsRepositoryInterface;
use App\Restaurants\Infraestructure\Persistence\Models\EloquentRestaurants;

class EloquentRestaurantsRepository implements RestaurantsRepositoryInterface
{
    public function save(Restaurants $restaurants): void
    {
        EloquentRestaurants::updateOrCreate(
            ['uuid' => $restaurants->id()->value()],
            [
                'name' => $restaurants->name(),
                'legal_name' => $restaurants->legalName(),
                'tax_id' => $restaurants->taxId(),
                'email' => $restaurants->email(),
                'password' => $restaurants->password(),
            ],
        );
    }

    public function findById(string $id): ?Restaurants
    {
        $eloquentRestaurant = EloquentRestaurants::where('uuid', $id)->first();

        if (! $eloquentRestaurant) {
            return null;
        }

        return Restaurants::fromPersistence(
            $eloquentRestaurant->uuid,
            $eloquentRestaurant->name,
            $eloquentRestaurant->legal_name,
            $eloquentRestaurant->tax_id,
            $eloquentRestaurant->email,
            $eloquentRestaurant->password,
            $eloquentRestaurant->created_at->toImmutable(),
            $eloquentRestaurant->updated_at->toImmutable(),
            $eloquentRestaurant->deleted_at?->toImmutable(),
        );
    }
}