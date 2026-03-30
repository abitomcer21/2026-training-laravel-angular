<?php

namespace App\Families\Infrastructure\Persistence\Repositories;

use App\Families\Domain\Entity\Family;
use App\Families\Domain\Interfaces\FamilyRepositoryInterface;
use App\Families\Infrastructure\Persistence\Models\EloquentFamilies;

class EloquentFamiliesRepository implements FamilyRepositoryInterface
{
    public function save(Family $family): void
    {
        EloquentFamilies::updateOrCreate(
            ['uuid' => $family->id()->value()],
            [
                'restaurant_id' => $family->restaurantId(),
                'name'          => $family->name(),
                'active'        => $family->status()->value(),
                'created_at'    => $family->createdAt()->value(),
                'updated_at'    => $family->updatedAt()->value(),
                'deleted_at'    => $family->deletedAt()?->value(),
            ],
        );
    }

    public function findById(string $id): ?Family
    {
        $eloquentFamilies = EloquentFamilies::where('uuid', $id)->first();

        if (!$eloquentFamilies) {
            return null;
        }

        return Family::fromPersistence(
            $eloquentFamilies->uuid,
            $eloquentFamilies->name,
            $eloquentFamilies->active,
            $eloquentFamilies->restaurant_id,
            $eloquentFamilies->created_at->toDateTimeImmutable(),
            $eloquentFamilies->updated_at->toDateTimeImmutable(),
            $eloquentFamilies->deleted_at?->toDateTimeImmutable(),
        );
    }
}
