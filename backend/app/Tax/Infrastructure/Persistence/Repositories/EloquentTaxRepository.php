<?php

namespace App\Tax\Infrastructure\Persistence\Repositories;

use App\Tax\Domain\Entity\Tax;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;
use App\Tax\Infrastructure\Persistence\Models\EloquentTax;

class EloquentTaxRepository implements TaxRepositoryInterface
{
    public function __construct(
        private EloquentTax $model,
    ) {}

    public function save(Tax $tax): void
    {
        $this->model->newQuery()->updateOrCreate(
            ['uuid' => $tax->id()->value()],
            [
                'restaurant_id' => $tax->restaurantId(),
                'name' => $tax->name(),
                'percentage' => $tax->percentage()->value(),
                'created_at' => $tax->createdAt()->value(),
                'updated_at' => $tax->updatedAt()->value(),
            ],
        );
    }

    public function findById(string $id): ?Tax
    {
        $eloquentTax = $this->model->newQuery()->where('uuid', $id)->first();

        if (! $eloquentTax) {
            return null;
        }

        return Tax::fromPersistence(
            $eloquentTax->uuid,
            $eloquentTax->name,
            $eloquentTax->percentage,
            $eloquentTax->restaurant_id,
            $eloquentTax->created_at->toDateTimeImmutable(),
            $eloquentTax->updated_at->toDateTimeImmutable(),
        );
    }

    public function all(): array
    {
        return $this->model->newQuery()->get()->map(
            fn (EloquentTax $eloquentTax): Tax => Tax::fromPersistence(
                $eloquentTax->uuid,
                $eloquentTax->name,
                $eloquentTax->percentage,
                $eloquentTax->restaurant_id,
                $eloquentTax->created_at->toDateTimeImmutable(),
                $eloquentTax->updated_at->toDateTimeImmutable(),
            ),
        )->toArray();
    }

    public function delete(string $id): void
    {
        $this->model->newQuery()->where('uuid', $id)->delete();
    }
}
