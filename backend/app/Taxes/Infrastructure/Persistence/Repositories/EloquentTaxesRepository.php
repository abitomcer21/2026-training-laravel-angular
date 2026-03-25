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
                'name' => $taxes->name(),
                'percentage' => $taxes->percentage()->value(),
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
            $eloquentTax->created_at,
            $eloquentTax->updated_at,
            $eloquentTax->deleted_at,
        );
    }
}
