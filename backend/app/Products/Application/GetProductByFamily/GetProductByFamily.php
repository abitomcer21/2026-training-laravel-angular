<?php

namespace App\Products\Application\GetProductByFamily;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class GetProductByFamily
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(int $familyId): GetProductByFamilyResponse
    {
        $products = $this->productRepository->findByFamilyId($familyId);

        return GetProductByFamilyResponse::fromProducts($products);
    }
}
