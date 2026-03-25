<?php

namespace App\Tables\Infrastructure\Persistence\Repositories;

use App\Tables\Domain\Entity\Tables;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;
use App\Tables\Infrastructure\Persistence\Models\EloquentTables;

class EloquentTablesRepository implements TablesRepositoryInterface
{
    public function save(Tables $tables): void
    {
        EloquentTables::updateOrCreate(
            ['uuid' => $tables->id()->value()],
            [
                'zone_id' => $tables->zoneId()->value(),
                'name' => $tables->name(),
            ],
        );
    }

    public function findById(string $id): ?Tables
    {
        $eloquentTable = EloquentTables::where('uuid', $id)->first();

        if (!$eloquentTable) {
            return null;
        }

        return Tables::fromPersistence(
            $eloquentTable->uuid,
            $eloquentTable->zone_id,
            $eloquentTable->name,
            $eloquentTable->created_at,
            $eloquentTable->updated_at,
            $eloquentTable->deleted_at,
        );
    }
}
