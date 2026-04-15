<?php

namespace App\Products\Application\GetProductByName;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class GetProductByName
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(string $name): ?GetProductByNameResponse
    {
        $product = $this->productRepository->findByName($name);
        if (!$product) {
            return null;
        }

        return GetProductByNameResponse::create($product);
    }
}
