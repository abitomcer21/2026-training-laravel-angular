<?php

namespace App\Taxes\Infrastructure\Persistence\Repositories;

use App\Taxes\Domain\Entity\Taxes;
use App\Taxes\Domain\Interfaces\TaxesRepositoryInterface;
use App\Taxes\Infrastructure\Persistence\Models\EloquentTaxes;


class EloquentTaxesRepository implements TaxesRepositoryInterface
{
    public function __construct(
        private EloquentTaxes $model,
    ) {}

    public function save(Taxes $taxes): void
    {
        $this->model->newQuery()->updateOrCreate(
            ['uuid' => $taxes->id()->value()],
            [
                'restaurant_id' => $taxes->restaurantId(),
                'name'          => $taxes->name(),
                'percentage'    => $taxes->percentage()->value(),
                'created_at'    => $taxes->createdAt()->value(),
                'updated_at'    => $taxes->updatedAt()->value(),
            ],
        );
    }

    public function findById(string $id): ?Taxes
    {
        $eloquentTax = $this->model->newQuery()->where('uuid', $id)->first();

        if (!$eloquentTax) {
            return null;
        }

        return Taxes::fromPersistence(
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
            fn (EloquentTaxes $eloquentTax): Taxes => Taxes::fromPersistence(
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
