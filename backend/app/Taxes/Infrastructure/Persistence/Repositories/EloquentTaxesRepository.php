<?php

namespace App\Taxes\Infrastructure\Persistence\Repositories;

use App\Taxes\Domain\Entity\Taxes;
use App\Taxes\Domain\Interfaces\TaxesRepositoryInterface;
use App\Taxes\Infrastructure\Persistence\Models\EloquentTaxes;


class EloquentTaxesRepository implements TaxesRepositoryInterface
{
    public function save(Taxes $taxes): void
    {
        EloquentTaxes::updateOrCreate(
            ['uuid' => $taxes->id()->value()],
            [
                'restaurant_id' => $taxes->restaurantId(),
                'name'          => $taxes->name(),
                'percentage'    => $taxes->percentage()->value(),
                'created_at'    => $taxes->createdAt()->value(),
                'updated_at'    => $taxes->updatedAt()->value(),
                'deleted_at'    => $taxes->deletedAt()?->value(),
            ],
        );
    }

    public function findById(string $id): ?Taxes
    {
        $eloquentTax = EloquentTaxes::where('uuid', $id)->first();

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
            $eloquentTax->deleted_at?->toDateTimeImmutable(),
        );
    }

    public function all(): array
    {
        $eloquentTaxes = EloquentTaxes::all();

        return array_map(
            static fn (EloquentTaxes $eloquentTax): Taxes => Taxes::fromPersistence(
                $eloquentTax->uuid,
                $eloquentTax->name,
                $eloquentTax->percentage,
                $eloquentTax->restaurant_id,
                $eloquentTax->created_at->toDateTimeImmutable(),
                $eloquentTax->updated_at->toDateTimeImmutable(),
                $eloquentTax->deleted_at?->toDateTimeImmutable(),
            ),
            $eloquentTaxes->all(),
        );
    }
}
