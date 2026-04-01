<?php

namespace App\Families\Infrastructure\Persistence\Repositories;

use App\Families\Domain\Entity\Family;
use App\Families\Domain\Interfaces\FamilyRepositoryInterface;
use App\Families\Infrastructure\Persistence\Models\EloquentFamilies;

class EloquentFamiliesRepository implements FamilyRepositoryInterface
{
    public function __construct(
        private EloquentFamilies $model,
    ) {}

    public function save(Family $family): void
    {
        $this->model->newQuery()->updateOrCreate(
            ['uuid' => $family->id()->value()],
            [
                'restaurant_id' => $family->restaurantId(),
                'name'          => $family->name(),
                'active'        => $family->status()->value(),
                'created_at'    => $family->createdAt()->value(),
                'updated_at'    => $family->updatedAt()->value(),
            ],
        );
    }

    public function findById(string $id): ?Family
    {
        $eloquentFamilies = $this->model->newQuery()->where('uuid', $id)->first();

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
        );
    }

    public function all(): array
    {
        return $this->model->newQuery()->get()->map(
            fn (EloquentFamilies $family): Family => Family::fromPersistence(
                $family->uuid,
                $family->name,
                $family->active,
                $family->restaurant_id,
                $family->created_at->toDateTimeImmutable(),
                $family->updated_at->toDateTimeImmutable(),
            ),
        )->toArray();
    }                                                                                                                                            

    public function delete(string $id): void
    {
        $this->model->newQuery()->where('uuid', $id)->delete();
    }
}
