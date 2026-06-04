<?php

namespace App\Products\Domain\Services;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class UniqueProductName
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function check(string $name, string $familyId, int $restaurantId): void
    {
        $product = $this->productRepository->findByName($name, $restaurantId);
        
        if ($product !== null && $product->familyId()->value() === $familyId) {
            throw new \InvalidArgumentException('El nombre del producto ya existe en esta familia');
        }
    }
}