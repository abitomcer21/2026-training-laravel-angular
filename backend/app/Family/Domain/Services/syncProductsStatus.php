<?php

namespace App\Family\Domain\Services;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class SyncProductsStatus
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(string $familyId, bool $active): void
    {
        $products = $this->productRepository->findByFamilyId($familyId);

        foreach ($products as $product) {
            $updatedProduct = $product->updateData(
                familyId: $product->familyId(),
                taxId:    $product->taxId(),
                name:     $product->name(),
                price:    $product->price(),
                stock:    $product->stock(),
                imageSrc: $product->imageSrc(),
                active:   $active,
            );

            $this->productRepository->save($updatedProduct);
        }
    }
}