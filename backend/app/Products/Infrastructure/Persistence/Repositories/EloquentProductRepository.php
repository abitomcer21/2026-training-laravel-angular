<?php

namespace App\Products\Infrastructure\Persistence\Repositories;

use App\Products\Domain\Entity\Product;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Infrastructure\Persistence\Models\EloquentProduct;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private EloquentProduct $model,
    ) {}

    public function save(Product $product): void
    {
        $this->model->newQuery()->updateOrCreate(
            ['uuid' => $product->id()->value()],
            [
                'restaurant_id' => $product->restaurantId(),
                'family_id' => $product->familyId(),
                'tax_id' => $product->taxId(),
                'name' => $product->name()->value(),
                'price' => $product->price()->value(),
                'stock' => $product->stock()->value(),
                'image_src' => $product->imageSrc()->value(),
                'active' => $product->status()->value(),
            ],
        );
    }

    public function findById(string $id): ?Product
    {
        $eloquentProduct = $this->model->newQuery()->where('uuid', $id)->first();

        if (! $eloquentProduct) {
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
            $eloquentProduct->restaurant_id,
            $eloquentProduct->created_at->toDateTimeImmutable(),
            $eloquentProduct->updated_at->toDateTimeImmutable(),
        );
    }

    public function findByName(string $name): ?Product
    {
        $eloquentProduct = $this->model->newQuery()->where('name', $name)->first();

        if (! $eloquentProduct) {
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
            $eloquentProduct->restaurant_id,
            $eloquentProduct->created_at->toDateTimeImmutable(),
            $eloquentProduct->updated_at->toDateTimeImmutable(),
        );
    }

    public function findByFamilyId(int $familyId): array
    {
        return $this->model->newQuery()
            ->where('family_id', $familyId)
            ->get()
            ->map(fn ($eloquentProduct) => Product::fromPersistence(
                $eloquentProduct->uuid,
                $eloquentProduct->family_id,
                $eloquentProduct->tax_id,
                $eloquentProduct->name,
                $eloquentProduct->price,
                $eloquentProduct->stock,
                $eloquentProduct->image_src,
                (bool) $eloquentProduct->active,
                $eloquentProduct->restaurant_id,
                $eloquentProduct->created_at->toDateTimeImmutable(),
                $eloquentProduct->updated_at->toDateTimeImmutable(),
            ))->toArray();
    }

    public function all(): array
    {
        return $this->model->newQuery()->get()->map(
            fn (EloquentProduct $eloquentProduct): Product => Product::fromPersistence(
                $eloquentProduct->uuid,
                $eloquentProduct->family_id,
                $eloquentProduct->tax_id,
                $eloquentProduct->name,
                $eloquentProduct->price,
                $eloquentProduct->stock,
                $eloquentProduct->image_src,
                (bool) $eloquentProduct->active,
                $eloquentProduct->restaurant_id,
                $eloquentProduct->created_at->toDateTimeImmutable(),
                $eloquentProduct->updated_at->toDateTimeImmutable(),
            ),
        )->toArray();
    }

    public function delete(string $id): void
    {
        $this->model->newQuery()->where('uuid', $id)->delete();
    }
}
