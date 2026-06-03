<?php

namespace App\Family\Domain\Services;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\ValueObject\ProductStatus;

class SyncProductsStatus
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function sync(string $familyId, bool $active, int $restaurantId): void
    {
        $products = $this->productRepository->findByFamilyId($familyId,$restaurantId);

        foreach ($products as $product) {
            $updatedProduct = $product->updateData(
                familyId: $product->familyId(),
                taxId:    $product->taxId(),
                name:     $product->name(),
                price:    $product->price(),
                stock:    $product->stock(),
                imageSrc: $product->imageSrc(),
                status:   ProductStatus::create($active),
                restaurant: $restaurantId,
            );

            $this->productRepository->save($updatedProduct);
        }
    }
}