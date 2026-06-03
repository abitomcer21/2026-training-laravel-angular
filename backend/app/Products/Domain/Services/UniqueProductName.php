<?php

namespace App\Products\Domain\Services;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class UniqueProductName
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function check(string $name, string $familyId, int $restaurantId): void
    {
        $existing = $this->productRepository->findByNameAndFamily($name, $familyId, $restaurantId);
        
        if ($existing !== null) {
            throw new \InvalidArgumentException('El nombre del producto ya existe en esta familia');
        }
    }
}