<?php

namespace App\Zones\Infrastructure\Persistence\Repositories;

use App\Zones\Domain\Entity\Zones;
use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;
use App\Zones\Infrastructure\Persistence\Models\EloquentZones;

class EloquentZonesRepository implements ZonesRepositoryInterface
{
    public function __construct(
        private EloquentZones $model,
    ) {
    }

    public function save(Zones $zones): void
    {
        $model = $this->model->newQuery()->firstOrNew(['uuid' => $zones->id()->value()]);

        if (!$model->exists) {
            $model->created_at = $zones->createdAt()->value();
        }

        $model->fill([
            'name' => $zones->name(),
            'restaurant_id' => $zones->restaurantId() ?? $model->restaurant_id,
        ]);

        $model->updated_at = $zones->updatedAt()->value();
        $model->deleted_at = $zones->deletedAt()?->value();

        $model->save();
    }

    public function findById(string $id): ?Zones
    {
        $eloquentZone = $this->model->newQuery()->where('uuid', $id)->first();

        if (!$eloquentZone) {
            return null;
        }

        return Zones::fromPersistence(
            $eloquentZone->uuid,
            $eloquentZone->name,
            $eloquentZone->restaurant_id,
            $eloquentZone->created_at->toDateTimeImmutable(),
            $eloquentZone->updated_at->toDateTimeImmutable(),
            $eloquentZone->deleted_at?->toDateTimeImmutable(),
        );
    }

    public function all(): array
    {
        $zones = $this->model->newQuery()->get()->map(
            fn (EloquentZones $zone): Zones => Zones::fromPersistence(
                $zone->uuid,
                $zone->name,
                $zone->restaurant_id,
                $zone->created_at->toDateTimeImmutable(),
                $zone->updated_at->toDateTimeImmutable(),
                $zone->deleted_at?->toDateTimeImmutable(),
            ),
        );

        return $zones->toArray();
    }
}
