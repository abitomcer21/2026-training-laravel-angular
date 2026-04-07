<?php

namespace App\Restaurants\Infrastructure\Persistence\Repositories;

use App\Restaurants\Domain\Entity\Restaurant;
use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;

class EloquentRestaurantRepository implements RestaurantRepositoryInterface
{
    public function __construct(
        private EloquentRestaurant $model,
    ) {}

    public function save(Restaurant $restaurant): void
    {
        $this->model->newQuery()->updateOrCreate(
            ['uuid' => $restaurant->id()->value()],
            [
                'name' => $restaurant->name(),
                'legal_name' => $restaurant->legalName(),
                'tax_id' => $restaurant->taxId(),
                'email' => $restaurant->email()->value(),
                'password' => $restaurant->passwordHash(),
                'created_at' => $restaurant->createdAt()->value(),
                'updated_at' => $restaurant->updatedAt()->value(),
            ],
        );
    }

    public function findById(string $id): ?Restaurant
    {
        $eloquentRestaurant = $this->model->newQuery()->where('uuid', $id)->first();

        if (! $eloquentRestaurant) {
            return null;
        }

        return Restaurant::fromPersistence(
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

    public function findByInternalId(int $id): ?Restaurant
    {
        $eloquentRestaurant = $this->model->newQuery()->find($id);

        if (! $eloquentRestaurant) {
            return null;
        }

        return Restaurant::fromPersistence(
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

    public function all(): array
    {
        return $this->model->newQuery()->get()->map(
            fn(EloquentRestaurant $eloquentRestaurant): Restaurant => Restaurant::fromPersistence(
                $eloquentRestaurant->uuid,
                $eloquentRestaurant->name,
                $eloquentRestaurant->legal_name,
                $eloquentRestaurant->tax_id,
                $eloquentRestaurant->email,
                $eloquentRestaurant->password,
                $eloquentRestaurant->created_at->toImmutable(),
                $eloquentRestaurant->updated_at->toImmutable(),
                $eloquentRestaurant->deleted_at?->toImmutable(),
            ),
        )->toArray();
    }

    public function getInternalIdByUuid(string $uuid): ?int
    {
        $restaurant = $this->model->newQuery()->where('uuid', $uuid)->first();

        return $restaurant?->id;
    }
}
