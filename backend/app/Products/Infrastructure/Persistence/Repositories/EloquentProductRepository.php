<?php

namespace App\Products\Infrastructure\Persistence\Repositories;

use App\Products\Domain\Entity\Product;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Infrastructure\Persistence\Models\EloquentProduct;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function save(Product $product): void
    {
        EloquentProduct::updateOrCreate(
            ['uuid' => $product->id()->value()],
            [
                'family_id' => $product->familyId()->value(),
                'tax_id' => $product->taxId()->value(),
                'name' => $product->name(),
                'price' => $product->price()->value(),
                'stock' => $product->stock()->value(),
                'image_src' => $product->imageSrc(),
                'active' => $product->status()->value(),
            ],
        );
    }

    public function findById(string $id): ?Product
    {
        $eloquentProduct = EloquentProduct::where('uuid', $id)->first();

        if (!$eloquentProduct) {
            return null;
        }

        return Product::fromPersistence(
            $eloquentProduct->uuid,
            $eloquentProduct->family_id,
            $eloquentProduct->tax_id,
            $eloquentProduct->name,
            $eloquentProduct->price,
            $eloquentProduct->stock,
            $eloquentProduct->image_src,
            (bool) $eloquentProduct->active,
            $eloquentProduct->created_at->toDateTimeImmutable(),
            $eloquentProduct->updated_at->toDateTimeImmutable(),
            $eloquentProduct->deleted_at?->toDateTimeImmutable(),
        );
    }
}
