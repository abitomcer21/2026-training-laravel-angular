<?php

namespace App\Products\Application\Handler;

use App\Products\Application\Query\GetProductByFamilyQuery;
use App\Products\Application\Response\GetProductByFamilyResponse;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class GetProductByFamilyHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(GetProductByFamilyQuery $query): GetProductByFamilyResponse
    {
        $products = $this->productRepository->findByFamilyId($query->familyId);

        return GetProductByFamilyResponse::create($products);
    }
}