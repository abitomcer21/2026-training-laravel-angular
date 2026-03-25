<?php

namespace App\Products\Infrastructure\Persistence\Repositories;

use App\Products\Domain\Entity\Products;
use App\Products\Domain\Interfaces\ProductsRepositoryInterface;
use App\Products\Infrastructure\Persistence\Models\EloquentProducts;

class EloquentProductsRepository implements ProductsRepositoryInterface
{
    public function save(Products $products): void
    {
        EloquentProducts::updateOrCreate(
            ['uuid' => $products->id()->value()],
            [
                'family_id' => $products->familyId()->value(),
                'tax_id' => $products->taxId()->value(),
                'name' => $products->name(),
                'price' => $products->price()->value(),
                'stock' => $products->stock()->value(),
                'image_src' => $products->imageSrc(),
                'active' => $products->status()->value(),
            ],
        );
    }

    public function findById(string $id): ?Products
    {
        $eloquentProduct = EloquentProducts::where('uuid', $id)->first();

        if (!$eloquentProduct) {
            return null;
        }

        return Products::fromPersistence(
            $eloquentProduct->uuid,
            $eloquentProduct->family_id,
            $eloquentProduct->tax_id,
            $eloquentProduct->name,
            $eloquentProduct->price,
            $eloquentProduct->stock,
            $eloquentProduct->image_src,
            $eloquentProduct->active,
            $eloquentProduct->created_at,
            $eloquentProduct->updated_at,
            $eloquentProduct->deleted_at,
        );
    }
}
