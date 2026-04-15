<?php

namespace App\Family\Infrastructure\Persistence\Repositories;

use App\Family\Domain\Entity\Family;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Infrastructure\Persistence\Models\EloquentFamily;

class EloquentFamilyRepository implements FamilyRepositoryInterface
{
    public function __construct(
        private EloquentFamily $model,
    ) {}

    public function save(Family $family): void
    {
        $this->model->newQuery()->updateOrCreate(
            ['uuid' => $family->id()->value()],
            [
                'restaurant_id' => $family->restaurantId(),
                'name'          => $family->name()->value(),
                'active'        => $family->status()->value(),
                'created_at'    => $family->createdAt()->value(),
                'updated_at'    => $family->updatedAt()->value(),
            ],
        );
    }

    public function findById(string $id): ?Family
    {
        $eloquentFamily = $this->model->newQuery()->where('uuid', $id)->first();

        if (!$eloquentFamily) {
            return null;
        }

        return Family::fromPersistence(
            $eloquentFamily->uuid,
            $eloquentFamily->name,
            $eloquentFamily->active,
            $eloquentFamily->restaurant_id,
            $eloquentFamily->created_at->toDateTimeImmutable(),
            $eloquentFamily->updated_at->toDateTimeImmutable(),
        );
    }

    public function all(): array
    {
        return $this->model->newQuery()->get()->map(
            fn (EloquentFamily $family): Family => Family::fromPersistence(
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
