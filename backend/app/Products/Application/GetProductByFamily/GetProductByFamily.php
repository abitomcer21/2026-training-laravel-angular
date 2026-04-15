<?php

namespace App\Products\Application\GetProductByFamily;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class GetProductByFamily
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(int $FamilyId): GetProductByFamilyResponse
    {
        $products = $this->productRepository->findByFamilyId($FamilyId);

        return GetProductByFamilyResponse::fromProducts($products);
    }
}
