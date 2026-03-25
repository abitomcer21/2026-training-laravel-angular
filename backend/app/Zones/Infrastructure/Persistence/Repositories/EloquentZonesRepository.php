<?php

namespace App\Zones\Infrastructure\Persistence\Repositories;

use App\Zones\Domain\Entity\Zones;
use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;
use App\Zones\Infrastructure\Persistence\Models\EloquentZones;

class EloquentZonesRepository implements ZonesRepositoryInterface
{
    public function save(Zones $zones): void
    {
        EloquentZones::updateOrCreate(
            ['uuid' => $zones->id()->value()],
            [
                'name' => $zones->name(),
            ],
        );
    }

    public function findById(string $id): ?Zones
    {
        $eloquentZone = EloquentZones::where('uuid', $id)->first();

        if (!$eloquentZone) {
            return null;
        }

        return Zones::fromPersistence(
            $eloquentZone->uuid,
            $eloquentZone->name,
            $eloquentZone->created_at,
            $eloquentZone->updated_at,
            $eloquentZone->deleted_at,
        );
    }
}
