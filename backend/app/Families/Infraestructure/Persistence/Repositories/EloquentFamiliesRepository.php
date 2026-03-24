<?php

namespace App\Families\Infraestructure\Persistence\Repositories;

use App\Families\Domain\Entity\Families;
use App\Families\Domain\Interfaces\FamiliesRepositoryInterface;
use App\Families\Infraestructure\Persistence\Models\EloquentFamilies;

class EloquentFamiliesRepository implements FamiliesRepositoryInterface
{
    public function save(Families $families): void
    {
        EloquentFamilies::updateOrCreate(
            ['uuid' => $families->id()->value()],
            [
                'name' => $families->name(),
                'activo' => $families->status()->value(),
            ],
        );
    }

    public function findById(string $id): ?Families
    {
        $eloquentFamilies = EloquentFamilies::where('uuid', $id)->first();

        if (!$eloquentFamilies) {
            return null;
        }

        return Families::fromPersistence(
            $eloquentFamilies->uuid,
            $eloquentFamilies->name,
            $eloquentFamilies->activo,
            $eloquentFamilies->created_at,
            $eloquentFamilies->updated_at,
            $eloquentFamilies->deleted_at,
        );
    }
}
